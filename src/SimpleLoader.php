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
namespace gabbro;

if (!interface_exists("gabbro\\ClassLoader", false)) {
    require "ClassLoader.php";
}

use Closure;
use Exception;

/**
 * Internal function to include class files
 * in a clean scope.
 *
 * @param string $file
 * @return mixed
 *
 * @ignore
 */
function _main_include(string $file): mixed {
    return require $file;
}

/**
 * An implementation of the `ClassLoader` interface.
 * This ClassLoader implements `PSR4` lookup. 
 */
final class SimpleLoader implements ClassLoader {

    /**
     * @ignore
     * @var SimpleLoader|null
     */
    private static SimpleLoader|null $instance = null;
    
    /**
     * @ignore
     * @var array<string,string|null>
     */
    private array $paths = [];
    
    /**
     * @ignore
     * @var array<int,string>
     */
    private array $ext = ["php"];

    /**
     * @ignore
     * @var Closure|null
     */
    private Closure|null $loader = null;
    
    /**
     * Get the main instance of this class.
     *
     * @return SimpleLoader
     */
    public static function getInstance(): SimpleLoader {
        if (static::$instance === null) {
            static::$instance = new static();
        }
        
        return static::$instance;
    }
    
    /**
     * @ignore
     */
    public function __construct() {
        /* Also add the base directory as a source
         */
        $this->addPath(__DIR__, "gabbro");
    }
    
    /**
     * Add a search path.
     *
     * You can use the namespace parameter to filter namespaces for the path
     * and allow a namespace to be located in the root of the path.
     *
     * ```
     * $l->addPath("./src")                 // Class "myspace\MyClass" will be located in ./src/myspace/MyClass.php
     * $l->addPath("./src", "myspace")      // Class "myspace\MyClass" will be located in ./src/MyClass.php
     * ```
     *
     * @param string $path                  The path to add.
     * @param string|null $namespace         Optional namespace filter.
     *
     * @return void
     */
    public function addPath(string $path, string|null $namespace = null): void {
        if (!is_dir($path)) {
            throw new Exception("The path '$path' does not exist");
            
        } else if (($realpath = realpath($path)) !== false) {
            $path = $realpath;
        }
        
        if (!isset($this->paths[$path])) {
            $this->paths[$path] = $namespace === null ? null : trim(str_replace("\\", "/", $namespace), "/");
        }
    }
    
    /**
     * Add a file extension.
     *
     * By default this loader will only search for `.php` files. 
     * If you are using other file extensions then they can 
     * be included by adding them here. 
     *
     * @param string $ext
     *
     * @return void
     */
    public function addFileExt(string $ext): void {
        $ext = trim($ext, ".");
    
        if (!in_array($ext, $this->ext)) {
            $this->ext[] = $ext;
        }
    }
    
    /**
     * {inheritdoc}
     *
     * @override {@see ClassLoader::findClass()}
     */
    function findClass(string $class): string|null {
        $localPath = ltrim(str_replace("\\", "/", $class), "/");
    
        foreach ($this->ext as $ext) {
            foreach ($this->paths as $basePath => $namespace) {
                if ($namespace === null || !str_starts_with($localPath, "{$namespace}/")) {
                    $file = sprintf("%s/%s.%s", $basePath, $localPath, $ext);

                } else {
                    $file = sprintf("%s/%s.%s", $basePath, substr($localPath, strlen($namespace)+1), $ext);
                }
                
                if (is_file($file)) {
                    return $file;
                }
            }
        }
        
        return null;
    }
    
    /**
     * Enable the auto load feature.
     *
     * Allow classes to be automatically loaded when they are
     * being requested. 
     *
     * @return void
     */
    public function enableAutoload(): void {
        if ($this->loader !== null) {
            return;
        }
        
        $this->loader = Closure::bind(
            function($class){
                $file = $this->findClass($class);

                if ($file !== null) {
                    _main_include($file);
                }
            },
            $this
        );
        
        spl_autoload_register($this->loader);
    }
    
    /**
     * Disable the auto load feature.
     *
     * @return void
     */
    public function disableAutoload(): void {
        if ($this->loader === null) {
            return;
        }
        
        spl_autoload_unregister($this->loader);
        $this->loader = null;
    }
}

