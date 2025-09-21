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

/**
 * Defines an immutable structured list
 *
 * @template T
 * @extends ImmutableArray<T>
 * @extends Indexable<int,T>
 */
interface ImmutableStructuredArray extends ImmutableArray, Indexable {

    /**
     * Find the positional key for a value.
     *
     * This returns the position of the first match of a value.
     * It uses strict search e.g. `FALSE != 0`.
     *
     * @param T $value              Value to search for.
     * @param int $offset           Begin from offset. Can also be negative for right to left.
     *
     * @return int                  Returns `-1` if no match was found.
     *
     * @throws DatasetException     If the position is out of range.
     */
    function indexOf(mixed $value, int $offset = 0): int;

    /**
     * Check to see if a specified position exists.
     *
     * @note                        You can use negative position to walk right to left.
     *                              For an example `-1` would be the last element.
     *
     * @param int $pos              The position to check.
     *
     * @return bool
     */
    function isSet(int $pos): bool;

    /**
     * Get the value at a specified position.
     *
     * @note                        You can use negative position to walk right to left.
     *                              For an example `-1` would be the last element.
     *
     * @param int $pos              The position to get.
     *
     * @return T
     *
     * @throws DatasetException     If the position is out of range.
     */
    function get(int $pos): mixed;
    
    /**
     * Get the value at a specified position.
     *
     * Unlike `get()` this will not throw exception on missing key. 
     * Instead you can declare a default value to be returned. 
     *
     * @note                        You can use negative position to walk right to left.
     *                              For an example `-1` would be the last element.
     *
     * @param int $pos              The position to get.
     *
     * @return ($default is null ? T|null : T)
     */
    function getOr(int $pos, mixed $default = null): mixed;
    
    /**
     * Sort the dataset.
     *
     * Sort the internal dataset using PHP's usort(). 
     *
     * @param callable(T $a, T $b):int $closure
     */
    function sort(callable $closure): void;
}
