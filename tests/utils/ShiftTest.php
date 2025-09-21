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

use PHPUnit\Framework\TestCase;
use gabbro\utils\Shift;

final class ShiftTest extends TestCase {
    /* ---------------- toString ---------------- */

    public function testToStringCoversScalars(): void {
        $this->assertSame("", Shift::toString(null));
        $this->assertSame("hello", Shift::toString("hello"));
        $this->assertSame("1", Shift::toString(true));
        $this->assertSame("0", Shift::toString(false));
        $this->assertSame("123", Shift::toString(123));
        $this->assertSame("3.14", Shift::toString(3.14));
    }

    public function testToStringCoversArrayAndObject(): void {
        $this->assertSame("Array", Shift::toString([1,2,3]));

        $obj = new class {
            public function __toString(): string { return "stringy"; }
        };
        $this->assertSame("stringy", Shift::toString($obj));

        $obj2 = new class {};
        $this->assertSame("Object(" . get_class($obj2) . ")", Shift::toString($obj2));
    }

    public function testToStringCoversResource(): void {
        $res = fopen("php://temp", "r+");
        $this->assertStringStartsWith("Resource(", Shift::toString($res));
        fclose($res);
    }

    /* ---------------- toNumber / toInteger / toFloat ---------------- */

    public function testToNumberCoversNullAndBoolean(): void {
        $this->assertSame(0, Shift::toNumber(null));
        $this->assertSame(1, Shift::toNumber(true));
        $this->assertSame(0, Shift::toNumber(false));
    }

    public function testToNumberCoversDecimalAndSeparators(): void {
        $this->assertSame(123, Shift::toNumber("123"));
        $this->assertSame(1000000, Shift::toNumber("1_000_000"));
        $this->assertSame(3.14, Shift::toNumber("3.14"));
        $this->assertSame(2.4e10, Shift::toNumber("2.4e10"));
    }

    public function testToNumberCoversBinaryHexOctal(): void {
        $this->assertSame(5, Shift::toNumber("0b101"));
        $this->assertSame(255, Shift::toNumber("0xFF"));
        $this->assertSame(83, Shift::toNumber("0123")); // octal
    }

    public function testToNumberFallsBackToZero(): void {
        $this->assertSame(0, Shift::toNumber("nonsense"));
    }

    public function testToIntegerAndToFloat(): void {
        $this->assertSame(123, Shift::toInteger("123.9"));
        $this->assertSame(123.9, Shift::toFloat("123.9"));
    }

    /* ---------------- toBoolean ---------------- */

    public function testToBooleanTruthy(): void {
        foreach (["1","true","on","yes","y"] as $truthy) {
            $this->assertTrue(Shift::toBoolean($truthy), "Failed on $truthy");
        }
        $this->assertTrue(Shift::toBoolean(true));
        $this->assertTrue(Shift::toBoolean(1));
    }

    public function testToBooleanFalsy(): void {
        foreach ([null, "", "0","false","off","no","n",false,0] as $falsy) {
            $this->assertFalse(Shift::toBoolean($falsy), "Failed on ".var_export($falsy,true));
        }
    }

    public function testToBooleanRejectsInvalidValues(): void {
        $this->assertFalse(Shift::toBoolean("enabled"));
        $this->assertFalse(Shift::toBoolean("random"));
        $this->assertFalse(Shift::toBoolean([]));
    }
}

