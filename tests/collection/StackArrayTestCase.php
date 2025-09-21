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
namespace gabbro\test\collection;

use PHPUnit\Framework\TestCase;
use gabbro\collection\StackArray;
use gabbro\exception\DatasetException;

abstract class StackArrayTestCase extends TestCase {
    /**
     * @return StackArray<string>
     */
    abstract protected function createStack(): StackArray;

    public function testPushAndPop(): void {
        $stack = $this->createStack();
        $stack->push('a');
        $stack->push('b');
        $val = $stack->pop();

        $this->assertContains($val, ['a','b']); // check proper push/pop
    }

    public function testPeak(): void {
        $stack = $this->createStack();
        $stack->push('x');
        $this->assertSame('x', $stack->peak());
    }

    public function testPopOr(): void {
        $stack = $this->createStack();
        $this->assertSame('default', $stack->popOr('default'));
    }

    public function testClear(): void {
        $stack = $this->createStack();
        $stack->push('a');
        $stack->clear();
        $this->assertSame([], $stack->toArray());
    }

    public function testAddIterable(): void {
        $stack = $this->createStack();
        $stack->addIterable(['a','b','c']);
        $this->assertSame(3, $stack->length());
    }

    public function testTraverse(): void {
        $stack = $this->createStack();
        $stack->addIterable(['a','b','c']);
        $seen = [];
        $stack->traverse(function ($v) use (&$seen) {
            $seen[] = $v;
            return false;
        });
        $this->assertSame(3, count($seen));
    }

    public function testPopThrowsOnEmpty(): void {
        $stack = $this->createStack();
        $this->expectException(DatasetException::class);
        $stack->pop();
    }
}

