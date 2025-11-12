<?php

namespace Verifarma\SerialCodesGenerator\Services;

use Verifarma\SerialCodesGenerator\Contracts\SerialCodesGeneratorContract;
use Verifarma\SerialCodesGenerator\Contracts\GenerationAlgorithmContract;
use Verifarma\SerialCodesGenerator\DTO\SerialGenerationRequest;
use Verifarma\SerialCodesGenerator\GenerationAlgorithms\RandomGenerator;

class SerialCodesGeneratorService implements SerialCodesGeneratorContract
{
    /**
     * Defaults generales del paquete.
     */
    protected string $defaultAlphabet = '0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZ';
    protected int $defaultLength = 10;

    /**
     * Lista de algoritmos disponibles por defecto.
     *
     * @var GenerationAlgorithmContract[]
     */
    protected array $algorithms = [];

    public function __construct() {
        $this->algorithms = [
            new RandomGenerator(),
        ];
    }

    /**
     * @return string[]
     */
    public function generate(SerialGenerationRequest $request): array
    {
        // 1) Validar el request según reglas de dominio simples
        $this->validate($request);

        if ($request->quantity === 0) {
            return [];
        }

        // 2) Resolver length / alphabet efectivos
        $length   = $request->length   ?? $this->defaultLength;
        $alphabet = $request->alphabet ?? $this->defaultAlphabet;

        // 3) Resolver algoritmo a usar, según el nombre en el request
        $algorithm = $this->resolveAlgorithm($request);

        $generated      = [];
        $generatedIndex = []; // lookup O(1) para evitar duplicados en memoria

        for ($i = 0; $i < $request->quantity; $i++) {
            do {
                $code = $algorithm->generateRawCode(
                    alphabet: $alphabet,
                    length: $length,
                    idx: $i,
                    seed: $request->seed ?? null, // si querés tener un campo seed en el request
                );

                // $code = $this->applyStructure($rawCode, $request);

                $existsInMemory = isset($generatedIndex[$code]);
            } while ($existsInMemory);

            $generated[]           = $code;
            $generatedIndex[$code] = true;
        }

        return $generated;
    }

    /**
     * Validaciones simples del request.
     */
    protected function validate(SerialGenerationRequest $request): void
    {
        if ($request->quantity < 0) {
            throw new \InvalidArgumentException('Quantity cannot be negative.');
        }

        if ($request->quantity === 0) {
            // nada más que validar, generate() va a devolver []
            return;
        }

        $length   = $request->length   ?? $this->defaultLength;
        $alphabet = $request->alphabet ?? $this->defaultAlphabet;

        if ($length <= 0) {
            throw new \InvalidArgumentException('Length must be greater than zero.');
        }

        if ($alphabet === '') {
            throw new \InvalidArgumentException('Alphabet for serial code generation cannot be empty.');
        }
    }
    protected function resolveAlgorithm(SerialGenerationRequest $request): GenerationAlgorithmContract
    {
        // El request debería tener algo como:
        // public ?string $algorithm = null; // ej: 'random', 'pattern'
        $requestedAlgorithm = $request->algorithm ?? null;

        if ($requestedAlgorithm !== null) {
            foreach ($this->algorithms as $algorithm) {
                // Comparamos contra el nombre lógico del algoritmo
                if ($requestedAlgorithm === $algorithm->getName()) {
                    return $algorithm;
                }
            }

            // Si el usuario pidió un algoritmo específico y no está registrado, error claro
            throw new \RuntimeException(sprintf(
                'Requested algorithm "%s" is not registered in SerialCodesGeneratorService.',
                $requestedAlgorithm
            ));
        }

        // Si no se especificó algoritmo, usamos el primero como default
        if (! empty($this->algorithms)) {
            return $this->algorithms[0];
        }

        // Si no hay ningún algoritmo registrado, no podemos generar nada
        throw new \RuntimeException('No algorithms registered in SerialCodesGeneratorService.');
    }

}
