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

use gabbro\feature\Serializable;
use gabbro\feature\Cloneable;

/**
 * Defines a basic argv argument.
 */
interface Argument extends Serializable, Cloneable {

    /**
     * Check to see if this argument was in the argv.
     *
     * @param bool|null $state      Check if this is set or change the state by passing true|false.
     *
     * @return bool                 Returns the current or new state
     */
    function isSet(bool|null $state = null): bool;
    
    /**
     * Get the title for this argument. 
     *
     * @return string
     */
    function getTitle(): string|null;
    
    /**
     * Set a title for this argument.
     *
     * @param string $title      The title to set.
     *
     * @return void
     */
    function setTitle(string $title): void;
    
    /**
     * Get the description for this argument.
     *
     * @return string|null      Returns NULL if no description has been set.
     */
    function getDescription(): string|null;
    
    /**
     * Set a description for this argument.
     *
     * @param string $desc      The description to set.
     *
     * @return void
     */
    function setDescription(string $desc): void;
}

