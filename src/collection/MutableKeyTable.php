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

use gabbro\feature\Serializable;
use gabbro\feature\Enumerable;

/**
 * A mutable version of the key table interface.
 *
 * @template T
 * @extends ImmutableKeyTable<T>
 */
interface MutableKeyTable extends ImmutableKeyTable {

    /**
     * Set the state of a key.
     *
     * @param T&array-key $key      The key to change.
     * @param bool $flag            The state for this key.
     *  
     * @return bool                 Returns the previous state.
     */
    function set(mixed $key, bool $flag = true): bool;
    
    /**
     * Clear all keys in the table.
     */
    function clear(): void;
    
    /**
     * Set multiple keys from an iterable.
     *
     * This will use the values from the iterable
     * as the keys.
     *
     * @param iterable<T&array-key> $itt
     *
     * @return void
     */
    function addIterable(iterable $itt): void;
}

