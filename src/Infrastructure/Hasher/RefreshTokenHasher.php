<?php

declare(strict_types=1);

namespace Dimkinthepro\JwtAuth\Infrastructure\Hasher;

use Dimkinthepro\JwtAuth\Application\Component\Hasher\RefreshTokenHasherInterface;

readonly class RefreshTokenHasher implements RefreshTokenHasherInterface
{
    private const HASHING_ALGORITHM = 'sha256';

    public function hash(string $rawToken): string
    {
        return hash(self::HASHING_ALGORITHM, $rawToken);
    }
}
