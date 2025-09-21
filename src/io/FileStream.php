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

namespace gabbro\io;

use gabbro\utils\ErrorCatcher;
use gabbro\exception\IOException;
use gabbro\feature\Wrapper;

/**
 * A lazy file stream implementation.
 *
 * This class will only open a file the first time it is being used. 
 * It can be used whenever you need a stream but it's not certain
 * whether the file will actually be used. 
 *
 * The class will not use any resources to verify the file or path. 
 * Any error that may come, will only be revealed when the file is first accessed. 
 *
 * @implements Wrapper<Stream>
 */
class FileStream extends StreamDecorator implements Wrapper {

    /**
     * @ignore
     *
     * @var string
     */
    protected string $file;
    
    /**
     * @ignore
     *
     * @var string
     */
    protected string $mode;
    
    /**
     * @ignore
     *
     * @var Stream|null
     */
    protected Stream|null $stream = null;

    /**
     * Create a new file stream.
     *
     * @param string $file      Path to a file.
     * @param string $mode      File access mode.
     * 
     * @return void
     */
    public function __construct(string $file = "php://temp", string $mode = "r+") {
        $this->file = $file;
        $this->mode = $mode;
    }
    
    /**
     * {inheritdoc}
     *
     * @override {@see Wrapper::unwrap()}
     */
    public function unwrap(): object {
        return $this->getStream();
    }
    
    /**
     * {inheritdoc}
     *
     * @override {@see StreamDecorator::getStream()}
     */
    public function getStream(): Stream {
        if ($this->stream !== null) {
            return $this->stream;
        }
        
        $file = $this->file;
        $mode = $this->mode;
        $catcher = new ErrorCatcher();
        
        /*
         * fopen may trigger a warning if it fails.
         * Capture it and turn it into a proper exception.
         */
        $stream = $catcher->run(function() use ($file, $mode): Stream|null {
            $res = fopen($file, $mode);
            
            if ($res === false) {
                return null;
            }
        
            return new RawStream($res);
        });
        
        if ($stream === null) {
            $e = $catcher->getException();
        
            if ($e !== null) {
                throw new IOException($e->getMessage(), $e->getCode(), $e);
            }
            
            throw new IOException("Filed to open the file {$this->file}");
        }
        
        $this->stream = $stream;
        
        return $stream;
    }
}

