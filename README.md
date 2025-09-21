![logo/logo-banner.png](https://github.com/gabbro-php/base/blob/gh-main/logo/Logo-banner.png)

![PHP Version](https://img.shields.io/badge/PHP-8-blue?logo=php&logoColor=white)

**Gabbro** is a lightweight PHP utility library that provides a solid foundation of tools for building applications. 
It focuses on core abstractions and practical helpers — the kind of building blocks you reach for again and again — wrapped in a consistent, modern API. 

---

## ✨ Features

- **Strong Type Safety**  
  Designed to work seamlessly with PHPStan / Psalm for **compile-time safety**

- **Collections**  
  Immutable and mutable collection types for working with data:
  - `KeyTable`, `Map`, `ArrayList`, `HashSet`  
  - Immutable interfaces for safe sharing between components  

- **Streams**  
  Stream abstractions for handling input/output, iteration, and buffering.
  
- **Shell**  
  Quickly execute shell commands and work with interactive shells. 
  Built-in support for stream selection and makes use of the Stream library. 

- **Class loading**  
  Utility class loaders and autoload helpers that follow modern PSR standards.

- **CLI argument parsing**  
  `ArgV` provides a clean way to parse command-line input into **flags, options, and operands**, with support for GNU style options.

- **Flat-file configuration parsing**  
  `FlatFileScanner` reads simple, Unix-style config files with comments, whitespace, escaping, and quoted values.

- **Core utilities**  
  Handy helpers for type conversion (`toBoolean`, `toNumber`, `toString`), version handling, validation, and more.

---

## 📌 Philosophy

- **Small, composable classes** → each tool does one thing well.  
- **Strong static typing** → every class and method is designed with generics and docblocks for static analyzers.  
- **Consistent API surface** → all streams, collections and parsers behave the same way.  
- **Foundation, not framework** → no hidden magic, just reusable bricks.  

---

## 🚀 Example

```php
$arg = new stdClass();
$arg->Parser = new ArgV($argv);

$arg->Parser->parseAll(
    $arg->help = Flag::withDescription(
            "Show this help section.", 
            "--help", "-h"
    ),
      
    $arg->debug = Flag::withDescription(
            "Create a debug output.", 
            "--debug"
    ),
      
    $arg->name = Option::withDescription(
            "NAME", 
            "Set the output name.", 
            "--name"
    ),
      
    $arg->dirs = ArrayOption::withDescription(
            "PATH", 
            "Add one or more directories.", 
            "--dir"
    )
    
    $arg->dirs = Operand::withDescription(
            "Action", 
            "Perform action such as Action1 and Action2.", 
            0
    )
);

if ($arg->help->isSet() || !$arg->Parser->isConsumed()) {
    echo $arg->Parser->buildHelp("Usage: ". $arg->Parser->cmd ." <Options> Action"); 
    exit;
}
```

Output:

```
Usage: MyScript.php <Options> Action

Options:
  --help, -h        Show this help section.
  --debug           Create a debug output.
  --name NAME       Set the output name.
  --dir[] PATH      Add one or more directories.
  
Operands:
  Action            Perform action such as Action1 and Action2.
```

---

## 📦 Installation

Install via [Composer](https://getcomposer.org/):

```bash
composer require gabbro-php/base
```

