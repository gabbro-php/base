<?php declare(strict_types=1);
/*
 * This file is part of the Gabbro Project: https://github.com/Gabbro-PHP
 *
 * Copyright (c) 2025 Daniel Bergløv, License: MIT
 *
 * Permission is hereby granted, free of charge, to any person obtaining a copy of this software
 * and associated documentation files (the "Software"), to deal in the Software without restriction,
 * including without limitation the rights to use, copy, modify, merge, publish, distribute, sublicense,
 * and/or sell copies of the Software, and to permit persons to whom the Software is furnished to do so,
 * subject to the following conditions:
 *
 * The above copyright notice and this permission notice shall be included in all copies or substantial portions of the Software.
 *
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR IMPLIED, INCLUDING BUT NOT LIMITED TO
 * THE WARRANTIES OF MERCHANTABILITY, FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT.
 * IN NO EVENT SHALL THE AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER LIABILITY,
 * WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM, OUT OF OR IN CONNECTION WITH THE SOFTWARE OR
 * THE USE OR OTHER DEALINGS IN THE SOFTWARE.
 */

namespace gabbro\collection;

use gabbro\collection\ArgV\Argument;
use gabbro\collection\ArgV\NamedArgument;
use gabbro\collection\ArgV\IndexedArgument;
use gabbro\collection\ArgV\ValuedArgument;
use gabbro\feature\Serializable;
use gabbro\feature\Cloneable;
use gabbro\io\Shell;
use gabbro\utils\Text;
use Exception;

/**
 * ArgV – Command-line argument parser.
 *
 * This class provides a structured way to parse command-line arguments into
 * typed objects such as {@see Flag}, {@see Option}, {@see ArrayOption} and
 * {@see Operand}. It supports the most common Unix-style conventions:
 *
 * - **Flags**: `-h`, `--help`, `-abc` (multiple short flags collapsed)
 * - **Options**: `--name value`, `--name=value`
 * - **Array options**: `--vendor[] foo --vendor[] bar`, `--vendor[]=foo --vendor[]=bar`
 * - **Operands**: positional arguments
 * - **Double dash (`--`)**: stops option parsing, everything after is treated
 *   as operands
 *
 * Arguments can provide human-readable titles and descriptions, which allow
 * {@see ArgV::buildHelp()} to automatically generate a usage/help section.
 * Arguments can also be looked up later by name ({@see ArgV::getArgumentByName()})
 * or by positional index ({@see ArgV::getArgumentByIndex()}).
 *
 * ### Example
 *
 * ```
 * $arg = new stdClass();
 * $arg->Parser = new ArgV($argv);
 *
 * $arg->Parser->parseAll(
 *     $arg->help = Flag::withDescription(
 *             "Show this help section", 
 *             "--help", "-h"
 *     ),
 *       
 *     $arg->debug = Flag::withDescription(
 *             "Create a debug output", 
 *             "--debug"
 *     ),
 *       
 *     $arg->name = Option::withDescription(
 *             "NAME", 
 *             "Set the output name", 
 *             "--name"
 *     ),
 *       
 *     $arg->dirs = ArrayOption::withDescription(
 *             "PATH", 
 *             "Add one or more directories", 
 *             "--dir"
 *     )
 *
 *     $arg->dirs = Operand::withDescription(
 *             "Action", 
 *             "Perform action such as Action1 and Action2", 
 *             0
 *     )
 * );
 *
 * if ($arg->help->isSet() || !$arg->Parser->isConsumed()) {
 *     echo $arg->Parser->buildHelp(); 
 *     exit;
 * }
 * ```
 */
class ArgV implements Serializable, Cloneable {
    
    /**
     * The command that was invoked.
     *
     * @var string|null
     * @readonly
     */
    public string|null $cmd;
    
    /**
     * @ignore
     * @var array<
     *          int<0,max>,
     *          Pair<string|null,string,int<0,max>>
     *      >
     */
    protected array $argv = [];
    
    /**
     * @ignore
     * @var array<Argument>
     */
    protected array $argList = [];
    
    /**
     *
     *
     * @param list<string> $argv      Argv List
     *
     * @return void
     */
    public function __construct(iterable $argv) {
        $cur = 0;
        $cmd = null;
    
        foreach ($argv as $arg) {
            if ($cur++ == 0) {
                $cmd = $arg;
                continue;
            }
            
            $next = count($this->argv)+1;
            
            if (str_starts_with($arg, "-") && preg_match('/^-([a-zA-Z]+)$/', $arg, $m)) {
                $next += strlen($m[1]);
            
                foreach (str_split($m[1]) as $opt) {
                    /** @phpstan-ignore-next-line */
                    $this->argv[] = new Pair(null, "-$opt", $next);
                }
                
            } else {
                /** @phpstan-ignore-next-line */
                $this->argv[] = new Pair(null, $arg, $next);
            }
        }
        
        $this->cmd = $cmd;
    }
    
