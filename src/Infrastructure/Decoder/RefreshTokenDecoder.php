<?php

declare(strict_types=1);

namespace Dimkinthepro\JwtAuth\Infrastructure\Decoder;

use Dimkinthepro\JwtAuth\Application\Component\Decoder\FieldsDecoderInterface;
use Dimkinthepro\JwtAuth\Application\Component\Decoder\RefreshTokenDecoderInterface;

class RefreshTokenDecoder implements RefreshTokenDecoderInterface
{
    public function __construct(
        private readonly FieldsDecoderInterface $fieldsDecoder,
    ) {
    }

    public function decode(string $encodedToken): string
    {
        return $this->fieldsDecoder->decode($encodedToken);
    }
}
