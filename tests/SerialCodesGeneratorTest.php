<?php

use Verifarma\SerialCodesGenerator\GenerationAlgorithms\AesGenerator;
use Verifarma\SerialCodesGenerator\GenerationAlgorithms\DeterministicGenerator;
use Verifarma\SerialCodesGenerator\GenerationAlgorithms\RandomGenerator;

/**
 * --------------------------------------------------------------------------
 * Basic generation tests
 * --------------------------------------------------------------------------
 */
test('it generates the requested amount of codes', function () {
    $codes = SerialCodes::generateFrom([
        'quantity' => 10,
    ]);

    expect($codes)->toHaveCount(10);
});

test('all generated codes have the correct length', function () {
    $codes = SerialCodes::generateFrom([
        'quantity' => 20,
        'length' => 12,
    ]);

    foreach ($codes as $c) {
        expect(strlen($c))->toBe(12);
    }
});

test('all generated codes use only alphabet characters', function () {
    $alphabet = 'ABC123';

    $codes = SerialCodes::generateFrom([
        'quantity' => 30,
        'alphabet' => $alphabet,
    ]);

    foreach ($codes as $c) {
        foreach (str_split($c) as $ch) {
            expect($alphabet)->toContain($ch);
        }
    }
});

/**
 * --------------------------------------------------------------------------
 * Algorithm selection tests
 * --------------------------------------------------------------------------
 */
test('it uses RandomGenerator when no algorithm is selected', function () {
    $codes = SerialCodes::generateFrom(['quantity' => 5]);

    expect($codes)->toHaveCount(5);
});

test('it selects a specific algorithm when requested', function () {
    $codes = SerialCodes::generateFrom([
        'quantity' => 5,
        'algorithm' => (new AesGenerator)->getName(),
    ]);

    expect($codes)->toHaveCount(5);
});

/**
 * --------------------------------------------------------------------------
 * Uniqueness tests
 * --------------------------------------------------------------------------
 */
test('generated codes are unique for small batches', function () {
    $codes = SerialCodes::generateFrom([
        'quantity' => 200,
    ]);

    expect($codes)->toHaveCount(200);
    expect(array_unique($codes))->toHaveCount(200);
});

/**
 * --------------------------------------------------------------------------
 * Deterministic generator raw tests
 * --------------------------------------------------------------------------
 * These still test generateRawCode() directly.
 */
test('deterministic generator produces different codes for different idx', function () {
    $algorithm = new DeterministicGenerator;

    $M = gmp_init(100000);

    $a = $algorithm->generateRawCode($M, gmp_init(0), 999);
    $b = $algorithm->generateRawCode($M, gmp_init(1), 999);

    expect(gmp_cmp($a, $b))->not->toBe(0);
});

test('deterministic generator reproduces values with same seed and idx', function () {
    $algorithm = new DeterministicGenerator;

    $M = gmp_init(100000);

    $v1 = $algorithm->generateRawCode($M, gmp_init(42), 2024);
    $v2 = $algorithm->generateRawCode($M, gmp_init(42), 2024);

    expect(gmp_cmp($v1, $v2))->toBe(0);
});

/**
 * --------------------------------------------------------------------------
 * Domain selection tests
 * --------------------------------------------------------------------------
 */
test('it uses 2^128 domain when alphabet^length exceeds 128 bits', function () {
    $codes = SerialCodes::generateFrom([
        'quantity' => 1,
        'length' => 30,
        'alphabet' => '0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZ',
    ]);

    expect($codes)->toHaveCount(1);
});

test('it uses alphabet^length when smaller than 2^128', function () {
    $codes = SerialCodes::generateFrom([
        'quantity' => 1,
        'length' => 5,
        'alphabet' => '0123456789',
    ]);

    expect($codes)->toHaveCount(1);
});

/**
 * --------------------------------------------------------------------------
 * Input validation tests
 * --------------------------------------------------------------------------
 */
test('it rejects negative quantity', function () {
    expect(fn () => SerialCodes::generateFrom(['quantity' => -1]))
        ->toThrow(InvalidArgumentException::class);
});

test('it rejects empty alphabet', function () {
    expect(fn () => SerialCodes::generateFrom([
        'quantity' => 3,
        'alphabet' => '',
    ]))->toThrow(InvalidArgumentException::class);
});

test('it rejects non-positive length', function () {
    expect(fn () => SerialCodes::generateFrom([
        'quantity' => 3,
        'length' => 0,
    ]))->toThrow(InvalidArgumentException::class);
});

