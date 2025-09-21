# Phar Archive Builder

A CLI tool for creating self-contained [PHP Phar archives](https://www.php.net/manual/en/book.phar.php).  
It bundles source code, vendor dependencies, and optional stubs into a single `.phar` file.

---

## Features

- Recursively adds source and vendor files.
- Strips whitespace and comments from PHP files by default.
- `--debug` option to keep full source code.
- Supports **header**, **bootstrap**, **CLI stub** (`default.php`), **web stub** (`index.php`), and **meta** files.
- Generates a default bootstrap that handles CLI vs web execution.
- Optional `--rewrite` for clean URLs in web mode.

---

## Usage

```bash
compress.php [options] <Source Directory>
```

---

## Examples

### 1. Basic archive

```bash
php bin/compress.php src
```

Produces `app.phar` with contents from `src/`.

---

### 2. Custom name and output file

```bash
php bin/compress.php \
  --name=myapp.phar \
  --output=dist/myapp.phar \
  src
```

---

### 3. Adding vendor directory

```bash
php bin/compress.php \
  --vendor[] vendor \
  src
```

---

### 4. Adding stubs

```bash
php bin/compress.php \
  --stub cli.php \
  --web-stub index.php \
  src
```

- `cli.php` → added as `default.php` inside the Phar.  
- `index.php` → added as `index.php` inside the Phar.  

These will automatically be setup to be used in CLI mode and Web mode.

---

### 5. Adding header and meta files

```bash
php bin/compress.php \
  --header config/header.php \
  --meta config/app.json \
  src
```

- `header.php` → always included at runtime.  
- `app.json` → added as `meta` inside the Phar.  

---

### 6. Enable rewrite for web requests

```bash
php bin/compress.php \
  --web-stub index.php \
  --rewrite \
  src
```

Unknown paths without extensions will be routed to `index.php`.

---

## Options

| Option            | Alias | Argument   | Description                                                                 |
|-------------------|-------|------------|-----------------------------------------------------------------------------|
| `--help`          | `-h`  | —          | Show help text.                                                             |
| `--debug`         | `-d`  | —          | Keep original PHP source (no whitespace/comment stripping).                 |
| `--rewrite`       | —     | —          | Enable URL rewrite to `index.php` for paths without extensions.             |
| `--name`          | —     | `NAME`     | Set archive name (alias). Defaults to `app.phar`.                           |
| `--output`        | —     | `FILE`     | Output file path. Defaults to the value of `--name`.                        |
| `--bootstrap`     | —     | `FILE`     | Custom bootstrap stub file.                                                 |
| `--header`        | —     | `FILE`     | File always included at runtime (`header.php`).                             |
| `--meta`          | —     | `FILE`     | Arbitrary file added as `meta`.                                             |
| `--stub`          | —     | `FILE`     | CLI stub file (`default.php`).                                              |
| `--web-stub`      | —     | `FILE`     | Web stub file (`index.php`).                                                |
| `--vendor[]`      | —     | `PATH`     | Vendor directory (added to `vendor/`). Can be used multiple times.          |

---

## Stub Behavior

- **Bootstrap**: Configures Phar, includes header if present, decides CLI vs web entry point.
- **CLI mode**: Runs `default.php`.
- **Web mode**: Runs `index.php`.

---

## Requirements

- PHP with Phar extension enabled.
- `phar.readonly` must be set to `0` in `php.ini`.

---