    /**
     * Build a help section from all parsed arguments.
     *
     * @param string|null $usage        Optional usage title overwrite.
     *
     * @return string                   Returns a full help section with all flags, options and operands.
     */
    public function buildHelp(string|null $usage = null): string {
        $lines = [ $usage ?? "Usage: {$this->cmd}" ];
        
        $options = [];
        $operands = [];
        $maxWidth = 0;
        
        foreach ($this->argList as $arg) {
            $title = $arg->getTitle();
            $desc = $arg->getDescription();
            
            if (empty($title)) {
                continue;
            }
            
            $maxWidth = max(strlen($title), $maxWidth);
            
            if ($arg instanceof NamedArgument) {
                $options[] = $title;
                $options[] = $desc;
            
            } else if ($arg instanceof IndexedArgument) {
                $operands[] = $title;
                $operands[] = $desc;
            }
        }
        
        $maxWidth += 4;
        $len = count($options);
        $wrapLen = Shell::getConsoleWidth();
        
        if ($wrapLen > 120) {
            $wrapLen = (int) ($wrapLen * 0.8);
        }
        
        if ($len > 0) {
            $lines[] = "\nOptions:";
            
            for ($t = 0, $d = 1; $d < $len; $t+=2, $d+=2) {
                $str = sprintf("  %-{$maxWidth}s ", $options[$t]);
                
                if (!empty($options[$d])) {
                    $str .= Text::wrapText($options[$d], $wrapLen - $maxWidth, $maxWidth + 3);
                }
                
                if (strpos($str, "\n") !== false) {
                    $str .= "\n";
                }

                $lines[] = $str;
            }
        }
        
        $len = count($operands);
        
        if ($len > 0) {
            $lines[] = "\nOperands:";
            
            for ($t = 0, $d = 1; $d < $len; $t+=2, $d+=2) {
                $str = sprintf("  %-{$maxWidth}s ", $operands[$t]);
                
                if (!empty($operands[$d])) {
                    $str .= Text::wrapText($operands[$d], $wrapLen - $maxWidth, $maxWidth + 3);
                }
                
                if (strpos($str, "\n") !== false) {
                    $str .= "\n";
                }

                $lines[] = $str;
            }
        }
        
        return implode("\n", $lines)."\n";
    }
    
    /**
     * Get a parsed argument by name.
     *
     * @param string $name          The name of the argument.
     *                              This can be any name that was added to the argument.
     *
     * @return NamedArgument|null   Returns NULL if the argument was not found.
     */
    public function getArgumentByName(string $name): NamedArgument|null {
        foreach ($this->argList as $arg) {
            if (($arg instanceof NamedArgument) && $arg->hasName($name)) {
                return $arg;
            }
        }
        
        return null;
    }
    
    /**
     * Get a parsed argument by it's position.
     *
     * @param int<0,max> $pos           The position of the argument.
     *                                  This is the position that the argument was created with.
     *
     * @return IndexedArgument|null     Returns NULL if the argument was not found.
     */
    public function getArgumentByIndex(int $pos): IndexedArgument|null {
        foreach ($this->argList as $arg) {
            if (($arg instanceof IndexedArgument) && $arg->getPosition() == $pos) {
                return $arg;
            }
        }
        
        return null;
    }
    
    /**
     * Rewind/Reset the ArgV instance.
     *
     * @return void
     */
    public function rewind(): void {
        $this->argList = [];
        
        foreach ($this->argv as $pair) {
            $pair->key = null;
        }
    }
    
    /**
     * Check to see if all arguments in argv has been consumed.
     *
     * @param string|null &$arg         On failure this will contain the value of that argument.
     *
     * @return bool
     */
    public function isConsumed(string|null &$arg = null): bool {
        foreach ($this->argv as $pair) {
            if ($pair->key === null && $pair->value != "--") {
                $arg = $pair->value;
                return false;
            }
        }
        
        return true;
    }
    
    /**
     * Parse multiple arguments.
     *
     * This method will always parse operands last, no mater what order
     * they are declared in. 
     *
     * @see self::parse()
     *
     * @param Argument $args         One or more arguments to parse.
     *
     * @return void
     */
    public function parseAll(Argument ...$args): void {
        usort($args, function ($a, $b): int {
            $aIsOperand = $a instanceof IndexedArgument;
            $bIsOperand = $b instanceof IndexedArgument;

            /*
             * 0 <=> 1 = -1
             * 1 <=> 0 =  1
             * 0 <=> 0 =  0
             */
            return $aIsOperand <=> $bIsOperand;
        });
        
        foreach ($args as $arg) {
            $this->parse($arg);
        }
    }
    
