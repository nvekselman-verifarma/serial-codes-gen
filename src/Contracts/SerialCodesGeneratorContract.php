<?php

namespace Verifarma\SerialCodesGenerator\Contracts;

use Verifarma\SerialCodesGenerator\DTO\SerialGenerationRequest;

interface SerialCodesGeneratorContract
{
    /**
     * @return string[]
     */
    public function generate(SerialGenerationRequest $request): array;
}
