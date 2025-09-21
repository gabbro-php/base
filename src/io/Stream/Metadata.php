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

namespace gabbro\io\Stream;

/**
 * Interfaced wrapper for PHP's header/meta data from streams/file pointers.
 *
 * @see https://www.php.net/manual/function.stream-get-meta-data.php
 */
final class Metadata {
    /**
     * @ignore
     */
    public function __construct(
        /**
         * @var bool
         * @readonly
         */
        public bool $timedOut = false,
        
        /**
         * @var bool
         * @readonly
         */
        public bool $blocked = false,
        
        /**
         * @var bool
         * @readonly
         */
        public bool $eof = true,
        
        /**
         * @var int
         * @readonly
         */
        public int $unreadBytes = 0,
        
        /**
         * @var string
         * @readonly
         */
        public string $streamType = "",
        
        /**
         * @var string
         * @readonly
         */
        public string $wrapperType = "",
        
        /**
         * @var mixed|null
         * @readonly
         */
        public mixed $wrapperData = null,
        
        /**
         * @var string
         * @readonly
         */
        public string $mode = "",
        
        /**
         * @var bool
         * @readonly
         */
        public bool $seekable = false,
        
        /**
         * @var string
         * @readonly
         */
        public string $uri = "",
        
        /**
         * @var array<string,mixed>|null
         * @readonly
         */
        public array|null $crypto = null
    ) {}
}

