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

namespace gabbro\parser;

use gabbro\parser\ArgvParser\Argument;
use gabbro\parser\ArgvParser\NamedArgument;
use gabbro\parser\ArgvParser\IndexedArgument;
use gabbro\parser\ArgvParser\ValuedArgument;
use gabbro\exception\InvalidInputException;
use gabbro\exception\InvalidTypeException;
use gabbro\feature\Serializable;
use gabbro\feature\Cloneable;
use gabbro\collection\Pair;
use gabbro\io\Shell;
use gabbro\utils\Text;

/**
 * Command-line argument parser.
 *
 * The {@see ArgvParser} class provides a complete framework for defining and
 * parsing command-line arguments in a structured way. Each argument is defined
 * by an {@see Argument} subclass (such as {@see NamedArgument} for options or
 * {@see IndexedArgument} for positional operands).
 *
 * Features:
 *  - Supports named options (short and long forms)
 *  - Supports indexed operands (positional arguments)
 *  - Handles negative positional indices
 *  - Expands compact short options (e.g. `-abc` → `-a`, `-b`, `-c`)
 *  - Generates usage/help text automatically via {@see assembleHelp()}
 *  - Serializable and cloneable
 *
 * Further, this class does not enforce using `-` and `--` for flags and options. 
 * It will parse anything as a flag or option if a name matches a {@see NamedArgument}. 
 * 
 * The negative positional indices allows for optional operands before another operand, 
 * making it much more flexible when building an argv schema. 
 */
class ArgvParser implements Serializable, Cloneable {

    /**
     * @ignore
     * @var string|null
     */
    protected string|null $command = null;
    
    /**
     * @ignore
     * @var list<Argument>
     */
    protected array $arguments = [];
    
    /**
     * @ignore
     * @var bool
     */
    protected bool $consumed = true;
    
    /**
     * @ignore
     * @var list<array{
     *      type: string,
     *      row: bool,
     *      title: string,
     *      text?: string|null
     * }>
     */
    protected array $usage = [];
    
    /**
     * @ignore
     *
     * Sorts all defined arguments into parsing order.
     *
     * Non-indexed arguments (named options) come first, followed by
     * indexed operands. Operands with negative positions are sorted
     * before positive positions; within each sign group, they are
     * ordered ascending by their absolute index.
     *
     * @param bool $reversedNegation  If `true`, reverses the ordering of
     *                                negative indices.
     *
     * @return list<Argument>  Sorted list of argument definitions.
     */
    protected function sortArguments(bool $reversedNegation = false): array {
        $arguments = $this->arguments;
        
        usort($arguments, function ($a, $b) use ($reversedNegation): int {
            $aIsIndexed = $a instanceof IndexedArgument;
            $bIsIndexed = $b instanceof IndexedArgument;

            // Non-indexed arguments always come first
            if (!$aIsIndexed || !$bIsIndexed) {
                return $aIsIndexed <=> $bIsIndexed;
            }

            // Both are IndexedArgument
            $aPos = $a->getPosition();
            $bPos = $b->getPosition();

            // Negatives before non-negatives, unless $reversedNegation is true
            $cmp = ($bPos < 0) <=> ($aPos < 0);
            if ($cmp !== 0) {
                return $reversedNegation ? ($cmp * -1) : $cmp;
            }

            // Within same sign: sort ascending (e.g. -2,-1 or 0,1,2)
            return $aPos <=> $bPos;
        });
        
        return $arguments;
    }
    
    /**
     * @ignore
     * 
     * Tokenizes a list of raw command-line values into {@see Pair} objects.
     *
     * Expands compact short options such as `-abc` into separate tokens
     * (`-a`, `-b`, `-c`) and handles the special `--` stop marker that
     * terminates option parsing.
     *
     * @param list<string> $values                      Raw command-line argument values (excluding the command name).
     *
     * @return list<Pair<null, string, int<0,max>>>     Token objects representing parsed CLI items.
     */
    protected function parseTokens(iterable $values): array {
        $tokens = [];
        $break = false;
    
        foreach ($values as $val) {
            if (!$break && str_starts_with($val, "-") && preg_match('/^-([0-9a-zA-Z]+)$/', $val, $m)) {
                $chars = str_split($m[1]);
                $offset = count($chars);
            
                foreach ($chars as $char) {
                    $tokens[] = new Pair(null, "-{$char}", $offset);
                }
                
            } else if ($val == "--") {
                $break = true;
                
            } else {
                $tokens[] = new Pair(null, $val, $break ? 0 : 1);
            }
        }

        /*
         * According to PHPStan a non-falsy-string does not fit into string. 
         * Likewise int<1,max> does not fit into int<0,max>. Since we produce both, it cannot
         * figure out how to collapse it. Stupid design, let's ignore it.
         */
        /** @phpstan-ignore-next-line */
        return $tokens;
    }
    
