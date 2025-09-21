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

use gabbro\feature\Closeable;
use gabbro\feature\Stringable;
use gabbro\exception\IOException;
use gabbro\collection\ImmutableMappedArray;
use gabbro\io\Stream\Metadata;
use gabbro\io\Stream\Ready;

/**
 * Defines a Stream object.
 */
interface Stream extends Stringable, Closeable {

    /**
     * Writable modes
     */
    const WRITABLE_MODES = '/r(?:b)?\+|[waxc]/';

    /**
     * Readable modes
     */
    const READABLE_MODES = '/[waxc](?:b)?\+|r/';
    
    /**
     * Stream is readable.
     *
     * @var int = 0b100
     */
    const MODE_READABLE = 0b100;

    /**
     * Stream is writable.
     *
     * @var int = 0b1000
     */
    const MODE_WRITABLE = 0b1000;

    /**
     * Stream pointer is movable.
     *
     * @var int = 0b10000
     */
    const MODE_MOVABLE = 0b10000;
    
    /**
     * Stream is readable and writable (Multi bit)
     *
     * @var int = 0b1100
     */
    const MODE_UNI = 0b1100;
    
    /**
     * Stream is readable, writable and pointer is movable (Multi bit)
     *
     * @var int = 0b11100
     */
    const MODE_ALL = 0b11100;
    
    /**
     * Set position equal to offset bytes from the start of the file.
     *
     * @var int = \SEEK_SET
     */
    const MOVE_SET = \SEEK_SET;
    
    /**
     * Set position to current location plus offset bytes.
     *
     * @var int = \SEEK_CUR
     */
    const MOVE_CUR = \SEEK_CUR;
    
    /**
     * Set position to end-of-file plus offset bytes.
     *
     * @var int = \SEEK_END
     */
    const MOVE_END = \SEEK_END;
    
    /**
     * Get a PHP Resource pointing at this Stream.
     *
     * This will create a {@see StreamResource} that wraps this Stream. 
     * This allows using some of the normal file operation like `fwrite`,
     * regardless of the Stream type.
     *
     * @return resource
     */
    function getResource() /*resource*/;
    
    /**
     * Get the mode flags for the current resource.
     *
     * You can use the `Stream::MODE_` constants to sort
     * out the different bits and their meaning.
     *
     * @param int-mask-of<Stream::MODE_*> $mask     Mask to filter the flags before returning them.
     *
     * @return int-mask-of<Stream::MODE_*>          Returns `0` if the stream is closed.
     */
    function getFlags(int $mask = Stream::MODE_ALL): int;
    
    /**
     * Returns the mode used by this stream.
     *
     * This is a string representation of the modes like `r+`.
     * You can use the dedicated methods instead such as `{@see Stream::isReadable()}`.
     *
     * @return string|null      Returns `NULL` if the stream is closed.
     */
    function getMode(): string|null;
    
    /**
     * Check if the stream is writable.
     *
     * @see Stream::getMode()
     *
     * @return bool
     */
    function isWritable(): bool;

    /**
     * Check if the stream is readable.
     *
     * @see Stream::getMode()
     *
     * @return bool
     */
    function isReadable(): bool;

    /**
     * Check if the stream pointer is movable.
     *
     * @see Stream::getMode()
     *
     * @return bool
     */
    function isMovable(): bool;
    
    /**
     * Check if the stream is a remote stream.
     *
     * @return bool
     */
    function isRemote(): bool;
    
    /**
     * Get the length of the stream.
     *
     * @return int      Returns `0` if the stream is closed or `-1` if the length is unknown.
     */
    function getLength(): int;
    
    /**
     * Get the stream pointer position.
     *
     * @return int      Returns `-1` if position could not be determined.
     */
    function getPosition(): int;
    
    /**
     * Check to see if the stream pointer position is at the end.
     *
     * @return bool
     */
    function isEOF(): bool;
    
    /**
     * Move the pointer to a different position.
     *
     * @note                                Writing to the current offset will override any existing data below.
     *
     * @param int $offset                   A new position for the pointer.
     *
     * @param Stream::MOVE_* $whence        {@see https://secure.php.net/manual/function.fseek.php}
     *
     * @return bool                         May fail if the stream pointer is not movable.
     */
    function moveTo(int $offset, int $whence = Stream::MOVE_SET): bool;
    
    /**
     * Move the pointer to the beginning.
     *
     * This is just an alias of `moveTo(0, Stream::MOVE_SET)`.
     *
     * @return bool
     */
    function moveToStart(): bool;
    
    /**
     * Move the pointer to the end.
     *
     * This is just an alias of `moveTo(0, Stream::MOVE_END)`.
     *
     * @return bool
     */
    function moveToEnd(): bool;
    
    /**
     * Write data to the stream.
     *
     * @note                            When writing a stream, the pointer is not reset. 
     *                                  The stream will be read from the position where the pointer is.
     *                                  This is by design to avoid issue on non-movable streams as well as being able
     *                                  to control what should be written.
     *
     * @param string|Stream $data       Data to write.

     * @return int<0,max>               Returns the number of bytes written.
     *
     * @throws IOException              On error or if the stream is closed. 
     */
    function write(string|Stream $data): int;

    /**
     * Read bytes from the stream.
     *
     * @param int<1,max> $length            Number of bytes to read.
     * @param bool $allowBlocking           Declare whether this read is allowed block or not.
     *
     * @return string                       The bytes that was read or empty string '' on EOF.
     *
     * @throws IOException                  On error or if the stream is closed. 
     */
    function read(int $length, bool $allowBlocking = true): string;

    /**
     * Read a line from the stream.
     *
     * @note                            Not all file types has lines endings. 
     *                                  Be careful when using this as trying to read a very large binary file
     *                                  can end up consuming a lot of memory.
     *
     * @param int<0,max> $maxlen        Max bytes to read before stop.
     *                                  Parse `0` to set no read limit.
     *
     * @param bool $trim                If `true` do not return line break characters. 
     *                                  Only enable trim if you do not need to distinguish between
     *                                  empty line and EOF. 
     *
     * @return string                   The bytes that was read or empty string '' on EOF.
     *
     * @throws IOException              On error or if the stream is closed. 
     */
    function readLine(int $maxlen = 0, bool $trim = false): string;
    
    /**
     * Read the entire stream.
     *
     * This will read from current position and to the end
     * of the stream. 
     *
     * @return string                   The bytes that was read or empty string '' on EOF.
     *
     * @throws IOException              On error or if the stream is closed. 
     */
    function readAll(): string;
    
    /**
     * Wait for the stream to become ready.
     *
     * This method waits and returns when the stream is ready, 
     * or when the timeout has passed. You can use the result object
     * of this call to check if and what has become ready and avoid
     * a write to loose data or a read to hang.
     *
     * @param float $timeout    Timeout in seconds.
     *
     * @return Ready
     */
    function wait(float $timeout = 0.1): Ready;
    
    /**
     * Clear the entire stream.
     *
     * This is an alias of `truncate(0)`.
     *
     * @return bool             Returns `false` if the pointer is not movable.
     *
     * @throws IOException      On error or if the stream is closed. 
     */
    function clear(): bool;

    /**
     * Truncates a file to certain length.
     *
     * @param int<0,max> $size          Length to truncate to.
     *
     * @return bool                     Returns `false` if the pointer is not movable.
     *
     * @throws IOException              On error or if the stream is closed. 
     */
    function truncate(int $size): bool;
    
    /**
     * Get the metadata for the stream.
     *
     * @see https://www.php.net/manual/function.stream-get-meta-data
     *
     * @return Metadata
     */
    function getMetadata(): Metadata;
}

