<?php

declare(strict_types=1);

namespace Dimkinthepro\JwtAuth\Application\Component\Hasher;

interface RefreshTokenHasherInterface
{
    public function hash(string $rawToken): string;
}
