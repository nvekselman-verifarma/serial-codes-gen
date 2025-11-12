<?php

namespace Verifarma\SerialCodesGenerator\GenerationAlgorithms;

use Verifarma\SerialCodesGenerator\Contracts\GenerationAlgorithmContract;

class RandomGenerator implements GenerationAlgorithmContract
{
    public function getName(): string
    {
        return 'random';
    }

    public function generateRawCode(
        string $alphabet,
        int $length,
        ?int $idx = null,
        ?int $seed = null,
    ): string {
        $code = '';
        $alphabetLength = strlen($alphabet);

        for ($i = 0; $i < $length; $i++) {
            $index = random_int(0, $alphabetLength - 1);
            $code .= $alphabet[$index];
        }

        return $code;
    }
}
