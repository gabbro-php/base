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
namespace gabbro\test\io;

use PHPUnit\Framework\TestCase;
use gabbro\io\Shell;
use gabbro\io\IOStream;

final class ShellTest extends TestCase {

    public function testExecCapturesStdout(): void {
        $res = Shell::exec("echo HelloWorld");

        $this->assertTrue($res->isSuccessful(), "Command should succeed");
        $this->assertSame(0, $res->getExitCode(), "Exit code must be 0");

        $stdout = $res->getStream(IOStream::STDOUT)->readAll();
        $stderr = $res->getStream(IOStream::STDERR)->readAll();

        $this->assertSame("HelloWorld\n", $stdout);
        $this->assertSame("", $stderr);
    }

    public function testExecCapturesStderr(): void {
        // Use sh to force output to stderr
        $res = Shell::exec("sh -c 'echo ErrorMsg 1>&2'");

        $stdout = $res->getStream(IOStream::STDOUT)->readAll();
        $stderr = $res->getStream(IOStream::STDERR)->readAll();

        $this->assertSame("", $stdout);
        $this->assertSame("ErrorMsg\n", $stderr);
    }

    public function testExecExitCode(): void {
        // `false` is a shell built-in that exits with 1
        $res = Shell::exec("false");

        $this->assertFalse($res->isSuccessful());
        $this->assertSame(1, $res->getExitCode());
    }

    public function testStartInteractive(): void {
        $shell = Shell::start("cat"); // cat echoes stdin → stdout

        $stdin  = $shell->getStream(IOStream::STDIN);
        $stdout = $shell->getStream(IOStream::STDOUT);

        // Send two lines
        $stdin->println("LineOne");
        $stdin->println("LineTwo");

        // Wait until stdout is readable
        $ready = $stdout->wait(1.0); // wait up to 1 second
        $readable = $ready->isReadable();
        $this->assertTrue($readable, "stdout should become readable");

        if ($readable) {
            $output = $stdout->readLine();
            $output .= $stdout->readLine();
            
            $this->assertStringContainsString("LineOne", $output);
            $this->assertStringContainsString("LineTwo", $output);
        }
        
        $shell->stop();
    }

    public function testGetConsoleWidth(): void {
        $cols = Shell::getConsoleWidth();

        $this->assertIsInt($cols);
        $this->assertGreaterThan(0, $cols);
    }
}