    /**
     * @ignore
     * 
     * Formats a help table for display on the console.
     *
     * Takes a pre-built table definition (titles and rows) and generates a
     * string suitable for console output, with column alignment and optional
     * text wrapping according to console width.
     *
     * @param list<array{
     *      row: bool,
     *      title: string|null,
     *      text?: string|null
     * }|null> $table           Table definition.
     *
     * @return string           Aligned, wrapped help text.
     */
    protected function parseTable(iterable $table): string {
        $lines = [];
        $maxWith = 0;
        
        foreach ($table as $item) {
            if ($item !== null && $item["row"]) {
                $maxWith = max(strlen($item["title"] ?? ""), $maxWith);
            }
        }
        
        $maxWith += 4;
        $wrapLen = Shell::getConsoleWidth();
        
        if ($wrapLen > 120) {
            $wrapLen = (int) ($wrapLen * 0.8);
        }
        
        foreach ($table as $item) {
            if ($item === null) {
                $lines[] = "";
            
            } else if ($item["row"]) {
                $str = sprintf("  %-{$maxWith}s ", $item["title"] ?? "");
                
                if (!empty($item["text"])) {
                    $str .= ltrim(Text::wrapText($item["text"], $wrapLen - $maxWith, $maxWith + 3));
                }
                
                if (strpos($str, "\n") !== false) {
                    $str .= "\n";
                }
                
                $lines[] = $str;
            
            } else {
                if (!empty($lines)) {
                    $lines[] = "";
                }
            
                $lines[] = Text::wrapText($item["title"] ?? "", $wrapLen);
            }
        }
        
        return implode("\n", $lines)."\n";
    }
    
    /**
     * Sets or updates the main usage header line.
     *
     * This line forms the top of the generated usage
     * text, typically containing the command name and syntax.
     * Setting this will override the one that is usually generated
     * automatically by {self::getUsageText()}.
     *
     * @param string $title  The main usage header text.
     *
     * @return void
     */
    public function setUsageTitle(string $title): void {
        foreach ($this->usage as &$row) {
            if ($row["type"] == "header") {
                $row["title"] = $title;
                return;
            }
        }
        
        $this->usage[] = [
            "type" => "header",
            "row" => false,
            "title" => $title
        ];
    }
    
    /**
     * Sets or updates the main usage footer.
     *
     * This line forms the bottom of the generated usage
     * text, typically containing additional information, 
     * examples etc.
     *
     * @param string $footer  The footer text.
     *
     * @return void
     */
    public function setUsageFooter(string $footer): void {
        foreach ($this->usage as &$row) {
            if ($row["type"] == "footer") {
                $row["title"] = $footer;
                return;
            }
        }
        
        $this->usage[] = [
            "type" => "footer",
            "row" => false,
            "title" => $footer
        ];
    }
    
    /**
     * Adds an additional option entry to the usage section.
     *
     * The option will appear under the "Options:" heading in the
     * generated usage text. Each entry represents a command-line
     * option such as `--help` or `-v`, along with an optional
     * descriptive text.
     *
     * This is a custom added description and does not have any requirements
     * or dependencies on the {$see Argument}'s within.
     *
     * @param string      $title  The option name or syntax (e.g. `--help`, `-v`).
     * @param string|null $desc   Optional description of the option.
     *
     * @return void
     */
    public function addUsageOption(string $title, string|null $desc = null): void {
        $this->usage[] = [
            "type" => "option",
            "row" => true,
            "title" => $title,
            "text" => $desc
        ];
    }
    

    /**
     * Adds an operand (positional argument) entry to the usage section.
     *
     * The operand will appear under the "Operands:" heading in the
     * generated usage text. Each entry represents a required or optional
     * positional argument to the command, such as a target file or path.
     *
     * This is a custom added description and does not have any requirements
     * or dependencies on the {$see Argument}'s within.
     *
     * @param string      $title  The operand name or label (e.g. `FILE`, `TARGET`).
     * @param string|null $desc   Optional description of the operand.
     *
     * @return void
     */
    public function addUsageOperand(string $title, string|null $desc = null): void {
        $this->usage[] = [
            "type" => "operand",
            "row" => true,
            "title" => $title,
            "text" => $desc
        ];
    }
    
