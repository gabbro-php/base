<?php declare(strict_types=1);
/*
 * This file is part of the Gabbro Project: https://github.com/Gabbro-PHP
 *
 * Copyright (c) 2022 Daniel Bergløv, License: MIT
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

namespace gabbro\utils\Version;

use gabbro\exception\InvalidInputException;
use gabbro\utils\Version;

/**
 * A version constraint class.
 *
 * This class can be used to create version constraints that can later be used to
 * match version strings against. You can see {@see Constraint::addConstraint()} to see 
 * the options for the constraints.
 *
 * __Example__  
 *
 * ```
 * $obj = new Constraint();
 * $obj->addConstraint(">=1.2.3");
 * $obj->addConstraint("<1.3.0");
 *
 * if ($obj->matches("1.2.5")) {
 *
 * }
 * ```
 *
 * ```
 * $obj = new Constraint("~1.2.3");
 *
 * if ($obj->matches("1.2.5")) {
 *
 * }
 * ```
 */
final class Constraint {
    
    /**
     * @ignore
     * @var array<int<0,max>,string[]>
     */
    private array $ranges = [];

    /**
     * Create a new constraint object.
     *
     * @param string|null $expr     Optional constraint. 
     *                              {@see Constraint::addConstraint()}
     *
     * @return void
     */
    public function __construct(string|null $expr = null) {
        if ($expr !== null) {
            $this->ranges = $this->parse($expr);
        }
    }

    /**
     * Check to see of a version matches this constraint.
     *
     * @param Version|string $version       The version to validate.
     *
     * @return bool
     */
    public function matches(Version|string $version): bool {
        $version = is_object($version) ? $version->version : $version;
    
        foreach ($this->ranges as [$op, $target]) {
            $negate = false;
            
            if ($op == "!=") {
                $negate = true;
                $op = "=";
            }
        
            $result = version_compare($version, $target, $op);
            
            if ($negate ? $result : !$result) {
                return false;
            }
        }
        
        return true;
    }
    
    /**
     * Add a constraint to this instance.
     * 
     * | Operator | Description                                                                    |
     * | -------- | ------------------------------------------------------------------------------ |
     * | =        | Equal to (`version == target`). Not required as this is the default.           |
     * | >        | Greater than (`version > target`)                                              |
     * | <        | Less than (`version < target`)                                                 |
     * | !=       | Not equal to (`version != target`)                                             |
     * | >=       | Greater than or equal to (`version >= target`)                                 |
     * | <=       | Less than or equal to (`version <= target`)                                    |
     * | ~        | Tilde range: `>= target` and `< next minor` (e.g. `~1.2.3` → `>=1.2.3 <1.3.0`) |
     * | ^        | Caret range: `>= target` and `< next major` (e.g. `^1.2.3` → `>=1.2.3 <2.0.0`) |
     *
     * You can also, instead of operators, add wildcard e.g. `1.*`, `1.2.*`.
     *
     * @param string $expr       The constraint to add.
     *
     * @return void
     */
    public function addConstraint(string $expr): void {
        $this->ranges = array_merge(
            $this->ranges, 
            $this->parse($expr)
        );
    }
    
    /**
     * @ignore
     *
     * Internal parser method. 
     * This method takes an expr like `>=1.2.3` or `1.2.*` and parses it to 
     * range arrays. These are later used when comparing a version string.
     *
     * @param string $expr
     *
     * @return array<int<0,max>,string[]>
     */
    private function parse(string $expr): array {
        $expr = trim($expr);

        // Step 1: extract operator
        if (preg_match('/^([!<>=~^]+)(.+)$/', $expr, $m)) {
            $operator = $m[1];
            $pattern  = trim($m[2]);
        } else {
            $operator = "=";
            $pattern  = $expr;
        }
        
        if (!preg_match('/^(?:\d+|\*)(?:\.(?:\d+|\*)){0,2}(?:[-+].*)?$/', $pattern)) {
            throw new InvalidInputException("Invalid version in constraint: {$expr}");
        }

        // Extract release suffix (e.g. -beta, -alpha.1)
        $release = null;
        if (preg_match('/^(.+?)(-[0-9A-Za-z\._-]+)$/', $pattern, $m)) {
            $pattern = $m[1];
            $release = $m[2];
        }

        // Wildcards (*)
        if (str_contains($pattern, "*")) {
            return $this->expandWildcard($pattern, $release);
        }

        // Ranges (~ or ^)
        if ($operator === "~" || $operator === "^") {
            return $this->expandRange($operator, $pattern, $release);
        }

        // Simple comparisons (=, !=, <, <=, >, >=)
        return [[$operator, $pattern . ($release ?? "")]];
    }

    /**
     * @ignore
     *
     * Internal method that unwraps an expr like `1.2.*` into 
     * a range array.
     *
     * @param string $pattern
     * @param string|null $release
     *
     * @return array<int<0,max>,string[]>
     */
    private function expandWildcard(string $pattern, string|null $release): array {
        if ($pattern === "*") {
            // Wildcard for all versions
            return [[">=", "0.0.0"]];
        }

        $parts = explode(".", rtrim($pattern, ".*"));

        if (count($parts) === 1) {
            $major = (int)$parts[0];
            return [
                [">=", "$major.0.0" . ($release ?? "")],
                ["<",  ($major+1).".0.0"]
            ];
            
        } else if (count($parts) === 2) {
            [$major, $minor] = array_map("intval", $parts);
            return [
                [">=", "$major.$minor.0" . ($release ?? "")],
                ["<",  "$major." . ($minor+1) . ".0"]
            ];
            
        } else if (count($parts) === 3) {
            [$major, $minor, $patch] = array_map("intval", $parts);
            return [
                [">=", "$major.$minor.$patch" . ($release ?? "")],
                ["<",  "$major.$minor." . ($patch+1)]
            ];
        }

        throw new InvalidInputException("Invalid wildcard: $pattern");
    }

    /**
     * @ignore
     *
     * Internal method that unwraps an expr like `~1.2` or `^1.2` into 
     * a range array.
     *
     * @param string $operator
     * @param string $pattern
     * @param string|null $release
     *
     * @return array<int<0,max>,string[]>
     */
    private function expandRange(string $operator, string $pattern, string|null $release): array {
        // Parse major.minor.patch
        $parts = explode(".", $pattern);
        $major = intval($parts[0]);
        $minor = isset($parts[1]) ? intval($parts[1]) : 0;
        $patch = isset($parts[2]) ? intval($parts[2]) : 0;

        if ($operator === "~") {
            if (isset($parts[1])) {
                // ~1.2.3 or ~1.2 → lock minor
                return [
                    [">=", "$major.$minor.$patch" . ($release ?? "")],
                    ["<",  "$major." . ($minor+1) . ".0"]
                ];
            }
            
            // ~1 → lock major
            return [
                [">=", "$major.0.0" . ($release ?? "")],
                ["<",  ($major+1) . ".0.0"]
            ];
            
        } else if ($operator === "^") {
            if ($major > 0) {
                return [
                    [">=", "$major.$minor.$patch" . ($release ?? "")],
                    ["<",  ($major+1) . ".0.0"]
                ];
                
            } else if ($minor > 0) {
                return [
                    [">=", "$major.$minor.$patch" . ($release ?? "")],
                    ["<",  "0." . ($minor+1) . ".0"]
                ];
            }
            
            return [
                [">=", "$major.$minor.$patch" . ($release ?? "")],
                ["<",  "0.0." . ($patch+1)]
            ];
        }

        throw new InvalidInputException("Invalid constraint: {$operator}{$pattern}");
    }
}

