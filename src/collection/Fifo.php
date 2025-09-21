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

namespace gabbro\collection;

use gabbro\exception\DatasetException;
use Throwable;

/**
 * An implementation of a first-in-first-out (Queue).
 *
 * @template T
 * @extends StackArray<T>
 */
class Fifo extends StackArray {

    /**
     * Create a new Queue.
     *
     * @param iterable<T>|null $itt
     *
     * @return void
     */
    public function __construct(iterable|null $itt = null) {
        parent::__construct();
        
        if ($itt !== null) {
            foreach ($itt as $value) {
                $this->dataset->push($value);
            }
        }
    }

    /**
     * {inheritdoc}
     * 
     * @override {@see StackArray::push()}
     */
    function push(mixed $value): void {
        $this->dataset->push($value);
    }

    /**
     * {inheritdoc}
     * 
     * @override {@see StackArray::pop()}
     */
    function pop(): mixed {
        try {
            return $this->dataset->shift();

        } catch (Throwable $e) {
            throw new DatasetException($e->getMessage(), $e->getCode(), $e);
        }
    }

    /**
     * {inheritdoc}
     * 
     * @override {@see StackArray::peak()}
     */
    function peak(): mixed {
        try {
            return $this->dataset->bottom();

        } catch (Throwable $e) {
            return null;
        }
    }
}

