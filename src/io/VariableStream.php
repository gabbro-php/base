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
 * A Stream implementation that works on variables. 
 *
 * This stream takes a variable as reference and let's you
 * make normal reads and writes, just as if it was a file stream. 
 * This can sometimes be useful on large variables.
 *
 * @note    This class has it's own length and pointer properties that it keeps track of. 
 *          Any attempt to alter the variable from outside this class while it's
 *          referenced in it, will break things things. 
 */
class VariableStream extends BaseStream {

    /**
     * @ignore
     * @var string|null $buffer
     */
    protected string|null $buffer;
    
    /**
     * @ignore
     * @var int<0,max> $buffer
     */
    protected int $pointer = 0;
    
    /**
     * @ignore
     * @var int<0,max> $buffer
     */
    protected int $length = 0;

    /**
     * Create a new stream.
     *
     * @param string &$buffer      A data variable.
     * 
     * @return void
     */
    public function __construct(string &$buffer) {    
        $this->buffer = &$buffer;
        $this->length = strlen($this->buffer);
    }

    /**
     * {inheritdoc}
     *
     * @override {@see Stream::getLength()}
     */
    public function getLength(): int {
        if ($this->buffer === null) {
            return 0;
        }
        
        return strlen($this->buffer);
    }
    
    /**
     * {inheritdoc}
     *
     * @override {@see Stream::getMetadata()}
     */
    public function getMetadata(): Metadata {
        if ($this->buffer === null) {
            return parent::getMetadata();
        }
        
        return new Metadata(
            timedOut:       false,
            blocked:        false,
            eof:            $this->pointer >= $this->length,
            unreadBytes:    $this->length - $this->pointer,
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
        return $this->pointer;
    }
    
    /**
     * {inheritdoc}
     *
     * @override {@see Stream::moveTo()}
     */
    public function moveTo(int $offset, int $whence = Stream::MOVE_SET): bool {
        if ($whence == Stream::MOVE_CUR) {
            $offset += $this->pointer;

        } else if ($whence == Stream::MOVE_END) {
            $offset += $this->length;
        }

        if ($offset < 0 || $offset > $this->length) {
            return false;
        }

        $this->pointer = $offset;

        return true;
    }
    
    /**
     * {inheritdoc}
     *
     * @override {@see Stream::truncate()}
     */
    public function truncate(int $size): bool {
        if ($this->buffer !== null) {
            if ($size > $this->length) {
                $this->buffer .= str_repeat("\0", $this->length - $size);

            } else {
                $this->buffer = substr($this->buffer, 0, $size);
            }

            $this->length = $size;
            $this->pointer = strlen($this->buffer);

            return true;
        }
        
        return false;
    }
    
    /**
     * {inheritdoc}
     *
     * @override {@see Closeable::close()}
     */
    public function close(): void {
        if ($this->buffer !== null) {
            $null = null;

            // Detach from the shared buffer by assigning a new pointer.
            $this->buffer = &$null;
            $this->length = 0;
        }
    }
    
    /**
     * {inheritdoc}
     *
     * @override {@see Stream::write()}
     */
    public function write(string|Stream $data): int {
        if ($this->buffer !== null) {
            if (is_string($data)) {
                $len = strlen($data);
                
                $this->buffer = substr_replace(
                    $this->buffer,
                    $data,
                    $this->pointer,
                    $len
                );
                
                $this->pointer += $len;
                
                if ($this->pointer > $this->length) {
                    $this->length = $this->pointer;
                }
                
                return $len;
            }
            
            return parent::write($data);
        }
        
        throw new IOException("Trying to write to a closed stream");
    }
    
    /**
     * {inheritdoc}
     *
     * @override {@see Stream::read()}
     */
    public function read(int $length, bool $allowBlocking = true): string {
        if ($this->buffer !== null) {
            if ($this->pointer >= $this->length) {
                return ""; // EOF
            }

            $chunk = substr($this->buffer, $this->pointer, $length);
            $this->pointer += strlen($chunk); // advance pointer by what we actually got
            
            return $chunk;
        }
        
        throw new IOException("Trying to read from a closed stream");
    }
    
    /**
     * {inheritdoc}
     *
     * @override {@see Stream::readLine()}
     */
    public function readLine(int $maxlen = 0, bool $trim = false): string {
        if ($this->buffer === null) {
            throw new IOException("Trying to read from a closed stream");
        
        } else if ($this->pointer >= $this->length) {
            return ""; // EOF
        }

        // Look for the earliest occurrence of any newline sequence
        $posLF  = strpos($this->buffer, "\n", $this->pointer);
        $posCR  = strpos($this->buffer, "\r", $this->pointer);
        
        // Pick the closest line break (if both exist)
        $pos = null;
        
        if ($posLF !== false && $posCR !== false) {
            $pos = min($posLF, $posCR);
            
        } elseif ($posLF !== false) {
            $pos = $posLF;
            
        } elseif ($posCR !== false) {
            $pos = $posCR;
        }
        
        if ($pos === null) {
            // No line ending 
            $chunk = substr($this->buffer, $this->pointer);
            $this->pointer = $this->length;
            
            return $chunk;
        }
        
        $chars = 1;
        
        // Special case: CRLF (\r\n)
        if ($this->buffer[$pos] === "\r" && isset($this->buffer[$pos + 1]) && $this->buffer[$pos + 1] === "\n") {
            $chars = 2;
        }
        
        $chunk = substr($this->buffer, $this->pointer, $pos - $this->pointer + ($trim ? 0 : $chars));
        $this->pointer = $pos + $chars;
        
        return $chunk;
    }
}

