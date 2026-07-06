<?php

declare(strict_types=1);

namespace Dimkinthepro\JwtAuth\Application\Component\Encoder;

use Dimkinthepro\JwtAuth\Domain\Entity\JwtToken;

interface JwtTokenEncoderInterface
{
    public function encode(JwtToken $token): void;
}
