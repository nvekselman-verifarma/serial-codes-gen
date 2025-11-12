<?php

namespace Verifarma\SerialCodesGenerator\Contracts;

interface GenerationAlgorithmContract
{
    /**
     * Nombre lógico del algoritmo, ej: "random", "pattern", "emvs".
     */
    public function getName(): string;

    /**
     * @param  string  $alphabet  Alfabeto a utilizar para generar los códigos
     * @param  int|null  $idx  Índice / offset del batch (ej: número de chunk o iteración externa)
     * @param  int|null  $seed  Semilla opcional para generación determinística
     * @return string Raw serial code
     */
    public function generateRawCode(
        string $alphabet,
        int $length,
        ?int $idx = null,
        ?int $seed = null,
    ): string;
}
