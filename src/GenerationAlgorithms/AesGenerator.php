<?php

namespace Verifarma\SerialCodesGenerator\GenerationAlgorithms;

use Verifarma\SerialCodesGenerator\Contracts\GenerationAlgorithmContract;

class AesGenerator implements GenerationAlgorithmContract
{
    public function getName(): string
    {
        return 'AES';
    }

    public function generateRawCode(
        \GMP $serialCodeCombinations,
        \GMP $idx,
        int $seed
    ): \GMP {
        // chequeo de rango: idx < M
        $block = gmp_export($idx, 1, GMP_MSW_FIRST | GMP_BIG_ENDIAN);
        $block = substr(str_pad($block, 16, "\0", STR_PAD_LEFT), -16);

        $key = substr(hash('sha256', (string) $seed, true), 0, 16);

        $cipher = openssl_encrypt($block, 'aes-128-ecb', $key, OPENSSL_RAW_DATA | OPENSSL_ZERO_PADDING);

        $val = gmp_import($cipher, 1, GMP_MSW_FIRST | GMP_BIG_ENDIAN);

        return gmp_mod($val, $serialCodeCombinations);
    }
}
