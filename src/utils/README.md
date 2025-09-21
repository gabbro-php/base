# Gabbro Utils Library

The **utils** package contains general-purpose helper classes used across the Gabbro project.  
They cover error handling, data conversion, text utilities, and semantic versioning.

---

## Classes

### ErrorCatcher
Safely run code while catching errors, warnings, and exceptions.  
Useful for older PHP code that may otherwise spam notices or warnings to stdout.

**Example:**
```php
use gabbro\utils\ErrorCatcher;

$catcher = new ErrorCatcher();

$result = $catcher->run(function () {
    return 42 / 0; // Warning in raw PHP
});

if ($result instanceof Throwable) {
    echo "Caught error: " . $result->getMessage();
}
```

---

### Shift
Provides safe conversion methods for mixed or nullable values (`toString`, `toNumber`, etc.),  
making static analysis happier than raw PHP casts.

**Example:**
```php
use gabbro\utils\Shift;

echo Shift::toString(123);     // "123"
var_dump(Shift::toBoolean("")); // bool(false)
```

---

### Text
Lightweight string helpers, such as UTF-8–safe length calculation and text wrapping.

**Example:**
```php
use gabbro\utils\Text;

echo Text::utf8_strlen("héllo");   // 5
echo Text::wrapText("A long sentence that should wrap.", 10, 2);
```

---

### Version & Constraint
Implements a light form of **Semantic Versioning 2.0**, with constraint handling.

**Example:**
```php
use gabbro\utils\Version;
use gabbro\utils\Version\Constraint;

$v1 = new Version("1.0.0");
$v2 = new Version("1.0.0-beta.1+20220130");

$c = new Constraint(">=1.0.0");

if ($c->matches($v1)) {
    echo "$v1 satisfies constraint\n";
}
```

---
