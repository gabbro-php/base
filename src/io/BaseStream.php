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
use gabbro\exception\IOException;
use gabbro\collection\ImmutableMappedArray;
use gabbro\io\Stream\Metadata;
use gabbro\io\Stream\Ready;

/**
 * Base implementation of the Stream interface.
 */
abstract class BaseStream implements Stream {

    /**
     * {@inheritdoc}
     *
     * @override {@see Stream::getResource()}
     */
    public function getResource() /*resource*/ {
        return StreamWrapper::getResource($this);
    }
    
    /**
     * {@inheritdoc}
     *
     * @override {@see Stream::isWritable()}
     */
    public function isWritable(): bool {
        return $this->getFlags(Stream::MODE_WRITABLE) > 0;
    }

    /**
     * {@inheritdoc}
     *
     * @override {@see Stream::isReadable()}
     */
    public function isReadable(): bool {
        return $this->getFlags(Stream::MODE_READABLE) > 0;
    }

    /**
     * {@inheritdoc}
     *
     * @override {@see Stream::isMovable()}
     */
    public function isMovable(): bool {
        return $this->getFlags(Stream::MODE_MOVABLE) > 0;
    }
    
    /**
     * {@inheritdoc}
     *
     * @override {@see Stream::isRemote()}
     */
    public function isRemote(): bool {
        return false;
    }
    
    /**
     * {inheritdoc}
     *
     * @override {@see Stream::isEOF()}
     */
    public function isEOF(): bool {
        return $this->getMetadata()->eof;
    }
    
    /**
     * {@inheritdoc}
     *
     * @override {@see Stream::getFlags()}
     */
    public function getFlags(int $mask = Stream::MODE_ALL): int {
        $meta = $this->getMetadata();
        
        if (!empty($meta->mode)) {
            $flags = 0;
            $flags |= preg_match(Stream::READABLE_MODES, $meta->mode) ? Stream::MODE_READABLE : 0;
            $flags |= preg_match(Stream::WRITABLE_MODES, $meta->mode) ? Stream::MODE_WRITABLE : 0;
            $flags |= $meta->seekable ? Stream::MODE_MOVABLE : 0;
            
            return $flags & $mask;
        }
        
        return 0;
    }
    
    /**
     * {@inheritdoc}
     *
     * @override {@see Stream::getMode()}
     */
    public function getMode(): string|null {
        $meta = $this->getMetadata();
        
        if (!empty($meta->mode)) {
            return $meta->mode;
        }
        
        return null;
    }
    
    /**
     * {@inheritdoc}
     *
     * @override {@see Closeable::isClosed()}
     */
    public function isClosed(): bool {
        // If a stream is neither readable nor writable, then it is effectively closed. 
        return $this->getFlags(Stream::MODE_WRITABLE|Stream::MODE_READABLE) == 0;
    }
    
    /**
     * {@inheritdoc}
     *
     * @override {@see Stream::moveToStart()}
     */
    public function moveToStart(): bool {
        return $this->moveTo(0, Stream::MOVE_SET);
    }
    
    /**
     * {@inheritdoc}
     *
     * @override {@see Stream::moveToEnd()}
     */
    public function moveToEnd(): bool {
        return $this->moveTo(0, Stream::MOVE_END);
    }
    
    /**
     * {@inheritdoc}
     *
     * @override {@see Stream::clear()}
     */
    public function clear(): bool {
        return $this->truncate(0);
    }
    
    /**
     * {inheritdoc}
     *
     * @override {@see Stream::getMetadata()}
     */
    public function getMetadata(): Metadata {
        static $meta = new Metadata();
        return $meta;
    }
    
    /**
     * {inheritdoc}
     *
     * This implementation only implements writing a {@see Stream}. 
     * It still depends on the main class to extend this and do the 
     * string writes. 
     *
     * @override {@see Stream::write()}
     */
    public function write(string|Stream $data): int {
        if ($data instanceof Stream && $data->getFlags(Stream::MODE_READABLE)) {
            $out = 0;
            
            while (!$data->isEOF()) {
                $bytes = $data->read(16384);
                
                if (($count = $this->write($bytes)) != strlen($bytes)) {
                    throw new IOException("Failed writing from a stream");
                }

                $out += $count;
            }
            
            return $out;
            
        } else if ($data instanceof Stream) {
            throw new IOException("Trying to write from a closed or write-only stream");
        }
        
        return 0;
    }
    
    /**
     * {inheritdoc}
     *
     * @override {@see Stream::readAll()}
     */
    public function readAll(): string {
        if ($this->getFlags(Stream::MODE_READABLE)) {
            $bytes = "";
            
            while (!$this->isEOF()) {
                $bytes .= $this->read(16384);
            }
            
            return $bytes;
        }
        
        throw new IOException("Trying to read from a closed or write-only stream");
    }
    
    /**
     * {inheritdoc}
     *
     * @override {@see Stream::wait()}
     */
    public function wait(float $timeout = 0.1): Ready {
        $flags = $this->getFlags(Stream::MODE_READABLE|Stream::MODE_WRITABLE);
        
        if ($flags == 0) {
            throw new IOException("Trying to wait on a closed stream");
        }
        
        $flags = $this->getFlags();
        $ret = 0;

        if ($flags & Stream::MODE_READABLE) {
            $ret |= Ready::READY_READ;
        }

        if ($flags & Stream::MODE_WRITABLE) {
            $ret |= Ready::READY_WRITE;
        }
        
        return new Ready($this, $ret);
    }
    
    /**
     * {inheritdoc}
     *
     * @override {@see Stringable::toString()}
     */
    public function toString(): string {
        $flags = Stream::MODE_READABLE | Stream::MODE_MOVABLE;
        $output = "";
    
        if ($this->getFlags($flags) == $flags) {
            $pos = $this->getPosition();
            $this->moveTo(0);
            
            while (!$this->isEOF()) {
                $output .= $this->read(16384);
            }
            
            $this->moveTo($pos);
            
        } else if ($this->getFlags(Stream::MODE_READABLE)) {
            while (!$this->isEOF()) {
                $output .= $this->read(16384);
            }
        }

        return $output;
    }
    
    /**
     * @ignore
     * @return string
     */
    public function __toString(): string {
        return $this->toString();
    }
}

