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

use IteratorAggregate;
use Traversable;

/**
 * An implementation of the KeyTable interface.
 *
 * @template T
 * @implements IteratorAggregate<int,T&array-key>
 * @implements MutableKeyTable<T>
 */
class KeyTable implements IteratorAggregate, MutableKeyTable {

    /**
     * @ignore
     * @var array<T&array-key,bool>
     */
    protected array $table = [];
    
    /**
     * Create a new Table.
     * 
     * @param iterable<T&array-key>|null $itt      Initial values to add to the set.
     *
     * @return void
     */
    public function __construct(iterable|null $itt = null) {
        if ($itt !== null) {
            $this->addIterable($itt);
        }
    }
    
    /**
     * {inheritdoc}
     *
     * @override {@see MutableKeyTable::set()}
     */
    public function set(mixed $key, bool $flag = true): bool {
        $isset = isset($this->table[$key]);
    
        if (!$isset && $flag) {
            $this->table[$key] = true;
            
        } else if ($isset && !$flag) {
            unset($this->table[$key]);
        }
        
        return $isset;
    }
    
    /**
     * {inheritdoc}
     *
     * @override {@see ImmutableKeyTable::isSet()}
     */
    public function isSet(mixed $key): bool {
        return isset($this->table[$key]);
    }
    
    /**
     * {inheritdoc}
     *
     * @override {@see MutableKeyTable::clear()}
     */
    public function clear(): void {
        $this->table = [];
    }
    
    /**
     * {inheritdoc}
     *
     * @override {@see ImmutableKeyTable::toArray()}
     */
    public function toArray(): array {
        return array_keys($this->table);
    }
    
    /**
     * {inheritdoc}
     *
     * @override {@see MutableKeyTable::addIterable()}
     */
    public function addIterable(iterable $itt): void {
        foreach ($itt as $val) {
            $this->table[$val] = true;
        }
    }
    
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
     * @return Traversable<T&array-key>
     * @override {@see Enumerable}
     */
    public function getIterator(): Traversable {
        foreach ($this->table as $key => $value) {
            yield $key;
        }
    }
    
    /**
     * @ignore
     * @override {@see Serializable::__serialize()}
     */
    public function __serialize(): array {
        return $this->toArray();
    }
    
    /**
     * @ignore
     * @param array<int,T&array-key> $data
     * @override {@see Serializable::__unserialize()}
     */
    public function __unserialize(array $data): void {
        foreach ($data as $val) {
            $this->table[$val] = true;
        }
    }
    
    /**
     * @ignore
     * @override {@see Serializable::__debugInfo()}
     */
    public function __debugInfo(): array {
        return $this->toArray();
    }
    
    /**
     * @ignore
     * @override {@see Serializable::__debugInfo()}
     */
    public function __clone(): void {}
}

