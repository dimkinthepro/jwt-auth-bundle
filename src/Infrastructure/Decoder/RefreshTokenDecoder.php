<?php

declare(strict_types=1);

namespace DimkinThePro\JwtAuth\Infrastructure\Decoder;

use DimkinThePro\JwtAuth\Application\Component\Decoder\FieldsDecoderInterface;
use DimkinThePro\JwtAuth\Application\Component\Decoder\RefreshTokenDecoderInterface;

class RefreshTokenDecoder implements RefreshTokenDecoderInterface
{
    public function __construct(
        private readonly FieldsDecoderInterface $fieldsDecoder,
    ) {
    }

    /**
     * {@inheritDoc}
     */
    public function decode(string $encodedToken): string
    {
        return $this->fieldsDecoder->decode($encodedToken);
    }
}
