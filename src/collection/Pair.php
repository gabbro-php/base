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

use gabbro\feature\Serializable;
use gabbro\feature\Cloneable;

/**
 * A simple Key/Value pair class.
 *
 * @template K
 * @template V
 * @template M = mixed|null
 */
final class Pair implements Serializable, Cloneable {

    /**
     * @ignore
     */
    public function __construct(
        /**
         * @var K $key
         */
        public mixed $key,
        
        /**
         * @var V $value
         */
        public mixed $value,
        
        /**
         * @var M $meta
         */
        public mixed $meta = null
    ) {}
    
    /**
     * {@inheritdoc}
     *
     * @override {@see Cloneable::clone()}
     */
    function clone(): static {
        return clone $this;
    }
    
    /* =================================================
     * Internal functions used by PHP
     */
    
    /**
     * @ignore
     * @override {@see Serializable::__serialize()}
     */
    public function __serialize(): array {
        return [
            "key" => $this->key,
            "value" => $this->value,
            "meta" => $this->meta
        ];
    }
    
    /**
     * @ignore
     * @param array{
     *      "key": K,
     *      "value": V,
     *      "meta": M
     * } $data
     * @override {@see Serializable::__unserialize()}
     */
    public function __unserialize(array $data): void {
        $this->key = $data["key"];
        $this->value = $data["value"];
        $this->meta = $data["meta"];
    }
    
    /**
     * @ignore
     * @override {@see Serializable::__debugInfo()}
     */
    public function __debugInfo(): array {
        return $this->__serialize();
    }
    
    /**
     * @ignore
     * @override {@see Serializable::__debugInfo()}
     */
    public function __clone(): void {}
}

