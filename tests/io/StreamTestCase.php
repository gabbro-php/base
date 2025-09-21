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
namespace gabbro\test\io;


use PHPUnit\Framework\TestCase;
use gabbro\io\Stream;
use gabbro\io\Stream\Metadata;
use gabbro\exception\IOException;

/**
 * Base test case for all Stream implementations.
 *
 * Extend this in each concrete stream test class
 * and implement createStream() to return a fresh Stream instance.
 */
abstract class StreamTestCase extends TestCase
{
    /**
     * Create a new stream instance for testing.
     *
     * @return Stream
     */
    abstract protected function createStream(): Stream;

    public function testWritableAndReadable(): void {
        $stream = $this->createStream();

        $this->assertTrue($stream->isWritable() || $stream->isReadable());

        if ($stream->isWritable()) {
            $bytes = $stream->write("hello");
            $this->assertSame(5, $bytes);
        }

        if ($stream->isReadable()) {
            $stream->moveToStart();
            $data = $stream->read(5);
            $this->assertSame("hello", $data);
        }
        
        $stream->close();
    }

    public function testMoveAndPosition(): void {
        $stream = $this->createStream();

        if ($stream->isMovable()) {
            $stream->write("abcdef");
            $stream->moveTo(2);
            $this->assertSame(2, $stream->getPosition());

            $ch = $stream->read(1);
            $this->assertSame("c", $ch);
        } else {
            $this->markTestSkipped("Stream is not movable");
        }
        
        $stream->close();
    }

    public function testEOF(): void {
        $stream = $this->createStream();
        $stream->write("xyz");
        $stream->moveToStart();

        $this->assertFalse($stream->isEOF());
        $stream->read(4);
        $this->assertTrue($stream->isEOF());
        
        $stream->close();
    }

    public function testTruncateAndClear(): void {
        $stream = $this->createStream();

        if ($stream->isWritable()) {
            $stream->write("abcdef");
            $stream->truncate(3);

            $stream->moveToStart();
            $this->assertSame("abc", $stream->read(10));

            $stream->clear();
            $this->assertSame(0, $stream->getLength());
        } else {
            $this->markTestSkipped("Stream is not writable");
        }
        
        $stream->close();
    }

    public function testMetadata(): void {
        $stream = $this->createStream();
        $meta = $stream->getMetadata();

        $this->assertNotNull($meta);
        $this->assertInstanceOf(Metadata::class, $meta);
        
        $stream->close();
    }

    public function testClose(): void {
        $stream = $this->createStream();
        $stream->close();

        $this->assertSame(0, $stream->getFlags());
        $this->assertNull($stream->getMode());
    }
}

