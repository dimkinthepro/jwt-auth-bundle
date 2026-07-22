<?php

declare(strict_types=1);

namespace Dimkinthepro\JwtAuth\Application\DTO;

use Dimkinthepro\JwtAuth\Domain\Entity\JwtToken;
use Dimkinthepro\JwtAuth\Domain\Entity\RefreshToken;

readonly class TokenPair
{
    public function __construct(
        public JwtToken $token,
        public RefreshToken $refreshToken,
    ) {
    }
}
