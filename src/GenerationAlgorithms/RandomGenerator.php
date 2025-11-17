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
        \GMP $serialCodeCombinations,
        \GMP $idx,
        int $seed
    ): \GMP {
        $random = gmp_import(random_bytes(16)); // 128 bits

        return gmp_mod($random, $serialCodeCombinations);
    }
}
