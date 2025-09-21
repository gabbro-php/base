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

namespace gabbro\io;


/**
 * A real interface for PHP's Streamwrapper.
 *
 * This interface describes a data stream resource.
 * It does not include features for working with the actual
 * file, directory etc. It reads and writes data, it does not rename files, 
 * create directories or change file permissions.
 *
 * @see https://www.php.net/manual/class.streamwrapper.php
 *
 * @property resource $context
 */
interface PHPResource {

    /**
     * Opens file or URL
     *
     * @see https://www.php.net/manual/streamwrapper.stream-open.php
     *
     * @param string $url
     * @param string $mode
     * @param int $options
     * @param string|null &$opened_path = null
     *
     * @return bool
     */
    function stream_open(string $url, string $mode, int $options, string|null &$opened_path = null): bool;
    
    /**
     * Close a resource
     *
     * @see https://www.php.net/manual/streamwrapper.stream-close.php
     *
     * @return void
     */
    function stream_close(): void;
    
    /**
     * Write to stream
     *
     * @see https://www.php.net/manual/streamwrapper.stream-write.php
     *
     * @param string $data
     *
     * @return int
     */
    function stream_write(string $data): int;
    
    /**
     * Read from stream
     *
     * @see https://www.php.net/manual/streamwrapper.stream-read.php
     *
     * @param int<1, max> $length
     *
     * @return string|false
     */
    function stream_read(int $length): string|false;
    
    /**
     * Tests for end-of-file on a file pointer
     *
     * @see https://www.php.net/manual/streamwrapper.stream-eof.php
     *
     * @return bool
     */
    function stream_eof(): bool;
    
    /**
     * Retrieve the current position of a stream
     *
     * @see https://www.php.net/manual/streamwrapper.stream-tell.php
     *
     * @return int
     */
    function stream_tell(): int;
    
    /**
     * Seeks to specific location in a stream
     *
     * @see https://www.php.net/manual/streamwrapper.stream-seek.php
     *
     * @param int $offset
     * @param 0|1|2 $whence = \SEEK_SET
     *
     * @return bool
     */
    function stream_seek(int $offset, int $whence = SEEK_SET): bool;
    
    /**
     * Truncate stream
     *
     * @see https://www.php.net/manual/streamwrapper.stream-truncate.php
     *
     * @param int<0, max> $size
     *
     * @return bool
     */
    function stream_truncate(int $size): bool;
    
    /**
     * Retrieve information about a file resource
     *
     * @see https://www.php.net/manual/streamwrapper.stream-stat.php
     *
     * @return array<string,int>|false
     */
    function stream_stat(): array|false;
    
    /**
     * Flushes the output
     *
     * @see https://www.php.net/manual/streamwrapper.stream-flush.php
     *
     * @return bool
     */
    function stream_flush(): bool;
}

