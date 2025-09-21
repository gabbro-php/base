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

use Traversable;
use IteratorAggregate;
use gabbro\exception\DatasetException;

/**
 * A Mapped array implementation.
 * 
 * @template K
 * @template V
 * @implements MutableMappedArray<K&array-key,V>
 * @implements IteratorAggregate<K&array-key,V>
 */
class Map implements MutableMappedArray, IteratorAggregate {

    /**
     * @ignore
     * @var array<K&array-key,V>
     */
    protected array $dataset = [];
    
    /**
     * @ignore
     */
    protected int $length = 0;
    
    /**
     * Create a new Map.
     * 
     * @param iterable<K&array-key,V>|null $itt      Initial values to add to the set.
     *
     * @return void
     */
    public final function __construct(iterable|null $itt = null) {
        if ($itt !== null) {
            $this->addIterable($itt);
        }
    }
    
    /**
     * {@inheritdoc}
     *
     * @override {@see MutableMappedArray::clear()}
     */
    function clear(): void {
        $this->dataset = [];
        $this->length = 0;
    }
    
    /**
     * {@inheritdoc}
     *
     * @override {@see MutableMappedArray::set()}
     */
    function set(mixed $key, mixed $value): mixed {
        $ret = null;
        
        if (isset($this->dataset[$key])) {
            $ret = $this->dataset[$key];
            
        } else {
            $this->length++;
        }
        
        $this->dataset[$key] = $value;
        
        return $ret;
    }

    /**
     * {@inheritdoc}
     *
     * @override {@see MutableMappedArray::unset()}
     */
    function unset(mixed $key): mixed {
        if (!isset($this->dataset[$key])) {
            throw new DatasetException("Trying to unset an already unset key");
        }
        
        $ret = $this->dataset[$key];
        unset($this->dataset[$key]);
        $this->length = max(0, $this->length - 1);
        
        return $ret;
    }
    
    /**
     * {@inheritdoc}
     *
     * @override {@see MutableMappedArray::addIterable()}
     */
    function addIterable(iterable $itt): void {
        foreach ($itt as $key => $value) {
            $this->set($key, $value);
        }
    }
    
    /**
     * {@inheritdoc}
     *
     * @override {@see ImmutableMappedArray::toArray()}
     */
    function toArray(): array {
        return $this->dataset;
    }

    /**
     * {@inheritdoc}
     *
     * @override {@see ImmutableMappedArray::length()}
     */
    function length(): int {
        return $this->length;
    }
    
    /**
     * {@inheritdoc}
     *
     * @override {@see ImmutableMappedArray::get()}
     */
    function get(mixed $key): mixed {
        if (!isset($this->dataset[$key])) {
            throw new DatasetException("Use of unset key");
        }
        
        return $this->dataset[$key];
    }
    
    /**
     * {@inheritdoc}
     *
     * @override {@see ImmutableMappedArray::getOr()}
     */
    function getOr(mixed $key, mixed $default = null): mixed {
        if (!isset($this->dataset[$key])) {
            return $default;
        }
        
        return $this->dataset[$key];
    }
    
    /**
     * {@inheritdoc}
     *
     * @override {@see ImmutableMappedArray::getKey()}
     */
    function getKey(mixed $value): mixed {
        foreach ($this->dataset as $key => &$v) {
            if ($value === $v) {
                return $key;
            }
        }
        
        return null;
    }
    
    /**
     * {@inheritdoc}
     *
     * @override {@see ImmutableMappedArray::isSet()}
     */
    function isSet(mixed $key): bool {
        return isset($this->dataset[$key]);
    }
    
    /**
     * {@inheritdoc}
     *
     * @override {@see ImmutableMappedArray::getValues()}
     */
    function getValues(): ImmutableArray {
        /** @var ImmutableArray<V> $list */
        $list = new ArrayList(array_values($this->dataset));
        
        return $list;
    }

    /**
     * {@inheritdoc}
     *
     * @override {@see ImmutableMappedArray::getKeys()}
     */
    function getKeys(): ImmutableArray {
        /** @var ImmutableArray<string> $list */
        $list = new ArrayList(array_keys($this->dataset));
        
        return $list;
    }
    
    /**
     * {@inheritdoc}
     *
     * @override {@see ImmutableMappedArray::traverse()}
     */
    function traverse(callable $closure): bool {
        foreach ($this->dataset as $key => &$value) {
            if ($closure($key, $value)) {
                return false;
            }
        }
        
        return true;
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
     * @return Traversable<K&array-keys,V>
     * @override {@see Enumerable}
     */
    public function getIterator(): Traversable {
        foreach ($this->dataset as $key => $value) {
            yield $key => $value;
        }
    }
     
    /**
     * @ignore
     * @param (K&array-key)|null $offset
     * @param V $value
     * @override {@see Indexable}
     */
    public function offsetSet($offset, $value): void {
        if (is_null($offset)) {
            throw new DatasetException("Illegal NULL offset on mapped array");
            
        } else {
            $this->set($offset, $value);
        }
    }

    /**
     * @ignore
     * @param K&array-key $offset
     * @return bool
     * @override {@see Indexable}
     */
    public function offsetExists($offset): bool {
        return $this->isSet($offset);
    }

    /**
     * @ignore
     * @param K&array-key $offset
     * @override {@see Indexable}
     */
    public function offsetUnset($offset): void {
        $this->unset($offset);
    }

    /**
     * @ignore
     * @param K&array-key $offset
     * @return V
     * @override {@see Indexable}
     */
    public function offsetGet($offset): mixed {
        return $this->get($offset);
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
     * @param array<K&array-key,V> $data
     * @override {@see Serializable::__unserialize()}
     */
    public function __unserialize(array $data): void {
        $this->addIterable($data);
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

