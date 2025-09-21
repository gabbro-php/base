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
namespace gabbro\test\utils;

use gabbro\utils\Version;
use gabbro\exception\InvalidInputException;
use PHPUnit\Framework\TestCase;

final class VersionTest extends TestCase {

    public function testParsesBasicVersions(): void {
        $v = new Version("1.2.3");
        $this->assertSame(1, $v->major);
        $this->assertSame(2, $v->minor);
        $this->assertSame(3, $v->patch);
        $this->assertNull($v->release);
        $this->assertNull($v->build);
        $this->assertNull($v->meta);
        $this->assertSame("1.2.3", $v->version);
    }

    public function testParsesWithReleaseAndBuild(): void {
        $v = new Version("2.0.0-beta.1");
        $this->assertSame(2, $v->major);
        $this->assertSame(0, $v->minor);
        $this->assertSame(0, $v->patch);
        $this->assertSame("beta", $v->release);
        $this->assertSame(1, $v->build);
        $this->assertSame("2.0.0-beta.1", $v->version);
    }

    public function testParsesWithMetadata(): void {
        $v = new Version("1.2.3+build2025");
        $this->assertSame(1, $v->major);
        $this->assertSame(2, $v->minor);
        $this->assertSame(3, $v->patch);
        $this->assertSame("build2025", $v->meta);
        $this->assertSame("1.2.3", $v->version); // metadata is ignored in normalized
    }

    public function testInvalidVersionThrows(): void {
        $this->expectException(InvalidInputException::class);
        new Version("banana");
    }

    /**
     * @dataProvider matchesProvider
     */
    public function testMatches(string $base, string $target, string $operator, bool $expected): void {
        $v = new Version($base);
        $this->assertSame(
            $expected,
            $v->matches($target, $operator),
            "$base $operator $target"
        );
    }

    public static function matchesProvider(): array {
        return [
            // Equal
            ["1.2.3", "1.2.3", "=", true],
            ["1.2.3", "1.2.4", "=", false],

            // Greater / less
            ["1.2.3", "1.2.2", ">", false],
            ["1.2.3", "1.2.4", ">", true],
            ["1.2.3", "2.0.0", ">", true],

            // Greater or equal / less or equal
            ["1.2.3", "1.2.3", ">=", true],
            ["1.2.3", "1.2.3", "<=", true],
            ["1.2.3", "1.2.2", "<=", true],

            // With prerelease
            ["1.0.0-beta", "1.0.0-beta", "=", true],
            ["1.0.0-beta", "1.0.0", "=", false],
            ["1.0.0-beta", "1.0.0", ">", true],

            // With build number
            ["1.0.0-beta.1", "1.0.0-beta.1", "=", true],
            ["1.0.0-beta.1", "1.0.0-beta.2", ">", true],

            // Metadata does not affect normalization
            ["1.2.3+meta", "1.2.3", "=", true],
        ];
    }
}
