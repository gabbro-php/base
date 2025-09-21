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
use gabbro\collection\ArrayList;
use gabbro\collection\ArgV;
use gabbro\collection\ArgV\Flag;
use gabbro\collection\ArgV\Option;
use gabbro\collection\ArgV\ArrayOption;
use gabbro\collection\ArgV\Operand;

final class ArgVTest extends TestCase {
    private function makeArgV(array $args): ArgV {
        return new ArgV(new ArrayList($args));
    }

    public function testShortFlagsSplitAndSeparate(): void {
        // -a -b should be same as -ab
        $argv1 = $this->makeArgV(['app', '-a', '-b']);
        $argv2 = $this->makeArgV(['app', '-ab']);

        $a1 = new Flag('-a');
        $b1 = new Flag('-b');
        $a2 = new Flag('-a');
        $b2 = new Flag('-b');

        $this->assertTrue($argv1->parse($a1));
        $this->assertTrue($argv1->parse($b1));
        $this->assertTrue($a1->isSet());
        $this->assertTrue($b1->isSet());

        $this->assertTrue($argv2->parse($a2));
        $this->assertTrue($argv2->parse($b2));
        $this->assertTrue($a2->isSet());
        $this->assertTrue($b2->isSet());
    }

    public function testOptionEqualsAndSeparateValue(): void {
        // --name=value
        $argv1 = $this->makeArgV(['app', '--name=Daniel']);
        $opt1 = new Option('--name');
        $this->assertTrue($argv1->parse($opt1));
        $this->assertTrue($opt1->isSet());
        $this->assertSame('Daniel', $opt1->getValue());

        // --name value
        $argv2 = $this->makeArgV(['app', '--name', 'Daniel']);
        $opt2 = new Option('--name');
        $this->assertTrue($argv2->parse($opt2));
        $this->assertTrue($opt2->isSet());
        $this->assertSame('Daniel', $opt2->getValue());
    }

    public function testArrayOptionEqualsAndSeparate(): void {
        // --tag[]=one --tag[]=two
        $argv1 = $this->makeArgV(['app', '--tag[]=one', '--tag[]=two']);
        $tags1 = new ArrayOption('--tag');
        $this->assertTrue($argv1->parse($tags1));
        $this->assertTrue($argv1->parse($tags1));
        $this->assertSame(['one', 'two'], $tags1->toArray());

        // --tag[] one --tag[] two
        $argv2 = $this->makeArgV(['app', '--tag[]', 'one', '--tag[]', 'two']);
        $tags2 = new ArrayOption('--tag');
        $this->assertTrue($argv2->parse($tags2));
        $this->assertTrue($argv2->parse($tags2));
        $this->assertSame(['one', 'two'], $tags2->toArray());
        
        // --tag[] fail 
        $argv2 = $this->makeArgV(['app', '--tag[]', 'one', '--tag[]', 'two']);
        $tags2 = new ArrayOption('--tags');
        $this->assertFalse($argv2->parse($tags2));
        $this->assertFalse($argv2->parse($tags2));
        $this->assertFalse($tags2->isSet());
    }

    public function testOperandsMixedWithOptions(): void {
        // program run -a --name Daniel file.txt
        $argv = $this->makeArgV(['app', 'run', '-a', '--name', 'Daniel', 'file.txt']);

        $op0 = new Operand(0);
        $flagA = new Flag('-a');
        $optName = new Option('--name');
        $op1 = new Operand(1);

        $this->assertTrue($argv->parse($op0));
        $this->assertTrue($argv->parse($flagA));
        $this->assertTrue($argv->parse($optName));
        $this->assertTrue($argv->parse($op1));

        $this->assertSame('run', $op0->getValue());
        $this->assertTrue($flagA->isSet());
        $this->assertSame('Daniel', $optName->getValue());
        $this->assertSame('file.txt', $op1->getValue());
    }

    public function testDoubleDashStopsParsingOptions(): void {
        $argv = $this->makeArgV(['app', '--', '--notAnOption', '-z']);

        $op0 = new Operand(0);
        $op1 = new Operand(1);

        $this->assertTrue($argv->parse($op0));
        $this->assertTrue($argv->parse($op1));

        $this->assertSame('--notAnOption', $op0->getValue());
        $this->assertSame('-z', $op1->getValue());
    }
}

