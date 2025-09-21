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
namespace gabbro\utils;

/**
 * This class contains a few conversion methods.
 *
 * Unlike most PHP functions like `strval()` and such, 
 * these functions allow `mixed|null` types which makes static analysis happy, 
 * even when you know the value is safe to use. 
 */
final class Shift {

    /**
     * @ignore
     */
    private function __construct() {}

    /**
     * Convert a value to a proper string.
     * 
     * | Input type                      | Conversion result                          |
     * | ------------------------------- | ------------------------------------------ |
     * | `null`                          | `""` (empty string)                        |
     * | `string`                        | unchanged                                  |
     * | `bool`                          | `"1"` for `true`, `"0"` for `false`        |
     * | `int` / `float`                 | normal string cast (e.g. `123` → `"123"`)  |
     * | `array`                         | `"Array"`                                  |
     * | `object` with `__toString()`    | result of `__toString()`                   |
     * | `object` without `__toString()` | `"Object(<ClassName>)"`                    |
     * | `resource`                      | `"Resource(<resource_type>)"`              |
     * | other / unknown                 | `gettype($data)` (e.g. `"unknown type"`)   |
     *
     * @param mixed|null $data         The value to convert.
     *
     * @return string
     */
    public static function toString(mixed $data): string {
        if (is_null($data)) {
            return "";
            
        } else if (is_string($data)) {
            return $data;
        
        } else if (is_bool($data)) {
            return $data ? "1" : "0";
        
        } else if (is_int($data) || is_float($data)) {
            return (string) $data;
        
        } else if (is_array($data)) {
            return "Array";
        
        } else if (is_object($data)) {
            if (method_exists($data, "__toString")) {
                return (string) $data;
            }
            
            return "Object(" . get_class($data) . ")";
        
        } else if (is_resource($data)) {
            return "Resource(" . get_resource_type($data) . ")";
        }
        
        // fallback for any weird edge cases
        return gettype($data);
    }

    /**
     * Convert a value to a proper number.
     * 
     * | Input type / format        | Conversion result        |
     * | -------------------------- | ------------------------ |
     * | Binary string (`0b...`)    | Parsed as binary         |
     * | Hex string (`0x...`)       | Parsed as hexadecimal    |
     * | Octal string (`0...`)      | Parsed as octal          |
     * | Decimal string             | Parsed as integer/float  |
     * | Boolean (`true`/`false`)   | Converted to `1` or `0`  |
     * | Empty string / `null`      | Converted to `0`         |
     *
     * @param mixed|null $data      The value to convert.
     *
     * @return int|float
     */
    public static function toNumber(mixed $data): int|float {
        if (is_null($data)) {
            return 0;
        
        } else if (is_bool($data)) {
            return $data ? 1 : 0;

        } else if (is_int($data) || is_float($data)) {
            return $data;

        } else if (!is_string($data)) {
            $data = static::toString($data);
        }
        
        $data = trim($data);
        
        if ($data === "") {
            return 0;
        }
        
        // Remove PHP 7.4+ numeric separators
        $data = str_replace("_", "", $data);

        if (ctype_digit($data) || (isset($data[1]) && $data[0] == '-' && ctype_digit(substr($data, 1)))) {
            return ($data[0] === '0' && $data !== "0")
                ? octdec($data)
                : intval($data);

        } else if (str_starts_with($data, "0b") && preg_match("/^0b(?:0|1)+$/i", $data)) {
            return bindec($data);

        } else if (str_starts_with($data, "0x") && preg_match("/^0x[0-9a-f]+$/i", $data)) {
            return hexdec($data);

        } else if (preg_match("/^[+-]?(?:\d+\.?\d*|\.\d+)(?:[eE][+-]?\d+)?$/", $data)) {
            return floatval($data);
        }

        return 0;
    }

    /**
     * Convert a value to a proper integer.
     *
     * This method will run the value through `toNumber()` and
     * then cast it to an integer.
     *
     * @param mixed|null $data      The value to convert.
     *
     * @return int
     */
    public static function toInteger(mixed $data): int {
        return (int) static::toNumber($data);
    }

    /**
     * Convert a value to a proper integer.
     *
     * This method will run the value through `toNumber()` and
     * then cast it to a float.
     *
     * @param mixed|null $data      The value to convert.
     *
     * @return float
     */
    public static function toFloat(mixed $data): float {
        return (float) static::toNumber($data);
    }

    /**
     * Convert a value to a proper boolean.
     *
     * This is a two-in-one method. It validates whether a value can be considered 
     * a truthy/falsy value and then converts it into that value as a boolean. This 
     * has the designed side effect of returning `true` on valid truthy values and 
     * `false` if either the value is not valid truthy/falsy or if the
     * value is considered a valid falsy. 
     *
     * In other words this method returns `true` on valid truthy and `false` otherwise.
     *
     * Recognized truthy values (case-insensitive):
     *   - true, 1, "1", "true", "on", "yes", "y"
     *
     * Recognized falsy values (case-insensitive):
     *   - false, 0, "0", "false", "off", "no", "n", ""
     *
     * @param mixed|null    $data The value to convert.
     *
     * @return bool
     */
    public static function toBoolean(mixed $data): bool {
        if (empty($data)) {
            return false;
        }
        
        return in_array(
            strtolower(trim(static::toString($data))), 
            ["1", "true", "on", "yes", "y"]
        );
    }
}
