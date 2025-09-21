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

use Throwable;
use gabbro\utils\ErrorCatcher;
use gabbro\exception\IOException;
use gabbro\io\Stream\Metadata;
use gabbro\io\Stream\Ready;

/**
 * An implementation of Stream that works on PHP resources.
 *
 * This class works as a wrapper around a PHP resource.
 * The resource can be of any type, but it needs to support
 * normal file operations like read and write to work.
 */
class RawStream extends BaseStream {
    
    /**
     * @ignore
     *
     * @var resource|null
     */
    protected /*resource*/ $resource;
    
    /**
     * @ignore
     *
     * @var ErrorCatcher
     */
    protected ErrorCatcher $catcher;

    /**
     * Create a new stream.
     *
     * @param resource $res         A PHP resource.
     *                              If not included, this class will launch a memory/temp hybrid based resource instead 
     *                              using PHP's `php://temp`.
     * 
     * @return void
     */
    public function __construct(/*resource*/ $res = null) {
        if ($res === null) {
            $res = fopen('php://temp', 'r+');
        }
        
        if (!is_resource($res)) {
            throw new IOException("Invalid type. Must be of the type 'resource'");
            
        } else {
            $this->resource = $res;
        }

        $this->catcher = new ErrorCatcher(ErrorCatcher::ERR_HALT|ErrorCatcher::ERR_THROW);
    }
    
    /**
     * {@inheritdoc}
     *
     * @override {@see Stream::getResource()}
     */
    public function getResource() /*resource*/ {
        if ($this->resource === null) {
            throw new IOException("Failed to get resource from closed stream");
        }
    
        return $this->resource;
    }
    
    /**
     * {@inheritdoc}
     *
     * @override {@see Stream::isRemote()}
     */
    public function isRemote(): bool {
        if ($this->resource === null) {
            return false;
        }
    
        return !stream_is_local($this->resource);
    }
    
    /**
     * {inheritdoc}
     *
     * @override {@see Stream::getLength()}
     */
    public function getLength(): int {
        if ($this->resource !== null) {
            $meta = $this->getMetadata();
            $resource = $this->resource;
            $stat = $this->catcher->run(function() use ($meta, $resource): array|false {
                if (!empty($meta->uri)) {
                    clearstatcache(true, $meta->uri);
                }
            
                return fstat($resource);
            });
            
            if (!is_array($stat)) {
                return -1;
            }
            
            return $stat["size"];
        }
        
        return 0;
    }
    
    /**
     * {inheritdoc}
     *
     * @override {@see Stream::getMetadata()}
     */
    public function getMetadata(): Metadata {
        if ($this->resource === null) {
            return parent::getMetadata();
        }
        
        $resource = $this->resource;
        /** 
         * @var array{
         *   timed_out?: bool,
         *   blocked?: bool,
         *   eof?: bool,
         *   unread_bytes?: int,
         *   stream_type: string,
         *   wrapper_type?: string,
         *   wrapper_data?: mixed,
         *   mode: string,
         *   seekable?: bool,
         *   uri?: string,
         *   crypto?: array<string,mixed>
         * } $meta
         */
        $meta = $this->catcher->run(function() use ($resource): array {
            return stream_get_meta_data($resource);
        });
        
        /* Depending on the wrapper, booleans can be both integer 0 and 1 as well as regular bool
         * Also PHP's documentation is wrong. They promise most of these keys, but hardly any can be trusted.
         */
        return new Metadata(
            timedOut:       ($meta["timed_out"] ?? false) ? true : false,
            blocked:        ($meta["blocked"] ?? false) ? true : false,
            eof:            ($meta["eof"] ?? false) ? true : false,
            unreadBytes:    $meta["unread_bytes"] ?? 0,
            streamType:     $meta["stream_type"],
            wrapperType:    $meta["wrapper_type"] ?? "",
            wrapperData:    $meta["wrapper_data"] ?? null,
            mode:           $meta["mode"],
            seekable:       ($meta["seekable"] ?? false) ? true : false,
            uri:            $meta["uri"] ?? "",
            crypto:         $meta["crypto"] ?? null
        );
    }
    
