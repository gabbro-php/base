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

namespace gabbro\io\Shell;

use gabbro\io\Stream;
use gabbro\io\IOStream;
use gabbro\io\VariableStream;

/**
 * Immutable result object returned by {@see \gabbro\io\Shell::exec()}.
 *
 * Encapsulates the process exit code and captured stdout/stderr buffers.
 * Provides access to the raw strings as well as {@see Stream} wrappers
 * for convenient line-oriented reading or stream processing.
 */
class Result {

    /**
     * @param int    $exitcode      Exit code returned by the process.
     * @param string $stdout        Captured stdout data.
     * @param string $stderr        Captured stderr data.
     */
    public function __construct(
        /**
         * @var int
         */
        private int $exitcode,
        
        /**
         * @var string
         */
        private string &$stdout,
        
        /**
         * @var string
         */
        private string &$stderr
    
    ) {}
    
    /**
     * Get the exit code of the process.
     *
     * @return int              Exit code (0 indicates success by convention).
     */
    public function getExitCode(): int {
        return $this->exitcode;
    }
    
    /**
     * Whether the process completed successfully.
     *
     * @return bool             True if exit code == 0, false otherwise.
     */
    public function isSuccessful(): bool {
        return $this->exitcode == 0;
    }
    
    /**
     * Get the captured output as a stream.
     *
     * @param (1|2) $stream             One of IOStream::STDOUT or IOStream::STDERR.
     * @return Stream
     */
    public function getStream(int $stream): Stream {
        return match ($stream) {
            IOStream::STDOUT => new VariableStream($this->stdout),
            IOStream::STDERR => new VariableStream($this->stderr)
        };
    }
}

