<?php

namespace Verifarma\SerialCodesGenerator\Services;

use Illuminate\Support\Facades\DB;
use Verifarma\SerialCodesGenerator\Contracts\SerialCodesGeneratorContract;
use Verifarma\SerialCodesGenerator\DTO\SerialGenerationRequest;
use Verifarma\SerialCodesGenerator\DTO\SerialGenerationResult;

class SerialCodesGeneratorService implements SerialCodesGeneratorContract
{
    /**
     * @param string $defaultAlphabet  Alfabeto por defecto
     * @param int    $defaultLength    Largo por defecto del código
     */
    public function __construct(
        protected string $defaultAlphabet,
        protected int $defaultLength,
    ) {
    }

    public function generate(SerialGenerationRequest $request): SerialGenerationResult
    {
        $length   = $request->length   ?? $this->defaultLength;
        $alphabet = $request->alphabet ?? $this->defaultAlphabet;

        $generated      = [];
        $generatedIndex = []; // lookup O(1) para evitar duplicados en memoria

        for ($i = 0; $i < $request->quantity; $i++) {
            do {
                // Parte aleatoria "cruda"
                $rawCode = $this->generateCode($alphabet, $length);

                // Aplicar estructura: prefix, suffix, pattern, etc.
                $code = $this->applyStructure($rawCode, $request);

                $existsInMemory   = isset($generatedIndex[$code]);

            } while ($existsInMemory);

            $generated[]             = $code;
            $generatedIndex[$code]   = true;
        }

        return new SerialGenerationResult($generated);
    }

    /**
     * Genera un código usando un alfabeto y largo dados.
     * Intenta usar gmp_random_range si está disponible, sino random_int.
     */
    protected function generateCode(string $alphabet, int $length): string
    {
        $code           = '';
        $alphabetLength = strlen($alphabet);

        if ($alphabetLength === 0) {
            throw new \RuntimeException('Alphabet for serial code generation cannot be empty.');
        }

        for ($i = 0; $i < $length; $i++) {
            $index = random_int(0, $alphabetLength - 1);
            $code .= $alphabet[$index];
        }

        return $code;
    }

    /**
     * Aplica estructura (prefix, suffix, y a futuro pattern) sobre el código "crudo".
     */
    protected function applyStructure(string $rawCode, SerialGenerationRequest $request): string
    {
        // TODO: si usás pattern tipo 'AAAA-NNNN-XXXXXXXX', acá podrías mapearlo.
        // Por ahora sólo aplicamos prefix/suffix.

        $code = $rawCode;

        if ($request->prefix !== null) {
            $code = $request->prefix . $code;
        }

        if ($request->suffix !== null) {
            $code .= $request->suffix;
        }

        return $code;
    }

}
