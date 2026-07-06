<?php

declare(strict_types=1);

namespace Dimkinthepro\JwtAuth\Infrastructure\Encoder;

use Dimkinthepro\JwtAuth\Application\Component\Encoder\FieldsEncoderInterface;

/**
 * Encodes data as base64url without padding (RFC 7515, appendix C).
 */
readonly class Base64FieldsEncoder implements FieldsEncoderInterface
{
    public function encode(string $data): string
    {
        return rtrim(strtr(base64_encode($data), '+/', '-_'), '=');
    }
}
