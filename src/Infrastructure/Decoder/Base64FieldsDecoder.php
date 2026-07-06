<?php

declare(strict_types=1);

namespace Dimkinthepro\JwtAuth\Infrastructure\Decoder;

use Dimkinthepro\JwtAuth\Application\Component\Decoder\FieldsDecoderInterface;
use Dimkinthepro\JwtAuth\Infrastructure\Exception\InvalidTokenException;

/**
 * Decodes base64url without padding (RFC 7515, appendix C).
 */
readonly class Base64FieldsDecoder implements FieldsDecoderInterface
{
    private const BASE64_URL_PATTERN = '/^[A-Za-z0-9_-]*$/';
    private const BASE64_BLOCK_LENGTH = 4;

    public function decode(string $encodedToken): string
    {
        if (1 !== preg_match(self::BASE64_URL_PATTERN, $encodedToken)) {
            throw new InvalidTokenException('d230edf9-92fd-4cf2-aa06-bc3324a5e657 Invalid base64url data provided');
        }

        $base64 = strtr($encodedToken, '-_', '+/');
        $paddingLength = (self::BASE64_BLOCK_LENGTH - \strlen($base64) % self::BASE64_BLOCK_LENGTH)
            % self::BASE64_BLOCK_LENGTH;
        $token = base64_decode($base64 . str_repeat('=', $paddingLength), true);

        if (false === $token) {
            throw new InvalidTokenException('a34a95c9-4a2e-4507-9d24-0dd94a8e0e29 Invalid base64url data provided');
        }

        return $token;
    }
}
