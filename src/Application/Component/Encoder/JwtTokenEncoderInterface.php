<?php

declare(strict_types=1);

namespace DimkinThePro\JwtAuth\Application\Component\Encoder;

use DimkinThePro\JwtAuth\Domain\Entity\JwtToken;

interface JwtTokenEncoderInterface
{
    public function encode(JwtToken $token): void;
}
