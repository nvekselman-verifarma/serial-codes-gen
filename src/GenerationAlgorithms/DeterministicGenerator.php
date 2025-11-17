<?php

namespace Verifarma\SerialCodesGenerator\GenerationAlgorithms;

use Verifarma\SerialCodesGenerator\Contracts\GenerationAlgorithmContract;

class DeterministicGenerator implements GenerationAlgorithmContract
{
    public function getName(): string
    {
        return 'deterministic';
    }

    public function generateRawCode(
        \GMP $serialCodeCombinations,
        \GMP $idx,
        int $seed
    ): \GMP {

        if (gmp_cmp($idx, $serialCodeCombinations) >= 0) {
            throw new \RuntimeException('index is out of range for this serial code space');
        }

        $gSeed = gmp_init($seed);

        // need gcd(A, $serialCodeCombinations) = 1 to make sea bijective mod $serialCodeCombinations
        $A = gmp_init(3);
        while (gmp_cmp(gmp_gcd($A, $serialCodeCombinations), gmp_init(1)) !== 0) {
            $A = gmp_add($A, 2);
        }

        // B = another constant derived from the seed
        $B = gmp_xor($gSeed, gmp_init('0xA5A5A5A5A5A5A5A5'));

        // f(idx) = (A * idx + B) mod $serialCodeCombinations  → PRP over [0, $serialCodeCombinations-1]
        $value = gmp_mod(gmp_add(gmp_mul($idx, $A), $B), $serialCodeCombinations);

        return $value;

    }
}
