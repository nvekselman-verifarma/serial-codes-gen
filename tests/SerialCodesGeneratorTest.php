<?php

declare(strict_types=1);

use Verifarma\SerialCodesGenerator\DTO\SerialGenerationRequest;
use Verifarma\SerialCodesGenerator\Services\SerialCodesGeneratorService;

$defaultAlphabet = '0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZ';
$defaultLength   = 10;

function makeSerialService(): SerialCodesGeneratorService {
    // Podés ajustar esto si querés inyectar otras cosas en el futuro
    return new SerialCodesGeneratorService(
        defaultAlphabet: '0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZ',
        defaultLength: 10,
    );
}

it('generates the requested quantity of serials with default config', function () use ($defaultAlphabet, $defaultLength) {
    $service = makeSerialService();

    $request = new SerialGenerationRequest(
        quantity: 100,
    );

    $result  = $service->generate($request);
    $serials = $result->serials;

    // Cantidad correcta
    expect($serials)->toHaveCount(100);

    // Todos con el largo por defecto
    foreach ($serials as $serial) {
        expect(strlen($serial))->toBe($defaultLength);
    }

    // Todos usan sólo caracteres del alfabeto por defecto
    $alphabetChars = str_split($defaultAlphabet);

    foreach ($serials as $serial) {
        foreach (str_split($serial) as $char) {
            expect($alphabetChars)->toContain($char);
        }
    }

    // Todos únicos dentro del lote
    expect(array_unique($serials))->toHaveCount(100);
});

it('allows overriding length and alphabet from the request', function () {
    $service = makeSerialService();

    $customAlphabet = 'ABC';
    $customLength   = 5;
    $quantity       = 50;

    $request = new SerialGenerationRequest(
        quantity: $quantity,
        length: $customLength,
        alphabet: $customAlphabet,
    );

    $result  = $service->generate($request);
    $serials = $result->serials;

    // Cantidad correcta
    expect($serials)->toHaveCount($quantity);

    // Todos con el largo custom
    foreach ($serials as $serial) {
        expect(strlen($serial))->toBe($customLength);
    }

    // Todos usan sólo caracteres del alfabeto custom
    $alphabetChars = str_split($customAlphabet);

    foreach ($serials as $serial) {
        foreach (str_split($serial) as $char) {
            expect($alphabetChars)->toContain($char);
        }
    }
});

it('applies prefix and suffix correctly', function () {
    $service = makeSerialService();

    $baseLength = 8;
    $prefix     = 'PRE-';
    $suffix     = '-SFX';

    $request = new SerialGenerationRequest(
        quantity: 10,
        length: $baseLength,
        prefix: $prefix,
        suffix: $suffix,
    );

    $result  = $service->generate($request);
    $serials = $result->serials;

    foreach ($serials as $serial) {
        // Empieza con el prefix
        expect($serial)->toStartWith($prefix);

        // Termina con el suffix
        expect($serial)->toEndWith($suffix);

        // Largo = prefix + baseLength + suffix
        $expectedLength = strlen($prefix) + $baseLength + strlen($suffix);
        expect(strlen($serial))->toBe($expectedLength);
    }
});

it('returns an empty list when quantity is zero', function () {
    $service = makeSerialService();

    $request = new SerialGenerationRequest(
        quantity: 0,
    );

    $result  = $service->generate($request);
    $serials = $result->serials;

    expect($serials)->toBeArray()
        ->and($serials)->toBeEmpty();
});

it('throws an exception when alphabet becomes empty', function () {
    $service = new SerialCodesGeneratorService(
        defaultAlphabet: 'X', // algo cualquiera
        defaultLength: 5,
    );

    // Pasamos alphabet vacío explícitamente en el request.
    // Como no es null, pisa al default y llega vacío a generateCode().
    $request = new SerialGenerationRequest(
        quantity: 1,
        length: 5,
        alphabet: '',
    );

    $service->generate($request);
})->throws(RuntimeException::class, 'Alphabet for serial code generation cannot be empty.');

it('does not generate identical serials for a reasonable sample', function () {
    $service = makeSerialService();

    $request = new SerialGenerationRequest(
        quantity: 200,
    );

    $result  = $service->generate($request);
    $serials = $result->serials;

    // Sanity check simple: no deberían ser todos iguales
    $uniqueCount = count(array_unique($serials));

    expect($uniqueCount)->toBeGreaterThan(1);
});
