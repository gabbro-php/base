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

use gabbro\exception\IOException;

/*
 * Register this wrapper
 */
stream_wrapper_register("imphp", StreamWrapper::class);

/**
 *
 */
final class StreamWrapper implements PHPResource {

    /**
     * @ignore
     * @var resource $context
     */
    public /*resource*/ $context; // Updated by PHP

    /**
     * @ignore
     * @var Stream $stream
     */
    protected /*Stream*/ $stream;
    
    /**
     * Convert a Stream into a valid PHP Resource.
     *
     * @note                        This creates a PHP resource wrapper around the original Stream object.
     *                              If you close the resource then you are simply detaching the object from the resource.
     *
     * @param Stream $stream        The stream to create a resource for.
     *
     * @return resource
     */
    public static function getResource(Stream $stream) /*resource*/ {
        $meta = $stream->getMetadata();
        $context = stream_context_create(["imphp" => ["stream" => $stream]]);
        
        $res = fopen("imphp://stream", $meta->mode, false, $context);
        
        if (!is_resource($res)) {
            throw new IOException("Failed to create PHP resource");
        }
        
        return $res;
    }

    /**
     * {@inheritdoc}
     *
     * @override {@see PHPResource::stream_open}
     */
    function stream_open(string $url, string $mode, int $options, string|null &$opened_path = null): bool {
        $context = stream_context_get_options($this->context);
        
        if (!isset($context["imphp"]["stream"])) {
            return false;
        }
        
        $this->stream = $context["imphp"]["stream"];
        
        return true;
    }
    
    /**
     * {@inheritdoc}
     *
     * @override {@see PHPResource::stream_close}
     */
    function stream_close(): void {
        unset($this->stream);
    }
    
    /**
     * {@inheritdoc}
     *
     * @override {@see PHPResource::stream_write}
     */
    function stream_write(string $data): int {
        return $this->stream->write($data);
    }
    
    /**
     * {@inheritdoc}
     *
     * @override {@see PHPResource::stream_read}
     */
    function stream_read(int $length): string {
        return $this->stream->read($length);
    }
    
    /**
     * {@inheritdoc}
     *
     * @override {@see PHPResource::stream_eof}
     */
    function stream_eof(): bool {
        return $this->stream->isEOF();
    }
    
    /**
     * {@inheritdoc}
     *
     * @override {@see PHPResource::stream_tell}
     */
    function stream_tell(): int {
        $ret = $this->stream->getPosition();

        if ($ret < 0) {
            return 0;
        }

        return $ret;
    }
    
    /**
     * {@inheritdoc}
     *
     * @override {@see PHPResource::stream_seek}
     */
    function stream_seek(int $offset, int $whence = SEEK_SET): bool {
        return $this->stream->moveTo($offset, $whence);
    }
    
    /**
     * {@inheritdoc}
     *
     * @override {@see PHPResource::stream_truncate}
     */
    function stream_truncate(int $size): bool {
        return $this->stream->truncate($size);
    }
    
    /**
     * {@inheritdoc}
     *
     * @return array<string,int>
     *
     * @override {@see PHPResource::stream_stat}
     */
    function stream_stat(): array {
        $now = time();
        return [
            'dev'     => 0,
            'ino'     => 0,
            'mode'    => 0100666,   // regular file, rw-rw-rw-
            'nlink'   => 1,
            'uid'     => 0,
            'gid'     => 0,
            'rdev'    => 0,
            'size'    => $this->stream->getLength(),
            'atime'   => $now,
            'mtime'   => $now,
            'ctime'   => $now,
            'blksize' => -1,
            'blocks'  => -1,
        ];
    } 
    
    /**
     * {@inheritdoc}
     *
     * @override {@see PHPResource::stream_flush}
     */
    function stream_flush(): bool {
        return true;
    }
}