    /**
     * Parse a single argument. 
     *
     * Argv is a very primitive structure that parses into more complex parts. 
     * It can contain options which specify value as `name=value`, `name value` or `-n value, 
     * it can contain flags like `-f` or `--flag` and even multiple single char flags like `-abc`, 
     * it can contain arrays like `--name[] value` and `--name[]=value` and it can contain single operands. 
     *
     * To parse arguments correctly, make sure to parse all flags and options 
     * before parsing any operands. Without knowing which non-dashed argument belong as values
     * for options, there is no way to sort out operands correctly.  
     *
     * @param Argument $arg         The argument to parse.
     *
     * @return bool
     */
    public function parse(Argument $arg): bool {
        if (in_array($arg, $this->argList, true)) {
            return $arg->isSet();
        }
    
        $this->argList[] = $arg;
        
        $max = count($this->argv);
        $pos = 0; // Operand position pointer
        $process = true;
        $found = false;
        
        foreach ($this->argv as $pair) {
            if ($pair->key !== null) {
                if ($pair->key == "operand") {
                    $pos++;
                }
            
                continue;
                
            } else if ($process && $pair->value == "--") {
                $process = false;
                continue;
            }
            
            if ($process && ($arg instanceof NamedArgument)) {
                if (str_starts_with($pair->value, "-")) {
                    $key = $pair->value;
                    $value = null;
                    $isArray = false;
                    
                    if (($arg instanceof ValuedArgument)
                            && str_starts_with($pair->value, "--")) {
                            
                        $ofseq = strpos($pair->value, "=");
                        $ofsay = strpos($pair->value, "[");
                        $ofs = ($ofseq === false && $ofsay === false)
                                    ? false
                                    /** @phpstan-ignore-next-line */
                                    : min(array_filter([$ofseq, $ofsay], "is_int"));
                                    
                        $isArray = $ofsay !== false;
                        
                        if ($ofs !== false) {
                            $key = substr($pair->value, 0, $ofs);
                            
                            if ($ofseq !== false) {
                                $value = substr($pair->value, $ofseq+1);
                            }
                        }
                    }
                    
                    if (!$arg->hasName($key)) {
                        continue;
                    }
                    
                    $arg->isSet(true);
                    $pair->key = "flag";
                    
                    if ($arg instanceof ValuedArgument) {
                        if ($value === null) {
                            $valuePair = $this->argv[ $pair->meta ] ?? null;
                            
                            if ($valuePair === null || $valuePair->key !== null) {
                                throw new Exception("Missing value for option $key");
                            }
                            
                            $value = $valuePair->value;
                            $valuePair->key = "value";
                        }
                        
                        $pair->key = "option";
                        $arg->addValue($value);
                        
                        if ($isArray) {
                            $found = true;
                            continue;
                        }
                    }
                    
                    return true;
                }
            
                continue;
                
            } else if (($arg instanceof IndexedArgument) 
                    && ($arg instanceof ValuedArgument)
                    && $arg->getPosition() == $pos) {

                $pair->key = "operand";
                $arg->addValue($pair->value);
                $arg->isSet(true);
                
                return true;
            }
        }
        
        return $found;
    }
    
    /**
     * {@inheritdoc}
     *
     * @override {@see Cloneable::clone()}
     */
    function clone(): static {
        return clone $this;
    }
    
    /* =================================================
     * Internal functions used by PHP
     */
    
    /**
     * @ignore
     * @override {@see Serializable::__serialize()}
     */
    public function __serialize(): array {
        return [
            "cmd" => $this->cmd,
            "argv" => $this->argv
        ];
    }
    
    /**
     * @ignore
     * @param array{
     *      "cmd": string|null,
     *      "argv": array<
     *          int<0,max>,
     *          Pair<string|null,string,int<0,max>>
     *      >
     * } $data
     *
     * @override {@see Serializable::__unserialize()}
     */
    public function __unserialize(array $data): void {
        $this->cmd = $data["cmd"];
        $this->argv = $data["argv"];
    }
    
    /**
     * @ignore
     * @override {@see Serializable::__debugInfo()}
     */
    public function __debugInfo(): array {
        return $this->__serialize();
    }
    
    /**
     * @ignore
     * @override {@see Serializable::__debugInfo()}
     */
    public function __clone(): void {
        foreach ($this->argList as $pos => $arg) {
            $this->argList[$pos] = $arg->clone();
        }
    }
}

