<?php

declare(strict_types=1);

namespace Dimkinthepro\JwtAuth\Tests\Unit\Infrastructure\Decoder;

use Dimkinthepro\JwtAuth\Infrastructure\Decoder\Base64FieldsDecoder;
use Dimkinthepro\JwtAuth\Infrastructure\Decoder\RefreshTokenDecoder;
use Dimkinthepro\JwtAuth\Infrastructure\Encoder\Base64FieldsEncoder;
use Faker\Factory;
use PHPUnit\Framework\TestCase;

class RefreshTokenDecoderTest extends TestCase
{
    public function testDecodeRestoresRawToken(): void
    {
        $rawToken = bin2hex(random_bytes(Factory::create()->numberBetween(16, 128)));
        $encodedToken = (new Base64FieldsEncoder())->encode($rawToken);

        $decoded = (new RefreshTokenDecoder(new Base64FieldsDecoder()))->decode($encodedToken);

        self::assertEquals($rawToken, $decoded);
    }
}
