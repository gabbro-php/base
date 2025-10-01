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
use gabbro\exception\InvalidInputException;
use gabbro\io\Stream\Metadata;

/**
 * An extended stream specifically for I/O
 */
class IOStream extends RawStream {

    /**
     * Defines the stdin fd.
     *
     * @var 0
     */
    public const STDIN = 0;
    
    /**
     * Defines the stdout fd.
     *
     * @var 1
     */
    public const STDOUT = 1;
    
    /**
     * Defines the stderr fd.
     *
     * @var 2
     */
    public const STDERR = 2;

    /**
     * @ignore
     * @var IOStream|null
     */
    protected static IOStream|null $stderr;
    
    /**
     * @ignore
     * @var IOStream|null
     */
    protected static IOStream|null $stdin;
    
    /**
     * @ignore
     * @var IOStream|null
     */
    protected static IOStream|null $stdout;
    
    /**
     * @ignore
     * @var bool
     */
    protected bool $allowClose = true;
    
    /**
     * @ignore
     * @var int
     */
    protected int $id = -1;
    
    /**
     * Get the instance of a CLI Stream.
     *
     * @param (0|1|2) $fd        The file descriptor do return.
     *
     * @return IOStream
     */
    public static function getInstance(int $fd = IOStream::STDOUT): IOStream {
        return match ($fd) {
            IOStream::STDIN  => IOStream::$stdin  ??= new IOStream(
                defined("STDIN") ? STDIN : fopen("php://input", "r"),
                false,
                IOStream::STDIN
            ),
            
            IOStream::STDOUT => IOStream::$stdout ??= new IOStream(
                defined("STDOUT") ? STDOUT : fopen("php://output", "w"),
                false,
                IOStream::STDOUT
            ),
            
            IOStream::STDERR => IOStream::$stderr ??= new IOStream(
                defined("STDERR") ? STDERR : fopen("php://stderr", "w"),
                false,
                IOStream::STDERR
            )
        };
    }

    /**
     * Create a new instance of this class.
     *
     * @param resource $res         A PHP Resource.
     * @param bool $allowClose      Whether or not allow this stream to be closed.
     *
     * @return void
     */
    public function __construct(/*resource*/ $res, bool $allowClose = true, int $id = -1) {
        parent::__construct($res);
        
        $this->allowClose = $allowClose;
        $this->id = $id === -1 ? get_resource_id($res) : $id;
    }
    
    /**
     * Get the stream id.
     *
     * This is by default based on PHP's `get_resource_id()`. 
     * However it is possible to set a custom number during creation. 
     *
     * The default `stdin`, `stdout` and `stderr` will always use `0`, `1` and `2`. 
     * Just note that this id is not unique. Other implementaions may reuse 
     * the default I/O FD's. 
     *
     * @return int          Returns -1 when the stream is closed. 
     */
    public function getId(): int {
        return $this->id;
    }
    
    /**
     * Check to see if a stream is interactive.
     *
     * This will check to see if this stream is attached directly
     * to a TTY/Shell (User interaction). If the stream is piped or attached
     * to something like a web server, closed etc., this will return `false`.
     *
     * @return bool
     */
    public function isInteractive(): bool {
        if ($this->resource === null || PHP_SAPI !== "cli") {
            return false;
        }
        
        return stream_isatty($this->resource);
    }
    
    /**
     * Check to see if this stream has color support. 
     *
     * Color support is only available on interactive output streams
     * and only if the underlying shell supports it.
     *
     * @return bool
     */
    public function hasColorSupport(): bool {
        if (!$this->isInteractive() || $this->getFlags(Stream::MODE_WRITABLE) == 0) {
            return false;
        }

        // Windows support
        if (DIRECTORY_SEPARATOR === "\\") {
            $resource = $this->resource;
            
            // Modern Windows 10+ terminals support ANSI by default
            // For older ones, you'd need vt100 emulation check, but most PHP 8+ installs are fine
            if ($resource !== null && function_exists("sapi_windows_vt100_support")) {
                $bytes = $this->catcher->run(function() use ($resource): bool {
                    return sapi_windows_vt100_support($resource, true);
                });
                
                return $bytes === true;
            }
            
            return false;
        }

        // On Unix-like systems, check TERM
        $term = getenv("TERM") ?: "";
        
        if ($term === "" || $term === "dumb") {
            return false;
        }

        return true;
    }
    
    /**
     * Print a line to this stream. 
     *
     * This will automatically add a line ending at the end
     * of the string. You can also use {@see https://www.php.net/manual/function.printf.php} styling and pass
     * arguments for it.
     *
     * @param string $line                          String with optional `printf` formatting.
     * @param bool|float|int|string|null ...$args   Values for `printf` formatting.
     *
     * @return int                                  The number of bytes that was written, including the line ending.
     */
    public function println(string $line, mixed ...$args): int {
        return $this->write(
            sprintf($line . "\n", ...$args)
        );
    }
    
    /**
     * {inheritdoc}
     *
     * @override {@see Closeable::close()}
     */
    public function close(): void {
        if ($this->allowClose) {
            parent::close();
            $this->id = -1;
        }
    }
}

