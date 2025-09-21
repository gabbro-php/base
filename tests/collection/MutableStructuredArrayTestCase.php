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

use gabbro\collection\ImmutableStructuredArray;
use gabbro\collection\MutableStructuredArray;
use gabbro\exception\DatasetException;

/**
 * Base tests for MutableStructuredArray implementations.
 */
abstract class MutableStructuredArrayTestCase extends ImmutableStructuredArrayTestCase
{
    /**
     * @return MutableStructuredArray<string>
     */
    abstract protected function createMutable(array $values = []): MutableStructuredArray;

    protected function createImmutable(array $values = []): ImmutableStructuredArray {
        return $this->createMutable($values);
    }

    public function testSet(): void {
        $arr = $this->createMutable(['a', 'b']);
        $arr->set(1, 'z');
        $this->assertSame(['a', 'z'], $arr->toArray());

        $arr->set(-1, 'y');
        $this->assertSame(['a', 'y'], $arr->toArray());
    }

    public function testUnset(): void {
        $arr = $this->createMutable(['a', 'b', 'c']);
        $removed = $arr->unset(1);
        $this->assertSame('b', $removed);
        $this->assertSame(['a', 'c'], $arr->toArray());

        $removed = $arr->unset(-1);
        $this->assertSame('c', $removed);
        $this->assertSame(['a'], $arr->toArray());
    }

    public function testInsert(): void {
        $arr = $this->createMutable(['a', 'c']);
        $arr->insert(1, 'b');
        $this->assertSame(['a', 'b', 'c'], $arr->toArray());

        $arr->insert(-1, 'x');
        $this->assertSame(['a', 'b', 'x', 'c'], $arr->toArray());
    }

    public function testInvalidPositionsThrow(): void {
        $arr = $this->createMutable(['a']);
        $this->expectException(DatasetException::class);
        $arr->set(5, 'z');
    }
    
    public function testAddAndClear(): void {
        $arr = $this->createMutable();
        $arr->add('a');
        $arr->add('b');
        $this->assertTrue($arr->contains('a'));
        $arr->clear();
        $this->assertSame([], $arr->toArray());
    }

    public function testRemove(): void {
        $arr = $this->createMutable(['a', 'b', 'a']);
        $count = $arr->remove('a');
        $this->assertSame(2, $count);
        $this->assertSame(['b'], $arr->toArray());
    }

    public function testAddIterable(): void {
        $arr = $this->createMutable();
        $arr->addIterable(['a', 'b', 'c']);
        $this->assertSame(['a', 'b', 'c'], $arr->toArray());
    }
}

