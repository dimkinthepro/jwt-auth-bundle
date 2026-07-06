<?php

declare(strict_types=1);

namespace Dimkinthepro\JwtAuth\Tests\Unit\Infrastructure\Decoder;

use Dimkinthepro\JwtAuth\Infrastructure\Decoder\Base64FieldsDecoder;
use Dimkinthepro\JwtAuth\Infrastructure\Encoder\Base64FieldsEncoder;
use Dimkinthepro\JwtAuth\Infrastructure\Exception\InvalidTokenException;
use Faker\Factory;
use PHPUnit\Framework\TestCase;

class Base64FieldsDecoderTest extends TestCase
{
    public function testDecodeRestoresEncodedData(): void
    {
        $data = Factory::create()->sentence() . random_bytes(32);

        $encoded = (new Base64FieldsEncoder())->encode($data);
        $decoded = (new Base64FieldsDecoder())->decode($encoded);

        self::assertEquals($data, $decoded);
    }

    public function testEncodedDataIsUrlSafeWithoutPadding(): void
    {
        // 0xfb 0xff 0xfe encodes to "+//+" in standard base64; RFC 7515 requires "-__-"
        $encoded = (new Base64FieldsEncoder())->encode("\xfb\xff\xfe" . random_bytes(64));

        self::assertMatchesRegularExpression('/^[A-Za-z0-9_-]+$/', $encoded);
    }

    /**
     * @dataProvider providerInvalidData
     */
    public function testInvalidDataIsRejected(string $encodedData): void
    {
        self::expectException(InvalidTokenException::class);

        (new Base64FieldsDecoder())->decode($encodedData);
    }

    public function providerInvalidData(): array
    {
        return [
            'randomText' => ['not валидный base64!!!'],
            'standardBase64Alphabet' => ['+//+'],
            'standardBase64Padding' => ['QQ=='],
        ];
    }
}
