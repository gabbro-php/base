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

/**
 * General-purpose parsing utilities.
 *
 * This class provides a collection of static methods that handle common
 * parsing patterns.  
 *
 * Each method is self-contained (no state is stored) and focuses on
 * transforming or analyzing input text according to a specific pattern.  
 */
final class Parser {

    /**
     * Parse a format string with custom placeholders and resolve them via a callback.
     *
     * This works similarly to `sprintf()` format strings, but instead of substituting
     * values directly, each placeholder is passed to the given callback.  
     *
     * Placeholders have the following syntax:
     *  - `%s`, `%i`, `%x` … (a single-letter type).
     *  - `%12s` … (single-letter type with explicit target index).
     *  - `%{type}`, `%{custom}` … (multi-character type name).
     *  - `%12{type}` … (multi-character type with explicit target index).
     *
     * Escaping: a literal percent sign can be written as `\%`.
     *
     * Example:
     * ```php
     * $out = Parser::formatCallback("Hello %s, you have %{count} messages.", 
     *     function (string $type, int $target, int $index): string {
     *         if ($type === "s") return "World";
     *         if ($type === "count") return "5";
     *         return "?";
     *     });
     *
     * // $out === "Hello World, you have 5 messages."
     * ```
     *
     * @param string   $text
     *      The format string containing placeholders.
     *
     * @param callable(string $type, int $target, int $index): string $callback
     *      Callback that receives:
     *        - `$type`: the type identifier (`s`, `i`, `custom`, …).
     *        - `$target`: zero-based target index (from `%5s` → 4, `%s` → auto-increment).
     *        - `$index`: the sequential index of this placeholder (0, 1, 2, …).
     *      Must return the replacement string.
     *
     * @return string
     *      The format string with placeholders replaced by the callback results.
     */
    public static function formatCallback(string $text, callable $callable): string {
        $x = 0;
        $y = 0;
        
        return preg_replace_callback(
            '/(?<!\\\\)%(\d+)?(?:([a-z])|\{([a-z0-9]+)\})/i', 
            function($m) use (&$x, &$y, $callable){
                $index = $y++;
                $target = !empty($m[1]) ? intval($m[1]) - 1 : $x++;
                $param = $m[2] ?: $m[3];

                return $callable($param, $target, $index);

            }, 
            $text
            
        ) ?: $text;
    }
}

