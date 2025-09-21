# Gabbro Feature Interfaces

This package defines a set of **lightweight feature interfaces**.  
They exist to declare *capabilities* for classes — either aligning with PHP’s built-in behaviors (like `Traversable`, `ArrayAccess`, `__serialize`) or extending them with explicit contracts (`Closeable`, `Wrapper`, etc.).  

These interfaces do not implement logic themselves; they are used across the Gabbro libraries (Collections, I/O, etc.) to provide **consistent, type-safe contracts**.

---

## Interfaces

### Cloneable
```php
interface Cloneable {
    function clone();
    function __clone();
}
```

> An interface that ensures that an object is made to be cloned properly.  
>  
> PHP allows any object to be cloned using `clone $object`, however the object itself may not be suitable if it does not internally handle cloning issues.  
>  
> This interface defines objects that are built to deal with being cloned.

**Example:**
```php
class Config implements Cloneable {
    public function clone() { return clone $this; }
    public function __clone() {
        // Perform deep copy of internal data
    }
}
```

---

### Closeable
```php
interface Closeable {
    function close();
    function isClosed();
}
```

> Feature that describes a closeable object.  
> Used for streams, handles, and other resources that require explicit cleanup.

**Example:**
```php
class FileHandle implements Closeable {
    private $fp;
    private bool $closed = false;

    public function __construct(string $path) {
        $this->fp = fopen($path, "r");
    }

    public function close(): void {
        if (!$this->closed) {
            fclose($this->fp);
            $this->closed = true;
        }
    }

    public function isClosed(): bool {
        return $this->closed;
    }
}
```

---

### Enumerable
```php
/**
 * @template K
 * @template V
 * @extends Traversable<K,V>
 */
interface Enumerable extends Traversable {}
```

> Feature that describes objects compatible with PHP’s `Traversable`.  
> Classes implementing this can be iterated with `foreach`.

**Example:**
```php
class Numbers implements Enumerable, IteratorAggregate {
    public function getIterator(): Traversable {
        yield 1;
        yield 2;
        yield 3;
    }
}
```

---

### Indexable
```php
/**
 * @template K
 * @template V
 * @extends ArrayAccess<K,V>
 */
interface Indexable extends ArrayAccess {}
```

> Feature that describes objects with PHP’s `ArrayAccess`.  
> Classes implementing this can be accessed like arrays.

**Example:**
```php
class Vector implements Indexable {
    private array $data = [];

    public function offsetExists($offset): bool { return isset($this->data[$offset]); }
    public function offsetGet($offset): mixed { return $this->data[$offset]; }
    public function offsetSet($offset, $value): void { $this->data[$offset] = $value; }
    public function offsetUnset($offset): void { unset($this->data[$offset]); }
}
```

---

### Serializable
```php
interface Serializable {
    function __serialize();
    function __unserialize(array $data);
    function __debugInfo();
}
```

> Defines an interface around PHP’s serializable features.  
>  
> PHP deprecated its old `Serializable` interface in favor of these magic methods.  
> This interface formalizes them as a proper contract.

**Example:**
```php
class User implements Serializable {
    private string $name;

    public function __serialize(): array {
        return ["name" => $this->name];
    }

    public function __unserialize(array $data): void {
        $this->name = $data["name"] ?? "";
    }

    public function __debugInfo(): ?array {
        return ["name" => $this->name];
    }
}
```

---

### Stringable
```php
interface Stringable {
    function toString();
}
```

> Feature that describes objects that can be cast to strings.  
> Unlike PHP’s `__toString()`, this makes conversion explicit via `toString()`.

**Example:**
```php
class Money implements Stringable {
    private int $cents = 1000;
    public function toString(): string {
        return sprintf("€%.2f", $this->cents / 100);
    }
}
```

---

### Wrapper
```php
/**
 * @template T of object
 */
interface Wrapper {
    function unwrap();
}
```

> Defines a generic wrapper object.  
>  
> This is a very generic interface that simply declares a class that acts as a wrapper on top of another object.  
> It should always be used in conjunction with another interface to better specify the type.

**Example:**
```php
class LoggingStream implements Wrapper {
    private Stream $inner;

    public function __construct(Stream $inner) {
        $this->inner = $inner;
    }

    public function unwrap(): Stream {
        return $this->inner;
    }
}
```

---
