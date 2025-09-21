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

use gabbro\io\Stream;

/**
 * Container for the readiness state of a {@see Stream} after a select/wait operation.
 *
 * This class represents whether a given stream is ready for reading, writing,
 * or has encountered an exceptional condition. It uses bitwise flags so that
 * multiple states can be combined.
 */
final class Ready {

    /**
     * The stream is ready for reading.
     */
    public const READY_READ   = 0b001;

    /**
     * The stream is ready for writing.
     */
    public const READY_WRITE  = 0b010;

    /**
     * The stream has an exceptional condition (e.g. socket OOB data).
     */
    public const READY_EXCEPT = 0b100;

    /**
     * Create a new readiness object.
     *
     * @param Stream $stream                            The stream associated with this readiness state.
     * @param int-mask-of<Ready::READY_*> $flags        Bitmask of READY_* flags indicating readiness.
     */
    public function __construct(
        /**
         * The stream associated with this readiness state.
         *
         * @readonly
         */
        public Stream $stream,

        /**
         * Bitmask of flags describing the readiness state.
         *
         * @var int-mask-of<Ready::READY_*>
         * @readonly
         */
        public int $flags
    ) {}

    /**
     * Check if the stream is ready to read.
     *
     * @return bool 
     */
    public function isReadable(): bool {
        return (bool) ($this->flags & self::READY_READ);
    }

    /**
     * Check if the stream is ready to write.
     *
     * @return bool 
     */
    public function isWritable(): bool {
        return (bool) ($this->flags & self::READY_WRITE);
    }

    /**
     * Check if the stream has an exceptional condition.
     *
     * @return bool
     */
    public function isExceptional(): bool {
        return (bool) ($this->flags & self::READY_EXCEPT);
    }
}

