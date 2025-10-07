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

namespace gabbro\parser\ArgvParser;

/**
 * Defines an argv operand.
 *
 * Operands are single arguments passed without
 * belonging to an option. An operand is identified
 * only by it's position relative to other operands.
 */
class Operand extends BaseArgument implements IndexedArgument, ValuedArgument {
    
    /**
     * @ignore
     * @var string|null
     */
    protected string|null $value = null;
    
    /**
     * @ignore
     * @var int
     */
    protected int $position = 0;
    
    /**
     * Create a new Operand with description.
     *
     * @param string $title             Title for this option.
     * @param string $desc              Description for this option.
     * @param int $position             The position of the operand
     *
     * @return Operand
     */
    public static function withDescription(string $title, string $desc, int $position): Operand {
        $obj = new Operand($position);
        $obj->setDescription($desc);
        $obj->setTitle($title);
        
        return $obj;
    }

    /**
     * Create a new Operand object.
     *
     * @param int $position      The position of the operand
     *
     * @return void
     */
    public function __construct(int $position) {
        $this->position = $position;
    }
    
    /**
     * {inheritdoc}
     *
     * @override {@see IndexedArgument::getPosition()}
     */
    public function getPosition(): int {
        return $this->position;
    }
    
    /**
     * {inheritdoc}
     *
     * @override {@see ValuedArgument::addValue()}
     */
    public function addValue(string $value): void {
        $this->value = $value;
    }

    /**
     * {inheritdoc}
     *
     * @override {@see ValuedArgument::getValue()}
     */
    public function getValue(string|null $default = null): string|null {
        return $this->value ?? $default;
    }
    
    /* =================================================
     * Internal functions used by PHP
     */
     
    /**
     * @ignore
     * @override {@see Serializable::__serialize()}
     */
    public function __serialize(): array {
        $arr = parent::__serialize();
        $arr["value"] = $this->value;
        $arr["position"] = $this->position;
        
        return $arr;
    }
    
    /**
     * @ignore
     * @param array<string,mixed> $data
     *
     * @override {@see Serializable::__unserialize()}
     */
    public function __unserialize(array $data): void {
        parent::__unserialize($data);
        
        $this->position = is_int($data["position"]) && $data["position"] >= 0 ? $data["position"] : 0;
        $this->value = is_string($data["value"]) ? $data["value"] : null;
    }
}