    /**
     * {inheritdoc}
     *
     * @override {@see Stream::getPosition()}
     */
    public function getPosition(): int {
        if ($this->resource !== null) {
            $resource = $this->resource;
            $pos = $this->catcher->run(function() use ($resource): int|false {
                return ftell($resource);
            });
            
            if (!is_int($pos)) {
                return -1;
            }
            
            return $pos;
        }
        
        return 0;
    }
    
    /**
     * {inheritdoc}
     *
     * @override {@see Stream::moveTo()}
     */
    public function moveTo(int $offset, int $whence = Stream::MOVE_SET): bool {
        if ($this->resource !== null && $this->getFlags(Stream::MODE_MOVABLE)) {
            $resource = $this->resource;
            $seek = $this->catcher->run(function() use ($resource, $offset, $whence): int {
                return fseek($resource, $offset, $whence);
            });
            
            if (!is_int($seek) || $seek < 0) {
                return false;
            }
            
            return true;
        }
        
        return false;
    }
    
    /**
     * {inheritdoc}
     *
     * @override {@see Stream::truncate()}
     */
    public function truncate(int $size): bool {
        $flags = Stream::MODE_WRITABLE | Stream::MODE_MOVABLE;
    
        if ($this->resource !== null && $this->getFlags($flags) == $flags) {
            $resource = $this->resource;
            $res = $this->catcher->run(function() use ($resource, $size): bool {
                return ftruncate($resource, $size)
                        && fseek($resource, 0, SEEK_END) == 0;
            });
            
            return is_bool($res) && $res;
        }
        
        return false;
    }
    
    /**
     * {inheritdoc}
     *
     * @override {@see Closeable::close()}
     */
    public function close(): void {
        if ($this->resource !== null) {
            $resource = $this->resource;

            try {
                $this->catcher->run(function() use ($resource): void {
                    if (!fclose($resource)) {
                        // Pipe Resource
                        pclose($resource);
                    }
                });

            } catch (Throwable $e) {}
            
            $this->resource = null;
        }
    }
    
    /**
     * {inheritdoc}
     *
     * @override {@see Stream::write()}
     */
    public function write(string|Stream $data): int {
        if ($this->resource !== null && $this->getFlags(Stream::MODE_WRITABLE)) {
            if (is_string($data)) {
                $resource = $this->resource;
                $count = $this->catcher->run(function() use ($data, $resource): int|false {
                    return fwrite($resource, $data);
                });
                
                if (!is_int($count)) {
                    throw new IOException("Failed while trying to write to stream");
                }
                
                return $count;
            }
            
            return parent::write($data);
        }
        
        throw new IOException("Trying to write to a closed or read-only stream");
    }
    
    /**
     * {inheritdoc}
     *
     * This implementation uses PHP resources. Depending on the resource type, 
     * they can have various behaviours to be aware of. 
     *
     *   1. Plain files (plainfile)  
     *       - fread() returns up to $len bytes or '' at EOF.
     *       - feof() flips to true when EOF is detected.
     *
     *   2. php://memory, php://temp  
     *       - Behaves like plainfile.
     *
     *   3. STDIN / pipes (php://stdin, process pipes)
     *       - fread() blocks until data arrives, or EOF when the writer closes the pipe.
     *       - feof() becomes true only after the pipe closes.
     *
     *   4. Network sockets (tcp_socket, udp_socket)
     *       - fread() returns as soon as any packet data is available.
     *       - feof() becomes true only when the peer closes the connection (FIN/RST).  
     *         If it’s a protocol where the server keeps the connection open (HTTP/1.1 keep-alive, long-poll, WebSockets), you’ll never see EOF.
     *
     *   5. HTTP/FTP wrappers
     *       - If Content-Length is known → PHP stops after that many bytes, then sets EOF.
     *       - If chunked encoding → PHP parses chunks, stops when stream ends, EOF true.
     *       - If neither (misbehaving server) → connection may stay open, feof never fires.
     *
     * This method will never force a read to obtain the requested length of bytes. 
     * The request length being passed is seen as a max length and not a must have length.
     * As such, depending on the resource type, `$length != return` does not equal `EOF`.
     * If the bytes returned is less than requested, it could just mean that we emptied the PHP buffer and
     * that more data may be on it's way. 
     *
     * Know your protocol. Use `Stream::isEOF()`, but be aware of the behaviours described above.
     *
     * @override {@see Stream::read()}
     */
    public function read(int $length, bool $allowBlocking = true): string {
        if ($this->resource !== null && $this->getFlags(Stream::MODE_READABLE)) {
            $resource = $this->resource;
            $isBlocked = true;
            
            if (!$allowBlocking) {
                $meta = $this->getMetadata();
                
                if (($isBlocked = $meta->blocked) && !stream_set_blocking($resource, false)) {
                    // Fallback to simply reading the buffered data
                    $length = $meta->unreadBytes;
                    
                    // No need to try changing this back after the read
                    $isBlocked = false;
                    
                    // No buffered data, do not try to read as it will block
                    if ($length <= 1) {
                        return "";
                    }
                }
            }
            
            $bytes = $this->catcher->run(function() use ($length, $resource): string|false {
                return fread($resource, $length);
            });
            
            if (!$allowBlocking && $isBlocked) {
                stream_set_blocking($resource, true);
            }
            
            if (!is_string($bytes)) {
                throw new IOException("Failed while reading from stream");
            }
            
            return $bytes;
        }
        
        throw new IOException("Trying to read from a closed or write-only stream");
    }
    
