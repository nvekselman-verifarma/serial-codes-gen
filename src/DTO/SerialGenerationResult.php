<?php
namespace Verifarma\SerialCodesGenerator\DTO;

final class SerialGenerationResult
{
    /**
     * @param string[] $serials
     */
    public function __construct(
        public array $serials,
    ) {}
}