    /**
     * Builds a complete usage/help text for the defined command.
     *
     * This method constructs a structured usage message including options
     * and operands based on the registered argument definitions.
     *
     * @return string  Formatted usage/help text.
     */
    public function getUsageText(): string {
        $arguments = $this->sortArguments(true);
        $optionsTable = [];
        $operandsTable = [];
        $footerTable = [];
        $mainTable = [];
        
        foreach ($arguments as $argument) {
            $title = $argument->getTitle();
            $desc = $argument->getDescription();
            
            if (empty($title)) {
                continue;
            }
            
            if ($argument instanceof NamedArgument) {
                $optionsTable[] = [
                    "row" => true,
                    "title" => $title,
                    "text" => $desc
                ];
            
            } else if ($argument instanceof IndexedArgument) {
                $operandsTable[] = [
                    "row" => true,
                    "title" => $title,
                    "text" => $desc
                ];
            }
        }
        
        foreach ($this->usage as $row) {
            if ($row["type"] == "option") {
                $optionsTable[] = $row;
                
            } else if ($row["type"] == "operand") {
                $operandsTable[] = $row;
            
            } else if ($row["type"] == "header") {
                $mainTable[0] = $row;
                
            } else if ($row["type"] == "footer") {
                $footerTable[0] = $row;
            }
        }
        
        if (empty($mainTable)) {
            $header = "Usage: ".($this->command ?? "CMD")."";
            
            if (!empty($optionsTable)) {
                $header .= " [Options]";
            }
            
            if (!empty($operandsTable)) {
                $header .= " ";
                $header .= implode(" ", array_column($operandsTable, "title"));
            }
            
            $mainTable[0] = [
                "row" => false,
                "title" => $header
            ];
        }
        
        if (!empty($optionsTable)) {
            array_unshift($optionsTable, [
                "row" => false,
                "title" => "Options:"
            ]);
        }
        
        if (!empty($operandsTable)) {
            array_unshift($operandsTable, null);
        }
        
        return $this->parseTable(
            array_merge($mainTable, $operandsTable, $optionsTable, $footerTable)
        );
    }
    
    /**
     * Adds one or more argument definitions to the parser.
     *
     * @param Argument $argument        Non-optional argument to register.
     * @param Argument ...$arguments    Additional arguments to register.
     *
     * @return void
     */
    public function addArguments(Argument $argument, Argument ...$arguments): void {
        $this->arguments[] = $argument;
        
        foreach ($arguments as $arg) {
            $this->arguments[] = $arg;
        }
    }
    
    /**
     * Retrieves an argument definition by name or position.
     *
     * For string identifiers, looks up a {@see NamedArgument} with the
     * given name. For integer positions, returns an {@see IndexedArgument}.
     * Negative positions are resolved from the right side.
     *
     * @param int|string $id    Argument name or index position.
     *
     * @return ($id is string
     *     ? NamedArgument
     *     : IndexedArgument
     * ) | null                 Matching argument definition or `null` if not found.
     */
    // TODO: Speed up this process
    public function getArgument(int|string $id): Argument|null {
        $operands = [];
        $arguments = $this->sortArguments(true);
        
        foreach ($arguments as $argument) {
            if (is_string($id)) {
                if ($argument instanceof NamedArgument 
                        && $argument->hasName($id)) {
                        
                    return $argument;
                }
            
            } else if ($argument instanceof IndexedArgument) {
                $operands[] = $argument;
            }
        }
        
        if (is_string($id)) {
            return null;
        
        } else if ($id < 0) {
            $id = count($operands) + $id;
            
            if ($id < 0) {
                return null;
            }
        }
        
        return $operands[$id] ?? null;
    }
    
    /**
     * Retrieves a valued argument definition by name or position.
     *
     * @param int|string $id            Argument name or index position.
     *
     * @return ($id is string
     *     ? ValuedArgument&NamedArgument
     *     : ValuedArgument&IndexedArgument
     * )                                Matching argument definition
     * @throws InvalidTypeException     If the argument does not exist or is not an ValuedArgument.
     */
    public function getValuedArgument(int|string $id): ValuedArgument {
        $argument = $this->getArgument($id);
        
        if ($argument === null || !($argument instanceof ValuedArgument)) {
            throw new InvalidTypeException("Argument '$id' is not a type of 'ValuedArgument'");
        }
        
        return $argument;
    }
    
    /**
     * Retrieves a named argument definition by name.
     *
     * @param string $name              The option name.
     *
     * @return NamedArgument            Matching named argument.
     * @throws InvalidTypeException     If the argument does not exist or is not an NamedArgument.
     */
    public function getNamedArgument(string $name): NamedArgument {
        $argument = $this->getArgument($name);
        
        if ($argument === null) {
            throw new InvalidTypeException("Argument '$name' is not a type of 'NamedArgument'");
        }
        
        return $argument;
    }
    
