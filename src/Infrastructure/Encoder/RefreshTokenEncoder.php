<?php

declare(strict_types=1);

namespace Dimkinthepro\JwtAuth\Infrastructure\Encoder;

use Dimkinthepro\JwtAuth\Application\Component\Encoder\FieldsEncoderInterface;
use Dimkinthepro\JwtAuth\Application\Component\Encoder\RefreshTokenEncoderInterface;
use Dimkinthepro\JwtAuth\Domain\Entity\RefreshToken;

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
