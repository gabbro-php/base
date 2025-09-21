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

use gabbro\exception\DatasetException;
use gabbro\feature\Indexable;
use gabbro\feature\Enumerable;
use gabbro\feature\Serializable;
use gabbro\feature\Cloneable;

/**
 * Defines a immutable mapped array.
 * 
 * @template K
 * @template V
 * @extends Indexable<K,V>
 * @extends Enumerable<K,V>
 */
interface ImmutableMappedArray extends Indexable, Enumerable, Serializable, Cloneable {

    /**
     * Parse the dataset into a PHP Array.
     *
     * Builds a PHP array containing all of the current values within
     * the dataset. If the dataset is empty, and empty array is returned.
     *
     * @return array<K,V>
     */
    function toArray(): array;

    /**
     * Get the current length of the dataset.
     *
     * @return int
     */
    function length(): int;
    
    /**
     * Check to see if a certain key exist.
     *
     * @param K $key      The key position to check.
     *
     * @return bool
     */
    function isSet(mixed $key): bool;
    
    /**
     * Get the value at a specified position.
     *
     * @param K $key      The key position to get.
     *
     * @return V
     *
     * @throws DatasetException     If the key position does not exist.
     */
    function get(mixed $key): mixed;
    
    /**
     * Get the value at a specified position.
     *
     * Unlike `get()` this will not throw exception on missing key. 
     * Instead you can declare a default value to be returned. 
     *
     * @param K $key            The key position to get.
     * @param V|null $default   Default value to return.
     *
     * @return ($default is null ? V|null : V)
     */
    function getOr(mixed $key, mixed $default = null): mixed;
    
    /**
     * Get the key for the first occurrence of a value.
     *
     * This will do a strict search e.g. `FALSE != 0`.
     *
     * @param V $value                  Value to search for.
     *
     * @return (K)|null       The key or `NULL` if the value does not exist.
     */
    function getKey(mixed $value): mixed;
    
    /**
     * Get a list of all values assigned to this dataset.
     *
     * @return ImmutableArray<V>
     */
    function getValues(): ImmutableArray;

    /**
     * Get a list of all keys assigned to this dataset.
     *
     * @return ImmutableArray<K>
     */
    function getKeys(): ImmutableArray;
    
    /**
     * Traverses the dataset.
     *
     * This will traverse the dataset and call the closure on each index. 
     * If the closure returns `true` at any time, then the traversal ends. 
     * Return `false` to keep the traversal going.
     *
     * @note                                      Values are sent by reference.
     *
     * @param callable(K,V):bool $closure         The closure to call.
     *
     * @return bool                               Returns `false` if the traversal was terminated or `true` otherwise.
     */
    function traverse(callable $closure): bool;
}

