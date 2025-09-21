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

use gabbro\feature\Stringable;
use gabbro\feature\Closeable;
use gabbro\exception\IOException;
use gabbro\io\Stream\Metadata;
use gabbro\io\Stream\Ready;

/**
 * Abstract class for easy implementation of custom Streams.
 */
abstract class StreamDecorator implements Stream {

    /**
     * Return the backed Stream instance.
     *
     * @return Stream
     *
     * @throws IOException      If the stream cannot be provided. 
     */
    public abstract function getStream(): Stream;

    /**
     * {inheritdoc}
     *
     * @override {@see Stream::getResource()}
     */
    public function getResource() /*resource*/ {
        return $this->getStream()->getResource();
    }
    
    /**
     * {inheritdoc}
     *
     * @override {@see Stream::getFlags()}
     */
    public function getFlags(int $mask = Stream::MODE_ALL): int {
        return $this->getStream()->getFlags($mask);
    }
    
    /**
     * {inheritdoc}
     *
     * @override {@see Stream::getMode()}
     */
    public function getMode(): string|null {
        return $this->getStream()->getMode();
    }
    
    /**
     * {inheritdoc}
     *
     * @override {@see Stream::isWritable()}
     */
    public function isWritable(): bool {
        return $this->getStream()->isWritable();
    }

    /**
     * {inheritdoc}
     *
     * @override {@see Stream::isReadable()}
     */
    public function isReadable(): bool {
        return $this->getStream()->isReadable();
    }

    /**
     * {inheritdoc}
     *
     * @override {@see Stream::isMovable()}
     */
    public function isMovable(): bool {
        return $this->getStream()->isMovable();
    }
    
    /**
     * {@inheritdoc}
     *
     * @override {@see Stream::isRemote()}
     */
    public function isRemote(): bool {
        return $this->getStream()->isRemote();
    }
    
    /**
     * {inheritdoc}
     *
     * @override {@see Stream::getLength()}
     */
    public function getLength(): int {
        return $this->getStream()->getLength();
    }
    
    /**
     * {inheritdoc}
     *
     * @override {@see Stream::getPosition()}
     */
    public function getPosition(): int {
        return $this->getStream()->getPosition();
    }
    
    /**
     * {inheritdoc}
     *
     * @override {@see Stream::isEOF()}
     */
    public function isEOF(): bool {
        return $this->getStream()->isEOF();
    }
    
    /**
     * {inheritdoc}
     *
     * @override {@see Stream::moveTo()}
     */
    public function moveTo(int $offset, int $whence = Stream::MOVE_SET): bool {
        return $this->getStream()->moveTo($offset, $whence);
    }
    
    /**
     * {inheritdoc}
     *
     * @override {@see Stream::moveToStart()}
     */
    public function moveToStart(): bool {
        return $this->getStream()->moveToStart();
    }
    
    /**
     * {inheritdoc}
     *
     * @override {@see Stream::moveToEnd()}
     */
    public function moveToEnd(): bool {
        return $this->getStream()->moveToEnd();
    }
    
    /**
     * {inheritdoc}
     *
     * @override {@see Stream::write()}
     */
    public function write(string|Stream $data): int {
        return $this->getStream()->write($data);
    }

    /**
     * {inheritdoc}
     *
     * @override {@see Stream::read()}
     */
    public function read(int $length, bool $allowBlocking = true): string {
        return $this->getStream()->read($length, $allowBlocking);
    }

    /**
     * {inheritdoc}
     *
     * @override {@see Stream::readLine()}
     */
    public function readLine(int $maxlen = 0, bool $trim = false): string {
        return $this->getStream()->readLine($maxlen, $trim);
    }
    
    /**
     * {inheritdoc}
     *
     * @override {@see Stream::readAll()}
     */
    public function readAll(): string {
        return $this->getStream()->readAll();
    }
    
    /**
     * {inheritdoc}
     *
     * @override {@see Stream::wait()}
     */
    public function wait(float $timeout = 0.1): Ready {
        return $this->getStream()->wait($timeout);
    }
    
    /**
     * {inheritdoc}
     *
     * @override {@see Stream::clear()}
     */
    public function clear(): bool {
        return $this->getStream()->clear();
    }

    /**
     * {inheritdoc}
     *
     * @override {@see Stream::truncate()}
     */
    public function truncate(int $size): bool {
        return $this->getStream()->truncate($size);
    }
    
    /**
     * {inheritdoc}
     *
     * @override {@see Stream::getMetadata()}
     */
    public function getMetadata(): Metadata {
        return $this->getStream()->getMetadata();
    }
    
    /**
     * {inheritdoc}
     *
     * @override {@see Closeable::close()}
     */
    public function close(): void {
        $this->getStream()->close();
    }

    /**
     * {inheritdoc}
     *
     * @override {@see Closeable::isClosed()}
     */
    public function isClosed(): bool {
        return $this->getStream()->isClosed();
    }
    
    /**
     * {inheritdoc}
     *
     * @override {@see Stringable::toString()}
     */
    public function toString(): string {
        return $this->getStream()->toString();
    }
    
    /**
     * @ignore
     */
    public function __toString(): string {
        return $this->getStream()->__toString();
    }
}

