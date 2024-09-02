<?php

declare(strict_types=1);

namespace Dimkinthepro\JwtAuth\Infrastructure\Decoder;

use Dimkinthepro\JwtAuth\Application\Component\Decoder\FieldsDecoderInterface;
use Dimkinthepro\JwtAuth\Infrastructure\Exception\InvalidTokenException;

class Base64FieldsDecoder implements FieldsDecoderInterface
{
    public function decode(string $encodedToken): string
    {
        $token = base64_decode($encodedToken, true);
        if (false === $token) {
            throw new InvalidTokenException(sprintf(
                'd230edf9-92fd-4cf2-aa06-bc3324a5e657 Invalid token provided for encode: "%s"',
                $encodedToken
            ));
        }

        return $token;
    }
}
