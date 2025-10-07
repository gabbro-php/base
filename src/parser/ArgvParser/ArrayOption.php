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

namespace gabbro\parser\ArgvParser;

use gabbro\feature\Enumerable;
use IteratorAggregate;
use Traversable;

/**
 * Defines an argv array option.
 * E.g. `--name[] value`.
 *
 * @implements IteratorAggregate<int,string>
 * @implements Enumerable<int,string>
 */
class ArrayOption extends Option implements Enumerable, IteratorAggregate {

    /**
     * @ignore
     * @var array<string>
     */
    protected array $valueList = [];
    
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
     * @return ArrayOption
     */
    public static function withDescription(string|null $argName, string $desc, string $name, string ...$names): ArrayOption {
        $obj = new ArrayOption($name, ...$names);
        $obj->setDescription($desc);
        $obj->setTitle(
            implode("[], ", array_merge([$name], $names))."[]" . " " . ($argName ?: "VALUE")
        );
        
        return $obj;
    }
    
    /**
     * Get the length of values available.
     *
     * @return int<0,max>
     */
    public function length(): int {
        return count($this->valueList);
    }
    
    /**
     * Get the value array.
     *
     * @return array<string>
     */
    public function toArray(): array {
        return $this->valueList;
    }
    
    /**
     * {inheritdoc}
     *
     * @override {@see ValuedArgument::addValue()}
     */
    public function addValue(string $value): void {
        parent::addValue($value);
        $this->valueList[] = $value;
    }
    
    /* =================================================
     * Internal functions used by PHP
     */
     
    /**
     * @ignore
     * @return Traversable<int,string>
     * @override {@see Enumerable}
     */
    public function getIterator(): Traversable {
        foreach ($this->valueList as $value) {
            yield $value;
        }
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
        $arr["valueList"] = $this->valueList;
        
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
        
        if (is_array($data["valueList"])) {
            /** @var string $value */
            foreach ($data["valueList"] as $value) {
                $this->valueList[] = $value;
            }
        }
    }
}

