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

namespace gabbro\collection\ArgV;

/**
 * Defines an argv option.
 * E.g. `--name value`.
 */
class Option extends BaseArgument implements ValuedArgument, NamedArgument {

    /**
     * @ignore
     * @var array<string,bool>
     */
    protected array $names = [];

    /**
     * @ignore
     * @var string|null
     */
    protected string|null $value;
    
    /**
     * Create a new Option with description.
     *
     * This method will automatically create the title
     * based on the names parsed.
     *
     * @param string|null $argName      Name for the title argument.
     * @param string $desc              Description for this option.
     * @param string $name              Name of this option.
     * @param string $names             Optional additional name of the option.
     *
     * @return Option
     */
    public static function withDescription(string|null $argName, string $desc, string $name, string ...$names): Option {
        $obj = new Option($name, ...$names);
        $obj->setDescription($desc);
        $obj->setTitle(
            implode(", ", array_merge([$name], $names)) . " " . ($argName ?: "VALUE")
        );
        
        return $obj;
    }
    
    /**
     * Create a new Option object.
     *
     * @param string $name          Name of this flag.
     * @param string $names         Optional additional name of the flag.
     *
     * @return void
     */
    public function __construct(string $name, string ...$names) {
        $this->names[$name] = true;
    
        foreach ($names as $name) {
            $this->names[$name] = true;
        }
    }
    
    /**
     * {inheritdoc}
     *
     * @override {@see NamedArgument::hasName()}
     */
    public function hasName(string $name): bool {
        return isset($this->names[$name]);
    }
    
    /**
     * {inheritdoc}
     *
     * @override {@see ValuedArgument::addValue()}
     */
    public function addValue(string $value): void {
        $this->value = $value;
    }

    /**
     * {inheritdoc}
     *
     * @override {@see ValuedArgument::getValue()}
     */
    public function getValue(string|null $default = null): string|null {
        return $this->value ?? $default;
    }
    
    /* =================================================
     * Internal functions used by PHP
     */
     
    /**
     * @ignore
     * @override {@see Serializable::__serialize()}
     */
    public function __serialize(): array {
        $arr = parent::__serialize();
        $arr["names"] = array_keys($this->names);
        $arr["value"] = $this->value;
        
        return $arr;
    }
    
    /**
     * @ignore
     * @param array<string,mixed> $data
     *
     * @override {@see Serializable::__unserialize()}
     */
    public function __unserialize(array $data): void {
        parent::__unserialize($data);
        
        if (is_array($data["names"])) {
            /** @var string $name */
            foreach ($data["names"] as $name) {
                $this->names[$name] = true;
            }
        }
        
        $this->value = is_string($data["value"]) ? $data["value"] : null;
    }
}

