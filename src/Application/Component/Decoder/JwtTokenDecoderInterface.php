<?php

declare(strict_types=1);

namespace Dimkinthepro\JwtAuth\Application\Component\Decoder;

use Dimkinthepro\JwtAuth\Application\Component\Exception\JwtAuthExceptionInterface;
use Dimkinthepro\JwtAuth\Domain\Entity\JwtToken;

interface JwtTokenDecoderInterface
{
    /**
     * @throws JwtAuthExceptionInterface
     */
    public function decode(string $encodedToken): JwtToken;
}
