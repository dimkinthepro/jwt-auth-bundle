<?php

declare(strict_types=1);

namespace Dimkinthepro\JwtAuth\Infrastructure\Provider;

use Dimkinthepro\JwtAuth\Application\Component\Provider\RefreshTokenGeneratorInterface;

readonly class RefreshTokenGenerator implements RefreshTokenGeneratorInterface
{
    public function __construct(
        private int $authRefreshTokenLength,
    ) {
    }

    public function generate(): string
    {
        /* @phpstan-ignore-next-line */
        return bin2hex(random_bytes($this->authRefreshTokenLength));
    }
}