    /**
     * Retrieves an indexed argument definition by position.
     *
     * @param int $pos                  Operand position index (negative values count from the end).
     *
     * @return IndexedArgument          Matching indexed argument.
     * @throws InvalidTypeException     If the argument does not exist or is not an IndexedArgument.
     */
    public function getIndexedArgument(int $pos): IndexedArgument {
        $argument = $this->getArgument($pos);
        
        if ($argument === null) {
            throw new InvalidTypeException("Argument '$pos' is not a type of 'IndexedArgument'");
        }
        
        return $argument;
    }
    
    /**
     * Indicates whether the entire argv was consumed during parsing.
     *
     * @return bool
     */
    public function isConsumed(): bool {
        return $this->consumed;
    }
    
    /**
     * Returns the parsed command name, or `null` if not set.
     *
     * @return string|null
     */
    public function getCommand(): string|null {
        return $this->command;
    }
    
    /**
     * Parses the given command-line values.
     *
     * Processes both named options and positional operands, assigning
     * values to matching argument definitions. Returns whether the
     * entire vector was consumed.
     *
     * @param list<string> $values      Raw argument vector (as from `$_SERVER['argv']`).
     *
     * @return bool                     `true` if all arguments were successfully consumed; otherwise `false`.
     * @throws InvalidInputException    If a required option value is missing.
     */
    public function parse(iterable $values): bool {
        $this->command = array_shift($values);
        $this->consumed = true;
    
        $arguments = $this->sortArguments();
        $tokens = $this->parseTokens($values);
        
        foreach ($arguments as $argument) {
            $argument->isSet(false);
        }
        
        if (count($tokens) == 0) {
            return true;
        }
        
        foreach ($arguments as $argument) {
            if (count($tokens) == 0) {
                return true;
            
            } else if (!($argument instanceof NamedArgument)) {
                continue;
            }
        
            for ($pos = 0; $pos < count($tokens); $pos++) {
                $token = $tokens[$pos];
            
                if ($token->meta == 0) {
                    break 2;
                }
                
                $name = $token->value;
                $value = null;
                $isArray = false;
                
                if ($argument instanceof ValuedArgument) {
                    $ofseq = strpos($token->value, "=");
                    $ofsay = strpos($token->value, "[");
                    $ofs = ($ofseq === false && $ofsay === false)
                                ? false
                                /** @phpstan-ignore-next-line */
                                : min(array_filter([$ofseq, $ofsay], "is_int"));
                                
                    $isArray = $ofsay !== false;
                    
                    if ($ofs !== false) {
                        $name = substr($token->value, 0, $ofs);
                        
                        if ($ofseq !== false) {
                            $value = substr($token->value, $ofseq+1);
                        }
                    }
                }
                
                if ($argument->hasName($name)) {
                    $argument->isSet(true);
                    
                } else {
                    continue;
                }
                
                if ($argument instanceof ValuedArgument) {
                    if ($value === null) {
                        $nextPos = $pos + $token->meta;
                        $nextToken = $tokens[$nextPos] ?? null;
                        
                        if ($nextToken === null) {
                            throw new InvalidInputException("Missing value for option $name");
                            
                        } else {
                            array_splice($tokens, $nextPos, 1);
                        }
                        
                        $value = $nextToken->value;
                    }
                    
                    $argument->addValue($value);
                }
                
                array_splice($tokens, $pos, 1);
                
                if (!$isArray) {
                    continue 2;
                }
                
            } // for
        } // foreach
        
        $len = count($tokens);
        
        if ($len == 0) {
            return true;
        }
        
        foreach ($arguments as $argument) {
            if (!($argument instanceof IndexedArgument)
                    || !($argument instanceof ValuedArgument)) {
                    
                continue;
                 
            } else if (($pos = $argument->getPosition()) < 0) {
                $pos += $len;
            }
            
            if ($pos < 0 || $pos >= $len || $tokens[$pos] === null) {
                continue;
            }
            
            $argument->isSet(true);
            $argument->addValue($tokens[$pos]->value);
            
            $tokens[$pos] = null;
        }
        
        $tokens = array_filter($tokens, fn($v) => $v !== null);
        $this->consumed = empty($tokens);

        return $this->consumed;
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
            "command" => $this->command,
            "arguments" => $this->arguments
        ];
    }
    
    /**
     * @ignore
     * @param array{
     *      "command": string|null,
     *      "arguments": list<Argument>
     * } $data
     *
     * @override {@see Serializable::__unserialize()}
     */
    public function __unserialize(array $data): void {
        $this->command = $data["command"];
        $this->arguments = $data["arguments"];
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
        foreach ($this->arguments as $pos => $arg) {
            /*
             * array<int<0, max>, VAL> != list<VAL>
             *
             * Again more stupid PHPStan "logic" to ignore. 
             * If you invent a new type like 'list', at least make it compatible
             * with actual matching PHP types.
             */
            /** @phpstan-ignore-next-line */
            $this->arguments[$pos] = $arg->clone();
        }
    }
}

