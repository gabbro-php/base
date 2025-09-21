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
use gabbro\collection\MutableArray;
use gabbro\collection\ImmutableArray;

/**
 * Base tests for MutableArray implementations.
 */
abstract class MutableArrayTestCase extends ImmutableArrayTestCase
{
    /**
     * @return MutableArray<string>
     */
    abstract protected function createMutable(array $values = []): MutableArray;

    protected function createImmutable(array $values = []): ImmutableArray {
        return $this->createMutable($values);
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
        $arr = $this->createMutable(['a', 'b']);
        $count = $arr->remove('a');
        $this->assertSame(1, $count);
        $this->assertSame(['b'], $arr->toArray());
    }

    public function testAddIterable(): void {
        $arr = $this->createMutable();
        $arr->addIterable(['a', 'b', 'c']);
        $this->assertSame(['a', 'b', 'c'], $arr->toArray());
    }
}

