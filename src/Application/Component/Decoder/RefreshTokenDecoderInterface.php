<?php

declare(strict_types=1);

namespace DimkinThePro\JwtAuth\Application\Component\Decoder;

use DimkinThePro\JwtAuth\Application\Component\Exception\JwtAuthExceptionInterface;

interface RefreshTokenDecoderInterface
{
    /**
     * @throws JwtAuthExceptionInterface
     */
    public function decode(string $encodedToken): string;
}
