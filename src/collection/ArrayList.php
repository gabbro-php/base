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

use OutOfBoundsException;
use IteratorAggregate;
use Traversable;
use gabbro\toolbox\ArrayKeys;
use gabbro\exception\DatasetException;

/**
 * A Structured array implementation.
 *
 * @template T
 * @implements MutableStructuredArray<T>
 * @implements IteratorAggregate<int,T>
 */
class ArrayList implements MutableStructuredArray, IteratorAggregate {

    use ArrayKeys;

    /**
     * @ignore
     * @var array<int,T>
     */
    protected array $dataset = [];
    
    /**
     * @ignore
     */
    protected int $length = 0;
    
    /**
     * Create a new ArrayList.
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
     * @override {@see ImmutableStructuredArray::indexOf()}
     */
    function indexOf(mixed $value, int $offset = 0): int {
        try {
            $offset = $this->normalizeIndex($offset, $this->length, true);
            
        } catch(OutOfBoundsException $e) {
            throw new DatasetException($e->getMessage(), $e->getCode(), $e);
        }
    
        for ($pos = $offset; $pos < $this->length; $pos++) {
            if ($this->dataset[$pos] === $value) {
                return $pos;
            }
        }
    
        return -1;
    }
    
    /**
     * {@inheritdoc}
     *
     * @override {@see ImmutableStructuredArray::get()}
     */
    function get(int $pos): mixed {
        try {
            $pos = $this->normalizeIndex($pos, $this->length);
            
        } catch(OutOfBoundsException $e) {
            throw new DatasetException($e->getMessage(), $e->getCode(), $e);
        }
        
        return $this->dataset[$pos];
    }
    
    /**
     * {@inheritdoc}
     *
     * @override {@see ImmutableStructuredArray::getOr()}
     */
    function getOr(int $pos, mixed $default = null): mixed {
        try {
            $pos = $this->normalizeIndex($pos, $this->length);
            
        } catch(OutOfBoundsException $e) {
            return $default;
        }
        
        return $this->dataset[$pos];
    }
    
    /**
     * {@inheritdoc}
     *
     * @override {@see ImmutableStructuredArray::isSet()}
     */
    function isSet(int $pos): bool {
        try {
            $this->normalizeIndex($pos, $this->length);
            
        } catch(OutOfBoundsException $e) {
            return false;
        }
        
        return true;
    }
    
    /**
     * {@inheritdoc}
     *
     * @override {@see MutableStructuredArray::set()}
     */
    function set(int $pos, mixed $value): mixed {
        try {
            $pos = $this->normalizeIndex($pos, $this->length, true);
            
        } catch(OutOfBoundsException $e) {
            throw new DatasetException($e->getMessage(), $e->getCode(), $e);
        }
            
        $ret = null;
        
        if (isset($this->dataset[$pos])) {
            $ret = $this->dataset[$pos];
            
        } else {
            $this->length++;
        }
        
        $this->dataset[$pos] = $value;
        
        return $ret;
    }

    /**
     * {@inheritdoc}
     *
     * @override {@see MutableStructuredArray::unset()}
     */
    function unset(int $pos): mixed {
        try {
            $pos = $this->normalizeIndex($pos, $this->length);
            
        } catch(OutOfBoundsException $e) {
            throw new DatasetException($e->getMessage(), $e->getCode(), $e);
        }
        
        $ret = $this->dataset[$pos];
        array_splice($this->dataset, $pos, 1);
        $this->length = max(0, $this->length - 1);
        
        return $ret;
    }

    /**
     * {@inheritdoc}
     *
     * @override {@see MutableStructuredArray::insert()}
     */
    function insert(int $pos, mixed $value): void {
        try {
            $pos = $this->normalizeIndex($pos, $this->length, true);
            
        } catch(OutOfBoundsException $e) {
            throw new DatasetException($e->getMessage(), $e->getCode(), $e);
        }
        
        if ($pos == $this->length) {
            $this->dataset[] = $value;
        
        } else {
            array_splice($this->dataset, $pos, 0, [$value]);
        }
            
        $this->length++;
    }

    /**
     * {@inheritdoc}
     *
     * @override {@see MutableArray::add()}
     */
    function add(mixed $value): void {
        $this->dataset[] = $value;
        $this->length++;
    }

    /**
     * {@inheritdoc}
     *
     * @override {@see MutableArray::remove()}
     */
    function remove(mixed $value): int {
        $len = $this->length;
    
        for ($pos = $len-1; $pos >= 0; $pos--) {
            if ($this->dataset[$pos] === $value) {
                array_splice($this->dataset, $pos, 1);
                $this->length = max(0, $this->length - 1);
            }
        }
        
        return $len - $this->length;
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
        return $this->dataset;
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
        return $this->indexOf($value) >= 0;
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
     * {@inheritdoc}
     *
     * @override {@see ImmutableStructuredArray::sort()}
     */
    function sort(callable $closure): void {
        usort($this->dataset, $closure);
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
     * @return Traversable<int,T>
     * @override {@see Enumerable}
     */
    public function getIterator(): Traversable {
        foreach ($this->dataset as $value) {
            yield $value;
        }
    }
     
    /**
     * @ignore
     * @param int|null $offset
     * @param T $value
     * @override {@see Indexable}
     */
    public function offsetSet($offset, $value): void {
        if (is_null($offset)) {
            $this->add($value);
            
        } else {
            $this->set($offset, $value);
        }
    }

    /**
     * @ignore
     * @param int $offset
     * @return bool
     * @override {@see Indexable}
     */
    public function offsetExists($offset): bool {
        try {
            $this->normalizeIndex($offset, $this->length);
            
        } catch(OutOfBoundsException $e) {
            return false;
        }
        
        return true;
    }

    /**
     * @ignore
     * @param int $offset
     * @override {@see Indexable}
     */
    public function offsetUnset($offset): void {
        $this->unset($offset);
    }

    /**
     * @ignore
     * @param int $offset
     * @return T
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

