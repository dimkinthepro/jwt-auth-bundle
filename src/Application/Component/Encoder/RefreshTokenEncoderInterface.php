<?php

declare(strict_types=1);

namespace DimkinThePro\JwtAuth\Application\Component\Encoder;

use DimkinThePro\JwtAuth\Domain\Entity\RefreshToken;

interface RefreshTokenEncoderInterface
{
    public function encode(RefreshToken $token): void;
}
