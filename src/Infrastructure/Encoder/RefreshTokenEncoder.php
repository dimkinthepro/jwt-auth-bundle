<?php

declare(strict_types=1);

namespace DimkinThePro\JwtAuth\Infrastructure\Encoder;

use DimkinThePro\JwtAuth\Application\Component\Encoder\FieldsEncoderInterface;
use DimkinThePro\JwtAuth\Application\Component\Encoder\RefreshTokenEncoderInterface;
use DimkinThePro\JwtAuth\Domain\Entity\RefreshToken;

class RefreshTokenEncoder implements RefreshTokenEncoderInterface
{
    public function __construct(
        private readonly FieldsEncoderInterface $fieldsEncoder,
    ) {
    }

    public function encode(RefreshToken $token): void
    {
        $encodedToken = $this->fieldsEncoder->encode($token->getToken());
        $token->setEncodedToken($encodedToken);
    }
}
