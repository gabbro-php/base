<?php declare(strict_types=1);
/*
 * This file is part of the Gabbro Project: https://github.com/Gabbro-PHP
 *
 * Copyright (c) 2022 Daniel Bergløv, License: MIT
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

namespace gabbro\utils;

use Throwable;
use ErrorException;
use Closure;

/**
 * Try running some code while catching any errors.
 *
 * This class can be used to catch errors, warning and exceptions
 * while trying to run some code. The main objective here, is to
 * catch internal errors and warnings that is triggered by some of the old-school
 * code in PHP, rather than having it printed to stdout.
 */
class ErrorCatcher {

    /**
     * Halt execution on the first PHP error
     *
     * If this flag is set, the execution will stop on the first php error. 
     * Otherwise it will continue and only suppress the messages.
     *
     * @var int = 0x01
     */
    const ERR_HALT = 0x01;

    /**
     * Throw exception on the first PHP error
     *
     * If this flag is set, it will re-throw any exceptions caught as well as
     * turning any php error into an exception. Otherwise it will simply stop execution
     * on exceptions and return `NULL` from the runner. 
     *
     * @var int = 0x03
     */
    const ERR_THROW = 0x03;
    
    /** 
     * @ignore 
     *
     * @var Throwable|null
     */
    protected Throwable|null $exception = null;

    /** 
     * @ignore 
     *
     * @var int
     */
    protected int $flags;

    /** 
     * @ignore 
     *
     * @var Closure
     */
    protected Closure $handler;
    
    /**
     * Create a new catch instance.
     *
     * @param int-mask<ErrorCatcher::ERR_*> $flags
     *
     * @return void
     */
    public function __construct(int $flags = ErrorCatcher::ERR_HALT) {
        $this->flags = $flags;
        $this->handler = Closure::fromCallable(function($severity, $message, $filename, $lineno){
            $this->exception = new ErrorException($message, 0, $severity, $filename, $lineno);

            if ($severity & (E_USER_ERROR|E_RECOVERABLE_ERROR|E_ERROR) 
                    && $this->flags & ErrorCatcher::ERR_HALT) {
                    
                throw $this->exception;
            }

            return true;

        })->bindTo($this);
    }
    
    /**
     * Get the exception from the last run.
     *
     * @return Throwable|null      Returns `null` if the last run was successful.
     */
    public function getException(): Throwable|null {
        return $this->exception;
    }
    
    /**
     * Try running a closure.
     *
     * This will catch `E_ALL` and turn them into an `ErrorException`. 
     * If `ERR_HALT` is set it will stop execution on `E_USER_ERROR|E_RECOVERABLE_ERROR|E_ERROR`
     * and with `ERR_THROW` it will throw `E_USER_ERROR|E_RECOVERABLE_ERROR|E_ERROR` exceptions.
     *
     * It will also catch any actual exception that are thrown during the code execution. 
     * This will automatically halt the execution. If `ERR_THROW` is set it will re-throw those
     * exceptions. 
     *
     * @template TReturn
     *
     * @param callable():TReturn $closure       The closure that will be executed.
     *
     * @return TReturn|null                     Returns the result of the closure or `NULL` is
     *                                          the execution was halted. 
     */
    public function run(callable $closure): mixed {
        $this->exception = null;

        set_error_handler($this->handler);

        try {
            return $closure();

        } catch (Throwable $e) {
            $this->exception = $e;

        } finally {
            restore_error_handler();
        }

        if ($this->flags & ErrorCatcher::ERR_THROW) {
            throw $this->exception;
        }

        return null;
    }
}

