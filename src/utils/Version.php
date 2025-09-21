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

use gabbro\exception\InvalidInputException;
use gabbro\utils\Version\Constraint;

/**
 * Version class that uses light flavoured Semantic Versioning 2.0.
 *
 * __Version Scheme__
 *
 * | Scheme                                                           | Example               |
 * | ---------------------------------------------------------------- | --------------------- |
 * | <major>[.<minor>[.<patch>]][-<release>][.<build>][+<meta>]       | 1.0.0-beta.1+20220130 |
 */
class Version {

    /** 
     * @ignore 
     * @var string
     */
    const RX_SCAN = '
        /^(?<x>\d+)                                # major
           (?:\.(?<y>\d+))?                        # optional minor
           (?:\.(?<z>\d+))?                        # optional patch
           (?:[-_](?<r>[A-Za-z]+)                  # prerelease name (letters only)
               (?:\.?(?<b>[0-9]+(?:\.[0-9]+)*))?   # build: 1, 1.2, 2025.09
           )?
           (?:\+(?<m>.+))?                         # metadata
        $/x
    ';
     
    /** 
     * The normalized version string in SemVer-like format.
     * Built from major.minor.patch plus optional release and build parts,
     * e.g. "1.2.3-beta.1".
     *
     * @var string
     * @readonly
     */
    public string $version; 

    /** 
     * The major version number (always present).
     *
     * @var int
     * @readonly
     */
    public int $major;

    /** 
     * The minor version number (optional).
     *
     * @var int|null
     * @readonly
     */
    public int|null $minor;

    /** 
     * The patch version number (optional).
     *
     * @var int|null
     * @readonly
     */
    public int|null $patch;

    /** 
     * Optional build metadata suffix.
     * Appended after a "+" in SemVer, but here stored as plain string.
     *
     * @var string|null
     * @readonly
     */
    public string|null $meta;

    /** 
     * Optional pre-release identifier.
     * Lowercased if present e.g. "alpha", "beta", "rc".
     *
     * @var string|null
     * @readonly
     */
    public string|null $release;

    /** 
     * Optional build/revision number tied to the release.
     *
     * @var int|null
     * @readonly
     */
    public int|null $build;
    
    /**
     * Validate a version against a constraint.
     *
     * @param string|Version $version       The version string or object.
     * @param string $expr                  Constraint to validate against, e.g. "^1.2.3", "~2.0", ">=1.0", "!=2.5"
     *
     * @return bool
     */
    public static function validate(string|Version $version, string $expr): bool { 
        return (new Constraint($expr))->matches($version);
    }
     
    /**
     * Create a new version object.
     *
     * @param string $version       The version string to represent.
     *
     * @return void
     */
    public function __construct(string $version) {
        if (preg_match(Version::RX_SCAN, $version, $match)) {
            $this->major = (int) $match["x"];
            $this->minor   = ($match["y"] ?? "") !== "" ? (int) $match["y"] : null;
            $this->patch   = ($match["z"] ?? "") !== "" ? (int) $match["z"] : null;
            $this->build   = ($match["b"] ?? "") !== "" ? (int) $match["b"] : null;
            $this->release = ($match["r"] ?? "") !== "" ? strtolower($match["r"]) : null;
            $this->meta    = ($match["m"] ?? "") !== "" ? $match["m"] : null;
            
            $version = $this->major;
            $version .= sprintf(".%s", $this->minor ?? 0);
            $version .= sprintf(".%s", $this->patch ?? 0);

            if (!empty($this->release)) {
                $version .= "-{$this->release}";
            }

            if ($this->build !== null && $this->build !== 0) {
                $version .= ".{$this->build}";
            }
            
            $this->version = $version;

        } else {
            throw new InvalidInputException("Invalid version: $version");
        }
    }
    
    /**
     * Match against a version string.
     *
     * This method will create a constraint from this instance
     * and use that to match against a version string.
     *
     * @param string|Version $version       Target version to compare against.
     * @param string|null $operator         The operator to use, see {@see Constraint::addConstraint()}
     *
     * @return bool
     */
    public function matches(string|Version $version, string|null $operator = null): bool {
        $expr = $operator ?? "";
        $expr .= $this->major;
        
        if ($this->minor !== null) {
            $expr .= ".{$this->minor}";
            
            if ($this->patch !== null) {
                $expr .= ".{$this->patch}";
            }
        }

        if (!empty($this->release)) {
            $expr .= "-{$this->release}";
        }

        if ($this->build !== null && $this->build !== 0) {
            $expr .= ".{$this->build}";
        }
        
        return (new Constraint($expr))->matches($version);
    }
}

