<?php

declare(strict_types=1);

use Verifarma\SerialCodesGenerator\DTO\SerialGenerationRequest;
use Verifarma\SerialCodesGenerator\Services\SerialCodesGeneratorService;

$defaultAlphabet = '0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZ';
$defaultLength = 10;

function makeSerialService(): SerialCodesGeneratorService
{
    // Podés ajustar esto si querés inyectar otras cosas en el futuro
    return new SerialCodesGeneratorService;
}

it('generates the requested quantity of serials with default config', function () use ($defaultAlphabet, $defaultLength) {
    $service = makeSerialService();

    $request = new SerialGenerationRequest(
        quantity: 100,
    );

    $serials = $service->generate($request);

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
    $customLength = 5;
    $quantity = 50;

    $request = new SerialGenerationRequest(
        quantity: $quantity,
        length: $customLength,
        alphabet: $customAlphabet,
    );

    $serials = $service->generate($request);

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

it('returns an empty list when quantity is zero', function () {
    $service = makeSerialService();

    $request = new SerialGenerationRequest(
        quantity: 0,
    );

    $serials = $service->generate($request);

    expect($serials)->toBeArray()
        ->and($serials)->toBeEmpty();
});

it('does not generate identical serials for a reasonable sample', function () {
    $service = makeSerialService();

    $request = new SerialGenerationRequest(
        quantity: 200,
    );

    $serials = $service->generate($request);

    // Sanity check simple: no deberían ser todos iguales
    $uniqueCount = count(array_unique($serials));

    expect($uniqueCount)->toBeGreaterThan(1);
});
