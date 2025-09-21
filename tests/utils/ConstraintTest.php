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

use gabbro\utils\Version\Constraint;
use gabbro\exception\InvalidInputException;
use PHPUnit\Framework\TestCase;

final class ConstraintTest extends TestCase {

    /**
     * @dataProvider operatorProvider
     */
    public function testMatches(string $version, string $constraint, bool $expected) {
        $c = new Constraint($constraint);
        $this->assertSame($expected, $c->matches($version), "$version $constraint");
    }

    public static function operatorProvider(): array {
        return [
            // Simple equality
            ["1.2.3", "1.2.3", true],
            ["1.2.3", "=1.2.4", false],
            ["1.2.3", "!=1.2.4", true],
            ["1.2.3", "!=1.2.3", false],

            // Greater / less
            ["1.2.3", ">1.2.2", true],
            ["1.2.3", ">1.2.3", false],
            ["1.2.3", ">=1.2.3", true],
            ["1.2.3", "<1.2.4", true],
            ["1.2.3", "<1.2.3", false],
            ["1.2.3", "<=1.2.3", true],

            // Tilde ranges
            ["1.2.3", "~1.2.3", true],   // >=1.2.3 <1.3.0
            ["1.2.9", "~1.2.3", true],   // still <1.3.0
            ["1.3.0", "~1.2.3", false],
            ["2.0.0", "~1.2.3", false],
            ["1.4.5", "~1", true],      // >=1.0.0 <2.0.0
            ["2.0.0", "~1", false],

            // Caret ranges
            ["1.2.3", "^1.2.3", true],   // >=1.2.3 <2.0.0
            ["1.9.9", "^1.2.3", true],
            ["2.0.0", "^1.2.3", false],
            ["0.2.5", "^0.2.3", true],   // >=0.2.3 <0.3.0
            ["0.3.0", "^0.2.3", false],
            ["0.0.4", "^0.0.3", false],  // >=0.0.3 <0.0.4
            ["0.0.3", "^0.0.3", true],

            // Wildcards
            ["1.2.3", "1.*", true],      // >=1.0.0 <2.0.0
            ["2.0.0", "1.*", false],
            ["1.5.0", "1.5.*", true],    // >=1.5.0 <1.6.0
            ["1.6.0", "1.5.*", false],
            ["0.0.5", "*", true],        // all versions
        ];
    }

    public function testInvalidConstraintThrows() {
        $this->expectException(InvalidInputException::class);
        new Constraint("invalid^pattern");
    }
}

