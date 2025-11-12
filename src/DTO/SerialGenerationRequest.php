<?php

namespace Verifarma\SerialCodesGenerator\DTO;

use Verifarma\SerialCodesGenerator\Enums\SerialRegulationProfile;

final class SerialGenerationRequest
{
    public function __construct(
        public int $quantity,

        // Formato básico
        public ?int $length = null,
        public ?string $alphabet = null,

        // Estructura
        public ?string $prefix = null,
        public ?string $suffix = null,
        public ?string $pattern = null, // Ej: 'AAAA-NNNN-XXXXXXXX'

        // Perfil regulatorio
        public ?SerialRegulationProfile $regulationProfile = null, // emvs, dscsa, default, etc.
        public bool $checkRandomization = false,
        public ?int $guessDifficultyFactor = null, // ej. 10000 para EMVS
        public ?int $minSampleSize = null,         // ej. 250 para EMVS

        // Contexto de negocio
        public ?int $productId = null,
        public ?int $batchId = null,
        public ?string $tenantId = null,
        public ?string $market = null, // 'EU', 'AR', 'US', etc.
        public array $context = [],    // extra libre

        // Extras futuros
        public array $options = [],
    ) {}
}
