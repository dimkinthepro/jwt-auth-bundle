<?php

declare(strict_types=1);

namespace Dimkinthepro\JwtAuth\Infrastructure\DTO;

class KeyPairDto
{
    public function __construct(
        public readonly string $publicKey,
        public readonly string $privateKey,
    ) {
    }
}
