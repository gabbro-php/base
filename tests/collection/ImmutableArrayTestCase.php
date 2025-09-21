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
use gabbro\collection\ImmutableArray;

/**
 * Base tests for ImmutableArray implementations.
 *
 * @template T
 */
abstract class ImmutableArrayTestCase extends TestCase
{
    /**
     * @return ImmutableArray<string>
     */
    abstract protected function createImmutable(array $values = []): ImmutableArray;

    public function testToArrayAndLength(): void {
        $arr = $this->createImmutable(['a', 'b']);
        $this->assertSame(['a', 'b'], $arr->toArray());
        $this->assertSame(2, $arr->length());
    }

    public function testJoin(): void {
        $arr = $this->createImmutable(['a', 'b', 'c']);
        $this->assertSame('a-b-c', $arr->join('-'));
        $this->assertSame('abc', $arr->join());
    }

    public function testContains(): void {
        $arr = $this->createImmutable(['a', 'b']);
        $this->assertTrue($arr->contains('a'));
        $this->assertFalse($arr->contains('c'));
    }

    public function testFilter(): void {
        $arr = $this->createImmutable(['a', 'b', 'c']);
        $filtered = $arr->filter(fn($v) => $v !== 'b');
        $this->assertSame(['a', 'c'], $filtered->toArray());
    }

    public function testTraverse(): void {
        $arr = $this->createImmutable(['a', 'b', 'c']);
        $called = [];
        $arr->traverse(function (&$v) use (&$called) {
            $called[] = $v;
            return false; // keep going
        });
        $this->assertSame(['a', 'b', 'c'], $called);
    }

    public function testTraverseStopsEarly(): void {
        $arr = $this->createImmutable(['a', 'b', 'c']);
        $called = [];
        $arr->traverse(function (&$v) use (&$called) {
            $called[] = $v;
            return $v === 'b';
        });
        $this->assertSame(['a', 'b'], $called);
    }

    public function testFind(): void {
        $arr = $this->createImmutable(['a', 'b', 'c']);
        $this->assertSame('b', $arr->find(fn($v) => $v === 'b'));
        $this->assertNull($arr->find(fn($v) => $v === 'z'));
    }
}

