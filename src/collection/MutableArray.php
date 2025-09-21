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

/**
 * Extends the immutable unstructured array with mutable options.
 *
 * @template T
 * @extends ImmutableArray<T>
 */
interface MutableArray extends ImmutableArray {

    /**
     * Clear the dataset.
     *
     * @return void
     */
    function clear(): void;

    /**
     * Add a value to the dataset.
     *
     * @param T $value    Value to add to the dataset.
     *
     * @return void
     */
    function add(mixed $value): void;

    /**
     * Remove a value from the dataset.
     *
     * @note                This will remove all occurrences of the value.
     *
     * @param T $value      Value to remove.
     *
     * @return int          Returns the number of removed items.
     */
    function remove(mixed $value): int;

    /**
     * Add values from an iterable object or array.
     *
     * @param iterable<T> $itt
     *
     * @return void
     */
    function addIterable(iterable $itt): void;
}
