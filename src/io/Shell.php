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
use gabbro\io\Shell\Result;

/**
 * Shell utility class for executing system commands and managing subprocesses.
 *
 * This class provides two modes of operation:
 *
 *  - One-shot execution via {@see Shell::exec()} which runs a command,
 *    collects stdout/stderr, and returns a {@see Result}.
 *
 *  - Interactive execution via {@see Shell::start()}, which spawns a
 *    subprocess and allows direct I/O access to stdin/stdout/stderr streams
 *    through {@see IOStream}.
 *
 * Implementation details:
 * - On Unix-like systems, string commands are passed through `/bin/sh -c`.
 *   A leading `exec` is added to replace the shell process with the target
 *   command, avoiding an extra wrapper process.
 * - Windows is currently not supported. 
 */
class Shell {
    
    /**
     * Get the current console width (number of columns).
     *
     * The method attempts several strategies:
     *  1. Check the COLUMNS environment variable (if set and numeric).
     *  2. Use `stty size` (Linux/macOS) and parse the "rows cols" output.
     *  3. Use `tput cols` (portable Unix command).
     *
     * If none succeed, defaults to 80.
     *
     * @return int          Console width in characters, or -1 if not running in CLI.
     */
    public static function getConsoleWidth(): int {
        if (PHP_SAPI !== "cli") {
            return -1;
        }
        
        $cols = getenv("COLUMNS");
        if ($cols !== false && ctype_digit($cols)) {
            return (int) $cols;
        }
        
        // Try stty (Linux/macOS)
        $res = Shell::exec("stty size");
        
        if ($res->isSuccessful()) {
            [$rows, $cols] = explode(" ", $res->getStream(IOStream::STDOUT)->readLine(0, true));
            
            if (ctype_digit($cols)) {
                return (int) $cols;
            }
        }

        // 3. Try tput (portable Unix way)
        $res = Shell::exec("tput cols");
        
        if ($res->isSuccessful()) {
            $cols = $res->getStream(IOStream::STDOUT)->readLine(0, true);
            
            if (ctype_digit($cols) && ((int) $cols) > 0) {
                return (int) $cols;
            }
        }
        
        return 80;
    }

    /**
     * Execute a command to completion and capture its output.
     *
     * This is a one-shot helper that runs the command, drains stdout and
     * stderr using {@see stream_select()} to avoid deadlocks, and returns
     * a {@see Result} containing exit code, stdout, and stderr data.
     *
     * @note        Output is cached. Do not use this command if
     *              you are expecting large amounts of output. 
     *              For large data output use {@see Shell::start()}.
     *
     * @param string|list<string> $command      Command line string or array of arguments.
     *
     * @return Result                           Execution result object.
     *
     * @throws IOException                      If the process could not be started.
     */
    public static function exec(string|array $command): Result {
        if (is_string($command) && !str_starts_with($command, "exec ")) {
            /*
             * When using a string with 'proc_open', PHP will launch the command
             * through a shell, e.g. `sh -c "$command"`. We will consume this shell
             * and take over the process to avoid running the command as an additional sub-process.
             */
            $command = "exec {$command}";
        }
        
        $descriptorSpec = [
            1 => ["pipe", "w"], // stdout
            2 => ["pipe", "w"], // stderr
        ];
        
        $proc = proc_open($command, $descriptorSpec, $pipes);
        
        if (!is_resource($proc)) {
            throw new IOException("Failed to run command");
        }
        
        $stdout = "";
        $stderr = "";
        $null = null;
        
        while (!empty($pipes)) {
            $read = array_values($pipes);
            $n = stream_select($read, $null, $null, null);
            
            if ($n === false) {
                break; // select failed
            }
            
            foreach ($read as $pipe) {
                $data = fread($pipe, 8192);
                
                if ($data === "" || $data === false) {
                    // EOF - close and remove this pipe
                    $idx = array_search($pipe, $pipes, true);
                    
                    if ($idx !== false) {
                        fclose($pipes[$idx]);
                        unset($pipes[$idx]);
                    }
                    
                } else if (isset($pipes[1]) && $pipe === $pipes[1]) {
                    $stdout .= $data; 
                    
                } else if (isset($pipes[2]) && $pipe === $pipes[2]) {
                    $stderr .= $data;
                    
                } else {
                    throw new IOException("Trying to access non-existing file descriptor");
                }
            }
        }
        
        return new Result(
            proc_close($proc),
            $stdout,
            $stderr
        );
    }

