<?php

declare(strict_types=1);

namespace Dimkinthepro\JwtAuth\Infrastructure\DTO;

readonly class KeyPairDto
{
    public function __construct(
        public string $publicKey,
        public string $privateKey,
    ) {
    }
}
