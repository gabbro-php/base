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

/**
 * Extends the immutable structured array with mutable options.
 *
 * @template T
 * @extends MutableArray<T>
 * @extends ImmutableStructuredArray<T>
 */
interface MutableStructuredArray extends MutableArray, ImmutableStructuredArray {

    /**
     * Set the value on a specified position.
     *
     * Unlike `add()`, this will set the value for a specified position
     * instead of appending the value to the end.
     *
     * @note                    You can use negative position to walk right to left.
     *                          For an example `-1` would be the last element.
     *
     * @note                    The key must be either an existing position or represent the end of the dataset.
     *                          You cannot insert data into position `10` if the dataset has a length of `7`.
     *
     * @param int $pos          The position where to set the value.
     * @param T $value          The value to add.
     *
     * @return T|null           The current value.
     *
     * @throws DatasetException If the position is out of range.
     */
    function set(int $pos, mixed $value): mixed;

    /**
     * Remove the element at a specified position.
     *
     * @note                        You can use negative position to walk right to left.
     *                              For an example `-1` would be the last element.
     *
     * @param $pos                  The position to remove.
     *
     * @return T                    The current value.
     *
     * @throws DatasetException     If the position is out of range.
     */
    function unset(int $pos): mixed;

    /**
     * Insert a value into a specified position.
     *
     * Unlike `set()` this will not override the existing
     * position. Instead it will move the dataset from that position a step to the right.
     *
     * @note                        You can use negative position to walk right to left.
     *                              For an example `-1` would be the last element.
     *
     * @param int $pos              The position to insert into.
     * @param T $value              The value to insert.
     *
     * @throws DatasetException     If the position is out of range.
     */
    function insert(int $pos, mixed $value): void;
}
