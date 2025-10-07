# Gabbro Collection Library

A strongly-typed collection library for PHP.  
It provides consistent, immutable/mutable data structures and a powerful CLI argument parser (`ArgV`).  

The goal is to bring **Java-style collections** into PHP with a modern, object-oriented design.

---

## Features

- **Core collections**: lists, stacks, queues, sets, and maps.  
- **Immutable and mutable variants** of arrays, key tables, and structured arrays.  
- **Pairs** and **tables** for associative data handling.  
- Iterators, type safety, and utility interfaces for consistency.  

---

## Library Structure

### Core Collections
- **`ArrayList`** – Ordered, resizable list.  
- **`Fifo`**, **`Lifo`** – Queue and stack implementations of StackArray base.  
- **`Map`** – Associative key/value mapping.  
- **`Pair`** – Simple key/value pair.  
- **`HashSet`** – Set collection, ensuring uniqueness.
- **`KeyTable`** – Optimized storage for keys with simple set/unset state.

### Immutable Interfaces
- **`ImmutableArray`** – Fixed array structure.  
- **`ImmutableKeyTable`** – Immutable key table.  
- **`ImmutableMappedArray`** – Immutable associative array.  
- **`ImmutableStructuredArray`** – Immutable structured array.

### Mutable Interfaces
- **`MutableArray`** – Mutable array.  
- **`MutableKeyTable`** – Mutable key table.  
- **`MutableMappedArray`** – Mutable associative array.  
- **`MutableStructuredArray`** – Mutable structured array.

---

## Examples

### 2. Using Lists and Stacks

```php
use gabbro\collection\ArrayList;
use gabbro\collection\Lifo;

$list = new ArrayList([1, 2, 3]);
$list->add(4);

foreach ($list as $item) {
    echo $item . PHP_EOL;
}
// Output: 1, 2, 3, 4

$stack = new Lifo();
$stack->push("first");
$stack->push("second");

echo $stack->pop(); // "second"
```

---

### 3. Using Maps and Sets

```php
use gabbro\collection\Map;
use gabbro\collection\HashSet;

$map = new Map();
$map->put("username", "daniel");
$map->put("email", "daniel@example.com");

echo $map->get("username"); // "daniel"

$set = new HashSet();
$set->add("apple");
$set->add("banana");
$set->add("apple"); // duplicates ignored

foreach ($set as $item) {
    echo $item . PHP_EOL;
}
// Output: apple, banana
```

---
