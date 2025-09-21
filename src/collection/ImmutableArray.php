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

use gabbro\feature\Enumerable;
use gabbro\feature\Serializable;
use gabbro\feature\Cloneable;

/**
 * Defines an immutable unstructured list
 *
 * @template T
 * @extends Enumerable<int,T>
 */
interface ImmutableArray extends Enumerable, Serializable, Cloneable {

    /**
     * Parse the dataset into a PHP Array.
     *
     * Builds a PHP array containing all of the current values within
     * the dataset. If the dataset is empty, and empty array is returned.
     *
     * @return array<int,T>
     */
    function toArray(): array;

    /**
     * Get the current length of the dataset.
     *
     * @return int
     */
    function length(): int;

    /**
     * Join all the values in the dataset into one string.
     *
     * @param string|null $delimiter        Optional string or character that will be added in between
     *                                      each value in the string.
     *
     * @return string                       Returns the joined string.
     */
    function join(string|null $delimiter = null): string;

    /**
     * Checks to see if a value exists.
     *
     * Search the dataset for the first occurrence of a value. 
     * It will do a strict search e.g. `FALSE != 0`.
     *
     * @param T $value        A value to look for.
     *
     * @return bool
     */
    function contains(mixed $value): bool;

    /**
     * Filters elements into a new instance.
     *
     * Make a copy of this instance with filtered values.
     * This will traverse the dataset and call the closure on each index. 
     * If the closure returns `false` then the value will not be added to the
     * copy.
     *
     * @param callable(T):bool $closure         The closure to call.
     *
     * @return static<T>                        Returns a new instance with the filtered dataset.
     */
    function filter(callable $closure): static;
    
    /**
     * Traverses the dataset.
     *
     * This will traverse the dataset and call the closure on each index. 
     * If the closure returns `true` at any time, then the traversal ends. 
     * Return `false` to keep the traversal going.
     *
     * @note                                    Values are sent by reference.
     *
     * @param callable(T):bool $closure         The closure to call.
     *
     * @return bool                             Returns `false` if the traversal was terminated or `true` otherwise.
     */
    function traverse(callable $closure): bool;
    
    /**
     * Find and return a value.
     *
     * This will traverse the dataset and call the closure on each index. 
     * If the closure returns true, then this value will be returned to the caller. 
     * This allows for searching complex value sets. 
     *
     * @param callable(T):bool $closure         The closure to call.
     *
     * @return T|null                           Returns the specified value or `NULL`.
     */
    function find(callable $closure): mixed;
}
