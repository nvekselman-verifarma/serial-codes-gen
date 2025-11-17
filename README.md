# Verifarma Serial Codes Generator

A Laravel package for generating secure, unique, and customizable serial codes. Supports multiple algorithms (random, AES-based, deterministic), flexible alphabet definitions, and deterministic reproducibility via seeds. Designed for high-volume serial generation with predictable behavior.

---

## 🚀 Features

- Simple API via the `SerialCodes` facade
- Multiple generation algorithms:
  - Random generator
  - AES-based generator
  - Fully deterministic generator
- Configurable alphabet and code length
- Deterministic mode supports:
  - Reproducible results when the same seed is used
  - Iterating over the entire domain without collisions
- Handles batch generation of millions of codes
- Thoroughly tested with Pest + Testbench
- Laravel-friendly, minimal setup required

---

## 📦 Installation

```bash
composer require verifarma/serial-codes-generator
```

Laravel will auto-discover both the service provider and the facade alias:

```json
"aliases": {
    "SerialCodes": "Verifarma\\SerialCodesGenerator\\Facades\\SerialCodes"
}
```

Start generating codes immediately:

```php
SerialCodes::generateFrom([...]);
```

---

## 🧩 Usage

```php

$codes = SerialCodes::generateFrom([
    'quantity'  => 1000,
    'length'    => 8,
    'alphabet'  => 'ABC',
    'seed'      => 12345,
    'algorithm' => 'deterministic', 
    'index' => 23,
]);
```



### 🎛 Algorithms

'algorithm' => 'random' | 'AES' | 'deterministic' 



1. **RandomGenerator (default)** 

Recommended when the alphabet-space is large (alphabet^length is high), making collisions statistically negligible.
Ideal for simple, high-throughput generation.
Its behavior is non-deterministic and extremely fast, making it suitable for large batches where reproducibility is not necessary and the combination space is sufficiently large to naturally avoid collisions.

2. **AesGenerator**

Suited for generating cryptographically strong serial numbers in deterministic environments.
Given the same seed and index, AES-ECB produces stable, reproducible outputs.
However, AES does not provide collision-free guarantees over small domains; when the alphabet-space is limited, ensuring uniqueness becomes computationally expensive.
Best used when security properties matter and full domain coverage is not required.

3. **DeterministicGenerator**

A purely arithmetic, pseudo-random mapping with deterministic output, similar to AES, for a given (alphabet, length, seed) guarantees collision-free enumeration for every index.
This makes it ideal for workflows that require full coverage of all possible codes, safe iteration across batches, or deterministic reproducibility without collisions. This generator is not cryptographically strong.


## 🧱 API details

### `SerialCodes::generateFrom(array $params)`

Main entry point. Accepts:

| Parameter  | Type   | Default | Description                          |
| ---------- | ------ | ------- | ------------------------------------ |
| `quantity` | `int`  | —       | Number of serials to generate        |
| `length`   | `int`  | `null`  | Code length (defaults to config)     |
| `alphabet` | `string` | `null`  | Allowed characters                   |
| `algorithm`| `string` | `null`  | Algorithm name                       |
| `seed`     | `int`  | `null`  | Deterministic seed                   |
| `index`     | `int`  | `0`  | Amount of current serial codes in database                |

---


## 🧪 Testing & tooling

- **Tests:** `composer test`
- **Static analysis:** `composer analyse`
- **Formatting:** `composer format`

The stack uses Pest, Testbench, PHPStan (Larastan), and Laravel Pint.

---

## 📄 License

Released under the MIT License. See `LICENSE.md`.
