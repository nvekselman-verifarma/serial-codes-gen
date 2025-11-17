<?php

namespace Verifarma\SerialCodesGenerator\Contracts;

interface GenerationAlgorithmContract
{
    /**
     * Logical name of the algorithm (e.g. "aes-prp", "feistel", "random").
     */
    public function getName(): string;

    /**
     * Generate a raw numeric code in the range [0, serialCodeCombinations - 1].
     *
     * @param  \GMP  $serialCodeCombinations  Total possible combinations.
     * @param  \GMP  $idx  Sequential index in the batch.
     * @param  int  $seed  Optional deterministic seed.
     */
    public function generateRawCode(
        \GMP $serialCodeCombinations,
        \GMP $idx,
        int $seed
    ): \GMP;
}
