<?php

use Verifarma\SerialCodesGenerator\DTO\SerialGenerationRequest;

it('generates codes correctly using generateFrom', function () {

    $params = [
        'quantity' => 10,
        'alphabet' => 'ABC',
        'length' => 4,
        'seed' => 12345,
        'algorithm' => 'deterministic',
        'options' => ['foo' => 'bar'],
    ];

    // Ejecuta mediante el Facade
    $codes = SerialCodes::generateFrom($params);

    // Son 10 códigos
    expect($codes)->toHaveCount(10);

    // Todos únicos
    expect(array_unique($codes))->toHaveCount(10);

    // Todos válidos con el alfabeto
    foreach ($codes as $code) {
        expect(strlen($code))->toBe(4);

        foreach (str_split($code) as $ch) {
            expect($params['alphabet'])->toContain($ch);
        }
    }
});

it('builds the DTO correctly using fromArray', function () {

    $params = [
        'quantity' => 100,
        'alphabet' => 'XYZ',
        'length' => 5,
        'seed' => 99,
        'algorithm' => 'deterministic',
        'options' => ['debug' => true],
    ];

    $dto = SerialGenerationRequest::fromArray($params);

    expect($dto)
        ->quantity->toBe(100)
        ->alphabet->toBe('XYZ')
        ->length->toBe(5)
        ->seed->toBe(99)
        ->algorithm->toBe('deterministic')
        ->options->toBe(['debug' => true]);
});

it('throws when required params are missing', function () {

    $params = [
        // Falta quantity que debería ser obligatorio si lo definiste así
        'alphabet' => 'ABC',
        'length' => 3,
    ];

    expect(fn () => SerialGenerationRequest::fromArray($params))
        ->toThrow(InvalidArgumentException::class);
});
