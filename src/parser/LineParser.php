<?php declare(strict_types=1);
/*
 * This file is part of the LXC Manager Project: https://github.com/LXCTRL
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

namespace gabbro\parser;

use gabbro\collection\ImmutableStructuredArray;
use gabbro\collection\ImmutableMappedArray;
use gabbro\collection\ImmutableKeyTable;
use gabbro\io\Stream;

/**
 * Defines a line parser. 
 *
 * The interface does not define how a line is parsed. 
 * The parser should simply take a string and produce a mapped
 * output with the requested keys.
 */
interface LineParser {

    /**
     * Parse a file.
     * Scan an entire file an return a list of parsed lines.
     *
     * @param Stream|string $stream             The file to scan.
     * @param ImmutableKeyTable<string> $keys   Keys to produce in the output map.
     *
     * @return ImmutableStructuredArray<ImmutableMappedArray<string,string>>
     */
    function scanFile(Stream|string $stream, ImmutableKeyTable $keys): ImmutableStructuredArray;

    /**
     * Parse a string.
     *
     * @param string $line                      The line to parse.
     * @param ImmutableKeyTable<string> $keys   Keys to produce in the output map.
     *
     * @return ImmutableMappedArray<string,string>|null
     */
    function scanLine(string $line, ImmutableKeyTable $keys): ImmutableMappedArray|null;
}

