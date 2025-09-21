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
use gabbro\io\RawStream;
use gabbro\collection\Map;
use gabbro\collection\ArrayList;

/**
 * This scanner parses flat file configurations.
 *
 * It uses a simple table like scheme where each line
 * is a row and columns are separated by white space. 
 *
 * You can use \'" to wrap white spaces if you need to. 
 *
 * ```
 * Column1  "Column2 with space"  Column3\ Spaced
 * ```
 *
 * Empty lines are ignored and so are lines with "#" for comments. 
 * A row may also contain less columns that defined by the key table 
 * and it may also contain more. The key table defines max columns to extract.
 */
class FlatFileScanner implements LineParser {

    /** 
     * {@inheritdoc}
     *
     * @override {@see LineParser::scanFile}
     */
    public function scanFile(Stream|string $stream, ImmutableKeyTable $keys): ImmutableStructuredArray {
        if (is_string($stream)) {
            $stream = new RawStream( fopen($stream, "r") ?: null );
        }
        
        /** @var ArrayList<ImmutableMappedArray<string,string>> */
        $list = new ArrayList();
        
        while ($line = $stream->readLine()) {
            $map = $this->scanLine($line, $keys);
            
            if ($map !== null) {
                $list->add($map);
            }
        }
        
        return $list;
    }

    /**
     * {@inheritdoc}
     *
     * @override {@see LineParser::scanLine}
     */
    public function scanLine(string $line, ImmutableKeyTable $keys): ImmutableMappedArray|null {
        $line = trim($line);

        if (empty($line) || $line[0] === "#") {
            return null;
        }

        $values = [];
        $len = strlen($line);
        $i = 0;

        foreach ($keys as $key) {
            $val = "";

            // Skip leading whitespace
            while ($i < $len && ord($line[$i]) <= 32) {
                $i++;
            }

            if ($i >= $len) {
                break;
            }

            // Quoted value?
            $quote = null;
            if ($line[$i] === '"' || $line[$i] === "'") {
                $quote = $line[$i];
                $i++; // skip opening quote
            }

            while ($i < $len) {
                $prev = $ch ?? null;
                $ch   = $line[$i];
                $next = $line[$i+1] ?? null;

                // If quoted, break only at matching quote
                if ($quote !== null) {
                    if ($ch === $quote) {
                        $i++; // skip closing quote
                        break;
                        
                    } else if ($ch === "\\" && $next === $quote) {
                        $val .= $next;
                        $i += 2;
                        continue;
                    }
                    
                    $val .= $ch;
                    $i++;
                    continue;
                }

                // Unquoted mode:
                // whitespace (not escaped) ends the token
                if (ord($ch) <= 32 && $prev !== "\\") {
                    break;
                }

                // backslash escapes whitespace
                if ($ch === "\\" && $next !== null && ord($next) <= 32) {
                    $val .= $next;
                    $i += 2;
                    continue;
                }

                $val .= $ch;
                $i++;
            }

            $values[$key] = $val;
        }

        return new Map($values);
    }
}

