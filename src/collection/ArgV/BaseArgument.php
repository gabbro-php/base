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

namespace gabbro\collection\ArgV;

use gabbro\util\Assert;

/**
 * Defines an argv operand.
 *
 * Operands are single arguments passed without
 * belonging to an option. An operand is identified
 * only by it's position relative to other operands.
 */
abstract class BaseArgument implements Argument {

    /**
     * @ignore
     * @var bool
     */
    protected bool $isSet = false;
    
    /**
     * @ignore
     * @var string|null
     */
    protected string|null $title;
    
    /**
     * @ignore
     * @var string|null
     */
    protected string|null $desc;
    
    /**
     * {inheritdoc}
     *
     * @override {@see Argument::getTitle()}
     */
    public function getTitle(): string|null {
        return $this->title;
    }
    
    /**
     * {inheritdoc}
     *
     * @override {@see Argument::getTitle()}
     */
    public function setTitle(string $title): void {
        $this->title = $title;
    }
    
    /**
     * {inheritdoc}
     *
     * @override {@see Argument::getDescription()}
     */
    public function getDescription(): string|null {
        return $this->desc;
    }
    
    /**
     * {inheritdoc}
     *
     * @override {@see Argument::setDesccription()}
     */
    public function setDescription(string $desc): void {
        $this->desc = $desc;
    }
    
    /**
     * {inheritdoc}
     *
     * @override {@see Argument::isSet()}
     */
    public function isSet(bool|null $state = null): bool {
        if ($state !== null) {
            $this->isSet = $state;
        }
        
        return $this->isSet;
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
        return [
            "isSet" => $this->isSet,
            "title" => $this->title,
            "description" => $this->desc
        ];
    }
    
    /**
     * @ignore
     * @param array<string,mixed> $data
     *
     * @override {@see Serializable::__unserialize()}
     */
    public function __unserialize(array $data): void {
        $this->isSet = is_bool($data["isSet"]) ? $data["isSet"] : false;
        $this->title = is_string($data["title"]) ? $data["title"] : null;
        $this->desc  = is_string($data["description"]) ? $data["description"] : null;
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

