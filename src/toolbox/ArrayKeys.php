<?php declare(strict_types=1);
/*
 * This file is part of the Gabbro Project: https://github.com/Gabbro-PHP
 *
 * Copyright (c) 2018 Daniel Bergløv, License: MIT
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

namespace gabbro\toolbox;

use RuntimeException;
use OutOfBoundsException;

/**
 * 
 */
trait ArrayKeys {
    
    /**
     * Create a new hash key.
     *
     * This can be used to creates keys that can be used
     * on PHP arrays and other areas where you need a unique identifier
     * for a value.
     *
     * @param mixed $key    Value to hash.
     *
     * @return string       The hashed value.
     */
    protected function hashKey(mixed $key): string {
        switch (gettype($key)) {
            case 'integer':
                // Type tag 'i' + 64-bit LE encoding
                return "i" . pack('P', $key); // 'P' = machine-size unsigned (64-bit on 64-bit PHP)
            case 'double': // float
                // Type tag 'f' + IEEE-754 bytes (LE)
                return "f" . pack('d', $key);
            case 'string':
                // Type tag 's' + hash of the string bytes
                return "s" . hash('xxh64', $key, true);
            case 'boolean':
                // Type tag 'b' + 1 byte
                return "b" . ($key ? "\x01" : "\x00");
            case 'NULL':
                return "n"; // null
            case 'object':
                return "o" . pack('P', spl_object_id($key));
            case 'resource':
            case 'resource (closed)':
                // Treat like object identity via cast to int id
                return "r" . pack('P', (int)$key);
            case 'array':
                // This would require canonicalizing the entire array. 
                // This would produce way to much overhead for a hashkey.
                // You could serialize it, but the result would be terrible and still very slow.
                throw new RuntimeException("Invalid use of array as a hashkey");
            default:
                // Fallback via serialize() + hash
                return "x" . hash('xxh64', serialize($key), true);
        }
    }
    
    /**
     * Normalizes array positions to allow for backwards positions.
     *
     * ```
     * normalizeIndex(4, 5) returns 4
     * normalizeIndex(5, 5) throws OutOfBoundsException
     * normalizeIndex(5, 5, true) returns 5
     * normalizeIndex(-2, 5) returns 3
     * normalizeIndex(-1, 5) returns 4
     * normalizeIndex(-1, 0) returns 0
     * normalizeIndex(-2, 0) throws OutOfBoundsException
     * ```
     *
     * @param int $pos                  The position to normalize.
     * @param int $length               The length to reflect normalization on.
     * @param bool $allowLengthPos      If this is set to `true` then `$length` is allowed as position. 
     *                                  Otherwise `$pos == $length` will throw an exception.
     *
     * @return int                      The normalized position.
     *
     * @throws OutOfBoundsException     If the normalized position get's out of range.
     */
    protected function normalizeIndex(int $pos, int $length, bool $allowLengthPos = false): int {
        if ($pos == -1) {
            $pos = max(0, $length + $pos);
            
        } else if ($pos < 0) {
            $pos = $length + $pos;
        }

        if ($pos < 0 || $pos > $length || ($pos == $length && !$allowLengthPos)) {
            throw new OutOfBoundsException("Index $pos out of range");
        }
        
        return $pos;
    }
}

