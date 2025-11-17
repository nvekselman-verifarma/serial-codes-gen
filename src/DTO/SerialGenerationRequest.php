<?php

namespace Verifarma\SerialCodesGenerator\DTO;

use ReflectionClass;

final class SerialGenerationRequest
{
    public function __construct(
        public ?int $quantity = null,
        public ?int $length = null,
        public ?string $alphabet = null,
        public ?string $algorithm = null,
        public ?int $seed = null,
        public ?int $index = 0,
        public array $options = [],
    ) {}

    public static function fromArray(array $params): self
    {
        // Reflect the constructor params
        $ref = new ReflectionClass(self::class);
        $ctorParams = $ref->getConstructor()->getParameters();

        $args = [];

        foreach ($ctorParams as $p) {
            $name = $p->getName();

            // Si viene en $params, lo uso
            if (array_key_exists($name, $params)) {
                $args[$name] = $params[$name];
            }
            // Si no viene, uso el default del constructor
            elseif ($p->isDefaultValueAvailable()) {
                $args[$name] = $p->getDefaultValue();
            }
            // Valor obligatorio sin default (casi nunca)
            else {
                throw new \InvalidArgumentException("Missing required parameter: {$name}");
            }
        }

        return new self(...$args);
    }
}
