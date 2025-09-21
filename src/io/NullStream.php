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

use gabbro\exception\IOException;
use gabbro\io\Stream\Metadata;

/**
 * A Stream implementation that points to nothing.
 *
 * This stream will never actually write anything and
 * any reads will just return random bytes. It has no length, will never
 * reach EOF, will never be filled. It's equal to `/dev/urandom` when reading and 
 * `/dev/null` when writing. Good for test cases.  
 */
class NullStream extends BaseStream {

    /**
     * {inheritdoc}
     *
     * @override {@see Stream::getLength()}
     */
    public function getLength(): int {
        return -1;
    }
    
    /**
     * {inheritdoc}
     *
     * @override {@see Stream::getMetadata()}
     */
    public function getMetadata(): Metadata {
        return new Metadata(
            timedOut:       false,
            blocked:        false,
            eof:            false,
            unreadBytes:    0,
            streamType:     "TEMP",
            wrapperType:    "PHP",
            wrapperData:    null,
            mode:           "r+",
            seekable:       true,
            uri:            "php://temp"
        );
    }
    
    /**
     * {inheritdoc}
     *
     * @override {@see Stream::getPosition()}
     */
    public function getPosition(): int {
        return 0;
    }
    
    /**
     * {inheritdoc}
     *
     * @override {@see Stream::moveTo()}
     */
    public function moveTo(int $offset, int $whence = Stream::MOVE_SET): bool {
        return true;
    }
    
    /**
     * {inheritdoc}
     *
     * @override {@see Stream::truncate()}
     */
    public function truncate(int $size): bool {
        return true;
    }
    
    /**
     * {inheritdoc}
     *
     * @override {@see Closeable::close()}
     */
    public function close(): void {}
    
    /**
     * {inheritdoc}
     *
     * @override {@see Stream::write()}
     */
    public function write(string|Stream $data): int {
        if (is_string($data)) {
            return strlen($data);
        }
        
        return parent::write($data);
    }
    
    /**
     * {inheritdoc}
     *
     * @override {@see Stream::read()}
     */
    public function read(int $length, bool $allowBlocking = true): string {
        return random_bytes($length);
    }
    
    /**
     * {inheritdoc}
     *
     * @override {@see Stream::readLine()}
     */
    public function readLine(int $maxlen = 0, bool $trim = false): string {
        return random_bytes($maxlen > 0 ? $maxlen : 4096);
    }
    
    /**
     * {inheritdoc}
     *
     * @override {@see Stream::readAll()}
     */
    public function readAll(): string {
        return random_bytes(4096);
    }
    
    /**
     * {inheritdoc}
     *
     * @override {@see Stream::isEOF()}
     */
    public function isEOF(): bool {
        return false;
    }
    
    /**
     * {inheritdoc}
     *
     * @override {@see Stringable::toString()}
     */
    public function toString(): string {
        return "";
    }
}

