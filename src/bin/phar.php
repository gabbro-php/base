#!/usr/bin/env php
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
 
require __DIR__ . "/../SimpleLoader.php";

/**
 * Command-line tool for inspecting PHAR archives.
 *
 * This script allows you to view the structure and contents of PHP
 * archive (.phar) files directly from the command line.
 * It can list files and directories within an archive or print the
 * contents of specific files, including the archive's bootstrap stub.
 *
 * Typical uses include verifying archive integrity, checking included
 * resources, or examining the stub of a packaged application without
 * extracting it.
 *
 * Supported actions:
 *  - ls [DIR] ARCHIVE
 *      Lists the contents of a directory inside the PHAR. If no
 *      directory is given, lists the archive root.
 *
 *  - cat [FILE] ARCHIVE
 *      Prints the contents of a file inside the PHAR. If no file is
 *      given, outputs the bootstrap stub section before
 *      __HALT_COMPILER.
 */

use gabbro\SimpleLoader;
use gabbro\collection\ArrayList;
use gabbro\collection\ArgV;
use gabbro\collection\ArgV\ArrayOption;
use gabbro\collection\ArgV\Option;
use gabbro\collection\ArgV\Flag;
use gabbro\collection\ArgV\Operand;
use gabbro\io\IOStream;
use gabbro\io\RawStream;
 
$loader = SimpleLoader::getInstance();
$loader->enableAutoload();

$stdout = IOStream::getInstance(IOStream::STDOUT);

/**
 * @ignore
 *
 * @param string $msg
 * @return never
 */
function doExit(string $msg): void {
    $stderr = IOStream::getInstance(IOStream::STDERR);
    $stderr->println($msg);
    
    exit(1);
}

/* ==================================================
 * Create and extract command line options
 */
 
$arg = new stdClass();
$arg->Parser = new ArgV($argv);

$arg->Parser->parseAll(
    $arg->help = new Flag("--help", "-h"),
    $arg->action = new Operand(0),
    $arg->target = new Operand(1),
    $arg->archive = new Operand(2)
);

if ($arg->help->isSet()) {
    $stdout->println("Usage %s Action [TARGET] ARCHIVE\n", $arg->Parser->cmd);
    
    $arg->Parser->addTableHeadline("Actions:");
    $arg->Parser->addTableRow("ls [DIR]", "List a specified directory or the archive.");
    $arg->Parser->addTableRow("cat [FILE]", "Print the content of a specified file or the archive header.");
    
    $stdout->println($arg->Parser->buildTable());
    
    exit(0);
    
} else if (!$arg->Parser->isConsumed() || !$arg->action->isSet()) {
    doExit("Invalid arguments. Use -h or --help to see all options.");
}

if (!$arg->archive->isSet() && $arg->target->isSet()) {
    $tmp = $arg->archive;
    $arg->archive = $arg->target;
    $arg->target = $tmp;
}

$archive = $arg->archive->getValue("__missing__");
$target = $arg->target->getValue();

if (!empty($target)) {
    if (str_starts_with($target, "./") || $target == ".") {
        $target = substr($target, 1);
    }

    $target = trim($target, "/");
}

if (!is_file($archive)) {
    doExit("The archive '$archive' does not exist.");
}

switch ($arg->action->getValue()) {
    case "ls": 
        if (!empty($target)) {
            $archive = "{$archive}/{$target}";
            
            if (!is_dir("phar://{$archive}")) {
                doExit("The directory '{$target}' does not exist in the archive.");
            }
        }
    
        $dir = new DirectoryIterator("phar://{$archive}");
        
        $stdout->println("Listing of %s\n------\n", $target ?: "(phar://". basename($archive) .")");
        
        foreach ($dir as $file) {
            if ($file->isDot()) continue;
            $stdout->println(
                "%s%s", 
                $file->getFilename(),
                $file->isDir() ? "/" : ""
            );
        }
        
        break;
    
    
    case "cat": 
        if (!empty($target)) {
            if (is_file("phar://{$archive}/{$target}")) {
                $stdout->println(
                    "Dump: %s\n------\n%s",
                    $target,
                    file_get_contents("phar://{$archive}/{$target}")
                );

            } else {
                doExit("The file '$target' does not exist in the archive.");
            }
            
        } else {
            $stdout->println("Dump: (bootstrap)\n------\n");
            
            $buffer = "";
            $marker = "__HALT_COMPILER";
            $stream = new RawStream(fopen($archive, "rb") ?: null);
            
            while (!$stream->isEOF()) {
                $chunk = $stream->read(8192);
                
                if ($chunk == "") {
                    break;
                    
                } else if (($pos = strpos($chunk, $marker)) !== false) {
                    $buffer .= substr($chunk, 0, $pos);
                    break;
                }
                
                $buffer .= $chunk;
            }
            
            $stream->close();
            $stdout->write($buffer);
        }
        
        break;
    
    default:
        doExit("Invalid arguments. Use -h or --help to see all options.");
}

