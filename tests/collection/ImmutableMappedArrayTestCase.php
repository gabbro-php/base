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
use gabbro\collection\ImmutableMappedArray;
use gabbro\exception\DatasetException;

abstract class ImmutableMappedArrayTestCase extends TestCase {
    /**
     * @return ImmutableMappedArray<string,string>
     */
    abstract protected function createImmutable(array $values = []): ImmutableMappedArray;

    public function testToArrayAndLength(): void {
        $map = $this->createImmutable(['a' => 'x', 'b' => 'y']);
        $this->assertSame(['a' => 'x', 'b' => 'y'], $map->toArray());
        $this->assertSame(2, $map->length());
    }

    public function testGetAndGetOr(): void {
        $map = $this->createImmutable(['a' => 'x']);
        $this->assertSame('x', $map->get('a'));

        $this->expectException(DatasetException::class);
        $map->get('missing');
    }

    public function testGetOrDefault(): void {
        $map = $this->createImmutable(['a' => 'x']);
        $this->assertNull($map->getOr('missing'));
        $this->assertSame('fallback', $map->getOr('missing', 'fallback'));
    }

    public function testGetKey(): void {
        $map = $this->createImmutable(['a' => 'x', 'b' => 'y']);
        $this->assertSame('a', $map->getKey('x'));
        $this->assertNull($map->getKey('z'));
    }

    public function testIsSet(): void {
        $map = $this->createImmutable(['a' => 'x']);
        $this->assertTrue($map->isSet('a'));
        $this->assertFalse($map->isSet('z'));
    }

    public function testGetKeysAndValues(): void {
        $map = $this->createImmutable(['a' => 'x', 'b' => 'y']);
        $this->assertSame(['a', 'b'], $map->getKeys()->toArray());
        $this->assertSame(['x', 'y'], $map->getValues()->toArray());
    }

    public function testTraverse(): void {
        $map = $this->createImmutable(['a' => 'x', 'b' => 'y']);
        $seen = [];
        $map->traverse(function ($k, &$v) use (&$seen) {
            $seen[$k] = $v;
            return false; // keep going
        });
        $this->assertSame(['a' => 'x', 'b' => 'y'], $seen);
    }

    public function testTraverseStopsEarly(): void {
        $map = $this->createImmutable(['a' => 'x', 'b' => 'y']);
        $seen = [];
        $map->traverse(function ($k, &$v) use (&$seen) {
            $seen[$k] = $v;
            return $k === 'a'; // stop after first
        });
        $this->assertSame(['a' => 'x'], $seen);
    }
}

