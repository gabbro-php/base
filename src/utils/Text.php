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

namespace gabbro\utils;

/**
 * Text utility helpers.
 *
 * Provides lightweight string handling functions,
 * focusing on multibyte safety and text formatting.
 */
class Text {

    /**
     * Calculate the length of a UTF-8 string in characters.
     *
     * Uses `mb_strlen()` if available; otherwise falls back
     * to a manual byte scan that counts non-continuation bytes.
     *
     * @param string $text   The input string (UTF-8 expected).
     *
     * @return int           The number of characters in the string.
     */
    public static function utf8_strlen(string $text): int {
        if (function_exists("mb_strlen")) {
            return mb_strlen($text, "UTF-8");
        }
        
        $len = 0;
        $bytes = strlen($text);
        
        for ($i = 0; $i < $bytes; $i++) {
            // Count only if not a continuation byte (10xxxxxx)
            if ((ord($text[$i]) & 0xC0) !== 0x80) {
                $len++;
            }
        }
        
        return $len;
    }

    /** 
     * Wrap a block of text to a given width and apply optional left padding.
     *
     * Uses `wordwrap()` internally to insert line breaks. Each line is then
     * padded with the specified number of spaces. The max length will stay
     * within padding size + line length.
     *
     * @param string $text      The input string to wrap.
     * @param int    $maxLen    Maximum line length before wrapping.
     * @param int    $padding   Number of spaces to add at the start of each line.
     *
     * @return string           The wrapped and padded text.
     */
    public static function wrapText(string $text, int $maxLen, int $padding = 0): string {
        $maxLen = $maxLen - $padding;
        $wrapped = wordwrap($text, $maxLen, "\n");
        $lines = explode("\n", $wrapped);
        $padStr = "";
        
        foreach ($lines as &$line) {
            $line = $padStr . $line;
            $padStr = str_repeat(" ", $padding);
        }

        return implode("\n", $lines);
    }
    
}
    