    /**
     * {inheritdoc}
     *
     * @override {@see Stream::readLine()}
     */
    public function readLine(int $maxlen = 0, bool $trim = false): string {
        if ($this->resource !== null && $this->getFlags(Stream::MODE_READABLE)) {
            $resource = $this->resource;
            $bytes = $this->catcher->run(function() use ($maxlen, $resource): string|false {
                return fgets($resource, $maxlen > 0 ? $maxlen : null);
            });
            
            if ($bytes === null) {
                throw new IOException("Failed while reading from stream");
                
            } else if ($trim && is_string($bytes)) {
                $bytes = rtrim($bytes, "\r\n");
            }
            
            return $bytes ?: "";
        }
        
        throw new IOException("Trying to read from a closed or write-only stream");
    }
    
    /**
     * {inheritdoc}
     *
     * @override {@see Stream::readAll()}
     */
    public function readAll(): string {
        if ($this->resource !== null && $this->getFlags(Stream::MODE_READABLE)) {
            $resource = $this->resource;
            $bytes = $this->catcher->run(function() use ($resource): string|false {
                return stream_get_contents($resource);
            });
            
            if ($bytes === null) {
                throw new IOException("Failed while reading from stream");
            }
            
            return $bytes ?: "";
        }
        
        throw new IOException("Trying to read from a closed or write-only stream");
    }
    
    /**
     * {inheritdoc}
     *
     * @override {@see Stream::wait()}
     */
    public function wait(float $timeout = 0.1): Ready {
        if ($this->resource !== null) {
            $sec  = (int) $timeout;
            $usec = (int) (($timeout - $sec) * 1_000_000);
            
            $read   = null;
            $write  = null;
            $except = null;
            $flags = $this->getFlags();

            if ($flags & Stream::MODE_READABLE) {
                $read = [$this->resource];
            }

            if ($flags & Stream::MODE_WRITABLE) {
                $write = [$this->resource];
            }
            
            if ($this->isRemote()) {
                $except = [$this->resource];
            }
            
            $n = $this->catcher->run(function() use (&$read, &$write, &$except, $sec, $usec): int|false {
                return stream_select($read, $write, $except, $sec, $usec);
            });

            $n = stream_select($read, $write, $except, $sec, $usec);
            if (!is_int($n)) {
                throw new IOException("Failed while waiting on stream to become ready");
            }

            $flags = 0;
            if (!empty($read))   { $flags |= Ready::READY_READ; }
            if (!empty($write))  { $flags |= Ready::READY_WRITE; }
            if (!empty($except)) { $flags |= Ready::READY_EXCEPT; }

            return new Ready($this, $flags);
        }
        
        throw new IOException("Trying to wait on a closed stream");
    }
    
    /**
     * {inheritdoc}
     *
     * @override {@see Stream::isEOF()}
     */
    public function isEOF(): bool {
        if ($this->resource !== null) {
            $resource = $this->resource;
            $eof = $this->catcher->run(function() use ($resource): bool {
                return feof($resource);
            });

            return $eof !== false;
        }

        return true;
    }
}

