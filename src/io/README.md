# Gabbro I/O Library

A modern abstraction layer over PHP’s stream system.  
It provides a unified `Stream` interface, concrete stream implementations (file, variable, null, etc.), decorators, and tools for process I/O (`Shell`).  

The goal is to give **consistent, object-oriented stream handling** that works across files, memory buffers, CLI, and system processes — without juggling raw PHP resources.

---

## Features

- **Unified `Stream` interface** with read, write, seek, metadata, and readiness (`wait()`).  
- **Core stream classes** for files, variables, and null streams.  
- **Extended `IOStream`** for stdin/stdout/stderr with TTY detection and color support.  
- **Decorators and wrappers** for extending stream behavior.  
- **Shell integration** with `Shell::exec()` and `Shell::start()` for process I/O.  

---

## Library Structure

### Interfaces
- **`Stream`** – Core contract for all streams (read, write, seek, wait, metadata).  
- **`PHPResource`** – Wrapper contract for exposing native PHP resources safely.  

### Core Stream Classes
- **`BaseStream`** – Common base implementation used by concrete streams.  
- **`RawStream`** – Direct wrapper around a PHP resource; minimal abstraction.  
- **`FileStream`** – File-backed stream.  
- **`VariableStream`** – Stream backed by a PHP string (memory buffer).  
- **`NullStream`** – Discards all writes and reads nothing (like `/dev/null`).  

### Extended / Utility Classes
- **`IOStream`** – Special stream for stdin, stdout, and stderr with TTY and color support.  
- **`StreamDecorator`** – Wraps another stream to extend behavior.  
- **`StreamWrapper`** – Integration point with PHP’s `stream_wrapper_register`.  
- **`Stream\Metadata`** – Access stream metadata (mode, URI, etc.).  
- **`Stream\Ready`** – Result object for readiness checks from `wait()`.  

### Shell Integration
- **`Shell`** – Start or execute external processes with stream I/O.  
- **`Shell\Result`** – Captures exit code, stdout, and stderr for one-shot commands.  

---

## Examples

### 1. RawStream Basics

`RawStream` is the lowest-level wrapper: it takes a PHP resource and exposes it through the unified `Stream` interface.

```php
use gabbro\io\RawStream;

// Open a file resource
$handle = fopen("example.txt", "w+");

// Wrap it in a RawStream
$stream = new RawStream($handle);

// Write some text
$stream->write("Hello World\n");

// Move pointer to beginning
$stream->moveToStart();

// Read the contents
echo $stream->readAll(); // "Hello World\n"

// Clean up
$stream->close();
```

---

### 2. Waiting on a Stream

Every stream implements `wait()`, which checks readiness before reading or writing (avoids blocking calls).

```php
use gabbro\io\RawStream;

$socket = stream_socket_client("tcp://example.com:80");
$stream = new RawStream($socket);

// Wait up to 0.5 seconds for readiness
$ready = $stream->wait(0.5);

if ($ready->isWritable()) {
    $stream->write("GET / HTTP/1.0\r\nHost: example.com\r\n\r\n");
}

if ($ready->isReadable()) {
    echo $stream->read(1024);
}
```

---

### 3. Running Commands with Shell

Use `Shell::exec()` for one-shot commands, capturing both stdout and stderr.

```php
use gabbro\io\Shell;
use gabbro\io\IOStream;

$result = Shell::exec("ls -l /");

if ($result->isSuccessful()) {
    echo $result->getStream(IOStream::STDOUT)->readAll();
} else {
    echo "Error:\n";
    echo $result->getStream(IOStream::STDERR)->readAll();
}
```

---

### 4. Interactive Shell Processes

Use `Shell::start()` when you need interactive I/O with a process.

```php
use gabbro\io\Shell;
use gabbro\io\IOStream;

$shell = Shell::start("cat"); // echoes back stdin

$stdin  = $shell->getStream(IOStream::STDIN);
$stdout = $shell->getStream(IOStream::STDOUT);

$stdin->println("Line One");
$stdin->println("Line Two");

// Read back from process
echo $stdout->readLine(); // "Line One"
echo $stdout->readLine(); // "Line Two"

$shell->stop();
```

---
