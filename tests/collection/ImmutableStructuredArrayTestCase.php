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
use gabbro\exception\DatasetException;

/**
 * Base tests for ImmutableStructuredArray implementations.
 *
 * @template T
 */
abstract class ImmutableStructuredArrayTestCase extends ImmutableArrayTestCase
{
    /**
     * @return ImmutableStructuredArray<string>
     */
    abstract protected function createImmutable(array $values = []): ImmutableStructuredArray;

    public function testIndexOf(): void {
        $arr = $this->createImmutable(['a', 'b', 'c', 'b']);
        $this->assertSame(1, $arr->indexOf('b'));
        $this->assertSame(3, $arr->indexOf('b', 2));
        $this->assertSame(-1, $arr->indexOf('z'));
    }

    public function testGet(): void {
        $arr = $this->createImmutable(['a', 'b', 'c']);
        $this->assertSame('a', $arr->get(0));
        $this->assertSame('c', $arr->get(-1));
        $this->expectException(DatasetException::class);
        $arr->get(10);
    }

    public function testGetOr(): void {
        $arr = $this->createImmutable(['a', 'b']);
        $this->assertSame('a', $arr->getOr(0));
        $this->assertNull($arr->getOr(10));
        $this->assertSame('fallback', $arr->getOr(10, 'fallback'));
    }

    public function testSort(): void {
        $arr = $this->createImmutable(['c', 'a', 'b']);
        $arr->sort(fn($a, $b) => $a <=> $b);
        $this->assertSame(['a', 'b', 'c'], $arr->toArray());
    }
}

