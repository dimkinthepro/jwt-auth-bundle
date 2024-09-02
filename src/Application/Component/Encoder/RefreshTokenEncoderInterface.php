<?php

declare(strict_types=1);

namespace Dimkinthepro\JwtAuth\Application\Component\Encoder;

use Dimkinthepro\JwtAuth\Domain\Entity\RefreshToken;

interface RefreshTokenEncoderInterface
{
    public function encode(RefreshToken $token): void;
}
