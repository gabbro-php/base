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
use gabbro\toolbox\ArrayKeys;
use Traversable;


/**
 * Implementation of MutableArray using hashed keys.
 *
 * This Set is using hashed keys to ensure the uniqueness of the stored values. 
 * This makes lookup a lot faster, which is important for a Set. Adding, searching, removing values 
 * are all done in O(1).
 *
 * @template T
 * @implements MutableArray<T>
 * @implements IteratorAggregate<int,T>
 */
class HashSet implements MutableArray, IteratorAggregate {

    use ArrayKeys;
    
    /**
     * @ignore
     * @var array<string,T>
     */
    protected array $dataset = [];
    
    /**
     * @ignore
     */
    protected int $length = 0;
    
    /**
     * Create a new Set.
     * 
     * @param iterable<T>|null $itt      Initial values to add to the set.
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
     * @override {@see MutableArray::clear()}
     */
    function clear(): void {
        $this->dataset = [];
        $this->length = 0;
    }

    /**
     * {@inheritdoc}
     *
     * @override {@see MutableArray::add()}
     */
    function add(mixed $value): void {
        $key = $this->hashKey($value);
    
        if (!isset($this->dataset[$key])) {
            $this->length++;
        }
    
        $this->dataset[ $this->hashKey($value) ] = $value;
    }

    /**
     * {@inheritdoc}
     *
     * @override {@see MutableArray::remove()}
     */
    function remove(mixed $value): int {
        $key = $this->hashKey($value);
        
        if (isset($this->dataset[$key])) {
            unset($this->dataset[$key]);
            
            $this->length = max(0, $this->length - 1);
            
            return 1;
        }
        
        return 0;
    }

    /**
     * {@inheritdoc}
     *
     * @override {@see MutableArray::addIterable()}
     */
    function addIterable(iterable $itt): void {
        foreach ($itt as $value) {
            $this->add($value);
        }
    }
    
    /**
     * {@inheritdoc}
     *
     * @override {@see ImmutableArray::toArray()}
     */
    function toArray(): array {
        $arr = [];
        
        foreach ($this->dataset as $value) {
            $arr[] = $value;
        }
        
        return $arr;
    }

    /**
     * {@inheritdoc}
     *
     * @override {@see ImmutableArray::length()}
     */
    function length(): int {
        return $this->length;
    }

    /**
     * {@inheritdoc}
     *
     * @override {@see ImmutableArray::join()}
     */
    function join(string|null $delimiter = null): string {
        return implode($delimiter ?: '', $this->dataset);
    }

    /**
     * {@inheritdoc}
     *
     * @override {@see ImmutableArray::contains()}
     */
    function contains(mixed $value): bool {
        return isset($this->dataset[ $this->hashKey($value) ]);
    }

    /**
     * {@inheritdoc}
     *
     * @override {@see ImmutableArray::filter()}
     */
    function filter(callable $closure): static {
        /** @var static<T> $ret */
        $ret = new static();
        
        foreach ($this->dataset as $value) {
            if ($closure($value)) {
                $ret->add($value);
            }
        }
        
        return $ret;
    }
    
    /**
     * {@inheritdoc}
     *
     * @override {@see ImmutableArray::traverse()}
     */
    function traverse(callable $closure): bool {
        foreach ($this->dataset as &$value) {
            if ($closure($value)) {
                return false;
            }
        }
        
        return true;
    }
    
    /**
     * {@inheritdoc}
     *
     * @override {@see ImmutableArray::find()}
     */
    function find(callable $closure): mixed {
        foreach ($this->dataset as $value) {
            if ($closure($value)) {
                return $value;
            }
        }
        
        return null;
    }
    
    /**
     * @ignore
     * @return Traversable<T>
     * @override {@see Enumerable}
     */
    public function getIterator(): Traversable {
        foreach ($this->dataset as $value) {
            yield $value;
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
     * @override {@see Serializable::__serialize()}
     */
    public function __serialize(): array {
        return $this->toArray();
    }
    
    /**
     * @ignore
     * @param T[] $data
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

