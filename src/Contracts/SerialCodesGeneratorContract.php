<?php

namespace Verifarma\SerialCodesGenerator\Contracts;

use Verifarma\SerialCodesGenerator\DTO\SerialGenerationRequest;
use Verifarma\SerialCodesGenerator\DTO\SerialGenerationResult;

interface SerialCodesGeneratorContract
{
    public function generate(SerialGenerationRequest $request): SerialGenerationResult;
}

