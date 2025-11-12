<?php

namespace Verifarma\SerialCodesGenerator\DTO;

final class SerialGenerationRequest
{
    public function __construct(
        public int $quantity,

        // Formato básico
        public ?int $length = null,
        public ?string $alphabet = null,
        public ?string $algorithm = null,

        // Estructura
        public ?string $pattern = null, // Ej: 'AAAA-NNNN-XXXXXXXX'

        // Extras futuros
        public array $options = [],
    ) {}
}
