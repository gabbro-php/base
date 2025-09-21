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

use Traversable;
use IteratorAggregate;
use SplDoublyLinkedList;
use UnderflowException;
use gabbro\exception\DatasetException;
use gabbro\feature\Enumerable;
use gabbro\feature\Serializable;
use gabbro\feature\Cloneable;

/**
 * Defines a basic stack array.
 *
 * The order of in/out is not defined in this class.
 *
 * @template T
 * @implements IteratorAggregate<int,T>
 * @implements Enumerable<int,T>
 */
abstract class StackArray implements Enumerable, IteratorAggregate, Serializable, Cloneable {


    /** 
     * @ignore 
     *
     * @var SplDoublyLinkedList<T> $dataset
     */
    protected SplDoublyLinkedList $dataset;

    /**
     * @ignore
     */
    public function __construct() {
        $this->dataset = new SplDoublyLinkedList();
    }
    
    /**
     * Push a new value into this stack.
     *
     * The order in which values are placed, depends on
     * the implementation.
     *
     * @param T $value      The new value to push.
     *
     * @return void
     */
    abstract function push(mixed $value): void;

    /**
     * Pop a value off of this stack.
     *
     * The order in which values are removed, depends on
     * the implementation.
     *
     * @return T                    This will return the value that was popped off the stack.
     *
     * @throws DatasetException     If the stack is empty.
     */
    abstract function pop(): mixed;
    
    /**
     * Pop a value off of this stack.
     *
     * Unlike `pop()` this will not throw exception on empty stack. 
     * Instead you can declare a default value to be returned.
     *
     * @return ($default is null ? T|null : T)             This will return the value that was popped off the stack or the default value.
     */
    function popOr(mixed $default = null): mixed {
        try {
            return $this->pop();
        
        } catch (DatasetException $e) {
            return $default;
        }
    }

    /**
     * Returns the current value in the stack.
     *
     * The value that is returned from this, is the next value
     * that will be removed when calling `pop()`.
     *
     * @return T|null       If this stack is empty, then `NULL` is returned.
     */
    abstract function peak(): mixed;
    
    /**
     * Clear the dataset.
     *
     * @return void
     */
    function clear(): void {
        $this->dataset = new SplDoublyLinkedList();
    }
    
    /**
     * Parse the dataset into a PHP Array.
     *
     * Builds a PHP array containing all of the current values within
     * the dataset. If the dataset is empty, and empty array is returned.
     *
     * @return array<T>
     */
    function toArray(): array {
        $arr = [];
        
        foreach ($this->dataset as $val) {
            $arr[] = $val;
        }

        return $arr;
    }

    /**
     * Get the current length of the dataset.
     *
     * @return int
     */
    function length(): int {
        return $this->dataset->count();
    }

    /**
     * Add values from an iterable object or array.
     *
     * @param iterable<T> $itt
     *
     * @return void
     */
    function addIterable(iterable $itt): void {
        foreach ($itt as $value) {
            $this->push($value);
        }
    }
    
    /**
     * Traverses the dataset.
     *
     * This will traverse the dataset and call the closure on each index. 
     * If the closure returns `true` at any time, then the traversal ends. 
     * Return `false` to keep the traversal going.
     *
     * @param callable(T):bool $closure         The closure to call.
     *
     * @return bool                             Returns `false` if the traversal was terminated or `true` otherwise.
     */
    public function traverse(callable $closure): bool {
        while ($this->peak() !== null) {
            $res = $closure($this->pop());

            if ($res) {
                return false;
            }
        }

        return true;
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
     * @override {@see Enumerable}
     * @return Traversable<T> 
     */
    public function getIterator(): Traversable {
        while ($this->peak() !== null) {
            yield $this->pop();
        }
    }
     
    /**
     * @ignore
     * @override {@see Serializable::__serialize()}
     */
    public function __serialize(): array {
        return $this->toArray();
    }
    
    /**
     * @ignore
     * @param mixed[] $data
     * @override {@see Serializable::__unserialize()}
     */
    public function __unserialize(array $data): void {
        $this->addIterable($data);
    }
    
    /**
     * @ignore
     * @override {@see Serializable::__debugInfo()}
     */
    public function __debugInfo(): array {
        return $this->toArray();
    }
    
    /**
     * @ignore
     * @override {@see Serializable::__debugInfo()}
     */
    public function __clone(): void {
        $this->dataset = clone $this->dataset;
    }
}
