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
use gabbro\feature\Cloneable;

/**
 * A small table class that contains keys with no associated values.
 * Keys are either in the table or they are not. 
 *
 * @template T
 * @extends Enumerable<int,T&array-key>
 */
interface ImmutableKeyTable extends Enumerable, Serializable, Cloneable {

    /**
     * Check to see if a key is set.
     *
     * @param T&array-key $key
     *
     * @return bool
     */
    function isSet(mixed $key): bool;
    
    /**
     * Get an array with all of the current keys.
     *
     * @return array<int,T&array-key>
     */
    function toArray(): array;
}

