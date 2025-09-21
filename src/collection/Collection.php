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

namespace gabbro\collection;


/**
 *
 */
class Collection {

    /**
     * Empty array.
     * 
     * @var int = 0
     */
    public const TYPE_EMPTY = 0;
    
    /**
     * List where keys are unordered integers.
     * 
     * @var int = 1
     */
    public const TYPE_SET   = 1;
    
    /**
     * List where keys are 0..n-1 in order.
     * 
     * @var int = 2
     */
    public const TYPE_LIST  = 2;
    
    /**
     * Map with mixed or string keys.
     * 
     * @var int = 3
     */
    public const TYPE_MAP   = 3;

    /**
     * Check the type of a PHP array.
     *
     * @param mixed[] &$arr    The array to check.
     *
     * @return Collection::TYPE_*
     */
    public static function type(array &$arr): int {
        if (empty($arr)) {
            return Collection::TYPE_EMPTY;
        }
        
        $isMap = false;
        $isList = true;
        $i = 0;
        
        foreach ($arr as $k => &$v) {
            if ($k !== $i) {
                if (is_string($k)) {
                    $isMap = true;
                    break;  // Not a Set and not a List, no need to check further keys.
                }
            
                $isList = false;
            }
            
            $i++;
        }
        
        if ($isMap) {
            return Collection::TYPE_MAP;
            
        } else if ($isList) {
            return Collection::TYPE_LIST;
            
        } else {
            return Collection::TYPE_SET; // Not really a Set, just an unordered list.
        }
    }
}