    /**
     * Start a subprocess for interactive use.
     *
     * Unlike {@see exec()}, this method is made for continuous and bi-directional processing or large data output. 
     * The caller can obtain {@see IOStream} handles to interact with stdin, stdout, and
     * stderr using {@see Shell::getStream()}.
     *
     * @param string|list<string> $command          Command line string or array of arguments.
     *
     * @return static                               A new Shell instance wrapping the live process.
     *
     * @throws IOException                          If the process could not be started.
     */
    public static function start(string|array $command): static {
        if (is_string($command) && !str_starts_with($command, "exec ")) {
            $command = "exec {$command}";
        }
        
        $descriptorSpec = [
            0 => ["pipe", "r"],  // stdin
            1 => ["pipe", "w"],  // stdout
            2 => ["pipe", "w"],  // stderr
        ];
        
        $obj = new static();
        $proc = proc_open($command, $descriptorSpec, $obj->pipes);
        
        if (!is_resource($proc)) {
            throw new IOException("Failed to start shell");
        }
        
        $obj->proc = $proc;
        
        return $obj;
    }
    
    /**
     * @var resource[]
     */
    protected array $pipes = [];
    
    /**
     * @var IOStream[]
     */
    protected array $streams = [];
    
    /**
     * @var resource|null
     */
    protected /*resource*/ $proc = null;
    
    /**
     * @ignore
     */
    private final function __construct() {}

    /**
     * @ignore
     */
    public function __destruct() { $this->stop(); }

    /**
     * Terminate the process and close its streams.
     *
     * This will close all open pipes, send a terminate signal to the process,
     * and call {@see proc_close()} to release resources.
     *
     * @return int              Process exit code, or 0 if has already been stopped ones.
     */
    public function stop(): int {
        if ($this->proc !== null) {
            foreach ($this->pipes as $pipe) {
                if (is_resource($pipe)) {
                    fclose($pipe);
                }
            }
            
            proc_terminate($this->proc);
            $ret = proc_close($this->proc);
            $this->proc = null;
            
            return $ret;
        }
        
        return 0;
    }
    
    /**
     * Check whether the process is still running.
     *
     * @return bool         True if the process is active, false otherwise.
     */
    public function isRunning(): bool {
        if ($this->proc === null) {
            return false;
        }
        
        $status = proc_get_status($this->proc);
        return $status["running"];
    }
    
    /**
     * Get a stream wrapper for one of the process file descriptors.
     *
     * @param (0|1|2) $fd               One of IOStream::STDIN, IOStream::STDOUT, IOStream::STDERR.
     *
     * @return IOStream                 Stream wrapper for reading/writing the chosen descriptor.
     *
     * @throws IOException              If the process is no longer active.
     */
    public function getStream(int $fd): IOStream {
        if ($this->proc === null) {
            throw new IOException("Cannot create a stream for a closed shell");
            
        } else if (!isset($this->streams[$fd])) {
            $this->streams[$fd] = new IOStream($this->pipes[$fd], false, $fd);
        }
    
        return $this->streams[$fd];
    }
    
    /**
     * Wait for stdout and stderr to become ready for reading. 
     *
     * This will do a select on all output streams and return whichever 
     * streams are ready to be read. An empty iterable only means that the
     * timeout was reached before any stream became ready while `null` return
     * means that all output streams has reached EOF. 
     *
     * @param float $timeout        Timeout to wait for.
     *
     * @return iterable<IOStream>
     */
    public function wait(float $timeout = 0.1): iterable|null {
        if ($this->proc === null) {
            throw new IOException("Cannot wait on a closed shell");
        }
    
        $read = [];
        
        foreach ($this->pipes as $fd => $pipe) {
            if ($fd === 0) {
                continue;
            
            } else if (!feof($pipe)) {
                $read[] = $pipe;
            }
        }
        
        if (empty($read)) {
            return null;
        }
        
        $ret = [];
        $null = null;
        $sec  = (int) $timeout;
        $usec = (int) (($timeout - $sec) * 1_000_000);
        $n = stream_select($read, $null, $null, $sec, $usec);
        
        if ($n === false) {
            throw new IOException("Failed to select pipe for read");
        }
        
        foreach ($read as $pipe) {
            $ret[] = $this->getStream(
                $pipe === $this->pipes[2] ? 2 : 1
            );
        }

        return $ret;
    }
}