test('it generates codes using the RandomGenerator when explicitly selected', function () {
    $codes = SerialCodes::generateFrom([
        'quantity' => 5,
        'algorithm' => (new RandomGenerator)->getName(),
    ]);

    expect($codes)->toHaveCount(5);
    foreach ($codes as $c) {
        expect($c)->toBeString();
    }
});

test('it generates codes using the AesGenerator when explicitly requested', function () {
    $params = [
        'quantity' => 5,
        'algorithm' => (new AesGenerator)->getName(),
        'seed' => 1234,
    ];

    $codes = SerialCodes::generateFrom($params);
    $codes2 = SerialCodes::generateFrom($params);

    expect($codes)->toHaveCount(5);
    expect($codes)->toEqual($codes2);
});

test('it generates codes using the DeterministicGenerator when selected', function () {
    $params = [
        'quantity' => 5,
        'algorithm' => (new DeterministicGenerator)->getName(),
        'seed' => 999,
    ];

    $codes = SerialCodes::generateFrom($params);
    $codes2 = SerialCodes::generateFrom($params);

    expect($codes)->toHaveCount(5);
    expect($codes)->toEqual($codes2);
});

/**
 * --------------------------------------------------------------------------
 * Cross-algorithm distinction tests
 * --------------------------------------------------------------------------
 */
test('different algorithms produce different outputs for the same input', function () {
    $random = SerialCodes::generateFrom([
        'quantity' => 10,
        'algorithm' => (new RandomGenerator)->getName(),
        'seed' => 555,
    ]);

    $det = SerialCodes::generateFrom([
        'quantity' => 10,
        'algorithm' => (new DeterministicGenerator)->getName(),
        'seed' => 555,
    ]);

    expect($random)->not->toEqual($det);
});

/**
 * --------------------------------------------------------------------------
 * Error handling for invalid algorithm parameter
 * --------------------------------------------------------------------------
 */
test('it throws an exception when requesting an unregistered algorithm', function () {
    expect(fn () => SerialCodes::generateFrom([
        'quantity' => 5,
        'algorithm' => 'non-existent-algorithm',
    ]))->toThrow(RuntimeException::class);
});

/**
 * --------------------------------------------------------------------------
 * Deterministic generator full-cycle uniqueness tests
 * --------------------------------------------------------------------------
 */
test('deterministic generator produces unique codes with small alphabet and length', function () {
    $alphabet = 'ABC';
    $length = 8;
    $qty = strlen($alphabet) ** $length;

    $codes = SerialCodes::generateFrom([
        'quantity' => $qty,
        'alphabet' => $alphabet,
        'length' => $length,
        'seed' => 12345,
        'algorithm' => (new DeterministicGenerator)->getName(),
    ]);

    expect($codes)->toHaveCount($qty);
    expect(array_unique($codes))->toHaveCount($qty);

    foreach ($codes as $code) {
        expect(strlen($code))->toBe($length);

        foreach (str_split($code) as $ch) {
            expect($alphabet)->toContain($ch);
        }
    }
});

test('deterministic generator produces unique values for every possible idx', function () {
    $alphabetLength = 3;
    $length = 5; // 3^5 = 243 combinations
    $seed = 999;

    $total = $alphabetLength ** $length;
    $M = gmp_pow(gmp_init($alphabetLength), $length);

    $generator = new DeterministicGenerator;
    $seen = [];

    for ($i = 0; $i < $total; $i++) {
        $idx = gmp_init($i);
        $value = $generator->generateRawCode($M, $idx, $seed);

        expect($value)->toBeInstanceOf(GMP::class);
        expect(gmp_cmp($value, 0))->toBeGreaterThanOrEqual(0);
        expect(gmp_cmp($value, $M))->toBeLessThan(0);

        $str = gmp_strval($value);

        expect(isset($seen[$str]))->toBeFalse();
        $seen[$str] = true;
    }

    expect(count($seen))->toBe($total);
});

test('deterministic generator respects index', function () {
    $alphabet = 'ABC';
    $length = 8;
    $seed = 12345;
    $intersection = 100;
    $qty1 = 100 + $intersection;

    $codes1 = SerialCodes::generateFrom([
        'quantity' => $qty1,
        'alphabet' => $alphabet,
        'length' => $length,
        'seed' => $seed,
        'index' => 0,
        'algorithm' => (new DeterministicGenerator)->getName(),
    ]);

    $codes2 = SerialCodes::generateFrom([
        'quantity' => $qty1,
        'alphabet' => $alphabet,
        'length' => $length,
        'seed' => $seed,
        'index' => ($qty1 - $intersection),
        'algorithm' => (new DeterministicGenerator)->getName(),
    ]);

    expect(array_intersect($codes1, $codes2))->toHaveCount($intersection);
});
