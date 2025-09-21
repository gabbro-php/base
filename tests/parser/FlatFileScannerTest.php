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
namespace gabbro\test\parser;

use gabbro\parser\FlatFileScanner;
use gabbro\collection\KeyTable;
use gabbro\io\Stream;
use gabbro\io\RawStream;
use PHPUnit\Framework\TestCase;

use gabbro\io\VariableStream;

final class FlatFileScannerTest extends TestCase {

    private function createStreamWithContent(string $content): Stream {
        $stream = new RawStream();
        $stream->write($content);
        $stream->moveToStart();
        return $stream;
    }

    public function testParsesSimpleRows(): void {
        $scanner = new FlatFileScanner();
        $stream = $this->createStreamWithContent(
            <<<TXT
            # comment line
            key1 value1 value2
            key2 "quoted value" value3
            key3 value\\ with\\ spaces "another one"  last
            TXT
        );

        $keys = new KeyTable(["col1", "col2", "col3"]);
        $result = $scanner->scanFile($stream, $keys)->toArray();

        $this->assertCount(3, $result);

        $this->assertSame([
            "col1" => "key1",
            "col2" => "value1",
            "col3" => "value2",
        ], $result[0]->toArray());

        $this->assertSame([
            "col1" => "key2",
            "col2" => "quoted value",
            "col3" => "value3",
        ], $result[1]->toArray());

        $this->assertSame([
            "col1" => "key3",
            "col2" => "value with spaces",
            "col3" => "another one",
        ], $result[2]->toArray());
        
        $stream->close();
    }

    public function testIgnoresEmptyAndCommentLines(): void {
        $scanner = new FlatFileScanner();
        $stream = $this->createStreamWithContent(
            <<<TXT

            # just a comment
            row1 col1 col2
            TXT
        );

        $keys = new KeyTable(["a", "b", "c"]);
        $result = $scanner->scanFile($stream, $keys)->toArray();

        $this->assertCount(1, $result);
        $this->assertSame(["a" => "row1", "b" => "col1", "c" => "col2"], $result[0]->toArray());
        
        $stream->close();
    }

    public function testHandlesMissingColumns(): void {
        $scanner = new FlatFileScanner();
        $stream = $this->createStreamWithContent("onlyOneCol");

        $keys = new KeyTable(["first", "second", "third"]);
        $result = $scanner->scanFile($stream, $keys)->toArray();

        $this->assertCount(1, $result);
        $this->assertSame(["first" => "onlyOneCol"], $result[0]->toArray());
        
        $stream->close();
    }
}
