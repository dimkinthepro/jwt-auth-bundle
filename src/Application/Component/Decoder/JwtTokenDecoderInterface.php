<?php

declare(strict_types=1);

namespace DimkinThePro\JwtAuth\Application\Component\Decoder;

use DimkinThePro\JwtAuth\Application\Component\Exception\JwtAuthExceptionInterface;
use DimkinThePro\JwtAuth\Domain\Entity\JwtToken;

interface JwtTokenDecoderInterface
{
    /**
     * @throws JwtAuthExceptionInterface
     */
    public function decode(string $encodedToken): JwtToken;
}
