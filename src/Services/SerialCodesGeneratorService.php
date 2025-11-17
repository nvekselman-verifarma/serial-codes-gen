<?php

namespace Verifarma\SerialCodesGenerator\Services;

use Verifarma\SerialCodesGenerator\Contracts\GenerationAlgorithmContract;
use Verifarma\SerialCodesGenerator\Contracts\SerialCodesGeneratorContract;
use Verifarma\SerialCodesGenerator\DTO\SerialGenerationRequest;
use Verifarma\SerialCodesGenerator\GenerationAlgorithms\AesGenerator;
use Verifarma\SerialCodesGenerator\GenerationAlgorithms\DeterministicGenerator;
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

    public function __construct()
    {
        $this->algorithms = [
            new RandomGenerator,
            new AesGenerator,
            new DeterministicGenerator,
        ];
    }

    /**
     * @return string[]
     */
    public function generate(SerialGenerationRequest $request): array
    {
        $this->validate($request);

        if ($request->quantity === 0) {
            return [];
        }

        $length = $request->length ?? $this->defaultLength;
        $alphabet = $request->alphabet ?? $this->defaultAlphabet;
        $alphabetLength = strlen($alphabet);

        $algorithm = $this->determineAlgorithm($request);

        // determine combinations of serial codes bounded to 2^128
        $serialCodeCombinations = gmp_pow(gmp_init(2), 128);
        if (log($alphabetLength, 2) * $length < 128) {
            $serialCodeCombinations = gmp_pow(gmp_init($alphabetLength), $length);
        }

        $generatedIndex = [];

        for ($i = 0; $i < $request->quantity; $i++) {
            do {
                $gmp_code = $algorithm->generateRawCode(
                    serialCodeCombinations: $serialCodeCombinations,
                    idx: gmp_init($i + $request->index),
                    seed: $request->seed ?? 0,
                );
                $code = $this->encodeBaseNFromGmp($gmp_code, $alphabet, $length);

            } while (isset($generatedIndex[$code]));

            $generatedIndex[$code] = true;
        }

        return array_keys($generatedIndex);
    }

    public function generateFrom(array $params)
    {
        return $this->generate(
            SerialGenerationRequest::fromArray($params)
        );
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

        $length = $request->length ?? $this->defaultLength;
        $alphabet = $request->alphabet ?? $this->defaultAlphabet;

        if ($length <= 0) {
            throw new \InvalidArgumentException('Length must be greater than zero.');
        }

        if ($alphabet === '') {
            throw new \InvalidArgumentException('Alphabet for serial code generation cannot be empty.');
        }

        // Validate: no repeated characters
        if (strlen($alphabet) !== count(array_unique(str_split($alphabet)))) {
            throw new \InvalidArgumentException('Alphabet cannot contain repeated characters.');
        }
    }

    protected function determineAlgorithm(SerialGenerationRequest $request): GenerationAlgorithmContract
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

    // -------------------------------------------------------------------------
    // Convert value [0..M-1] to given alphabet and length
    // -------------------------------------------------------------------------

    private function encodeBaseNFromGmp(\GMP $value, string $alphabet, int $length): string
    {
        $alphabetLength = gmp_init(strlen($alphabet));
        $chars = [];

        for ($i = 0; $i < $length; $i++) {
            $digit = gmp_intval(gmp_mod($value, $alphabetLength));
            $chars[] = $alphabet[$digit];
            $value = gmp_div_q($value, $alphabetLength);
        }

        return implode('', $chars);
    }
}
