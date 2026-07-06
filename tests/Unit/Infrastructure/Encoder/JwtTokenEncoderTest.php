<?php

declare(strict_types=1);

namespace Dimkinthepro\JwtAuth\Tests\Unit\Infrastructure\Encoder;

use Dimkinthepro\JwtAuth\Domain\Entity\JwtToken;
use Dimkinthepro\JwtAuth\Domain\Enum\AlgorithmEnum;
use Dimkinthepro\JwtAuth\Domain\Enum\TokenDictionaryEnum;
use Dimkinthepro\JwtAuth\Domain\Enum\TokenTypeEnum;
use Dimkinthepro\JwtAuth\Infrastructure\Decoder\Base64FieldsDecoder;
use Dimkinthepro\JwtAuth\Infrastructure\Encoder\Base64FieldsEncoder;
use Dimkinthepro\JwtAuth\Infrastructure\Encoder\JwtTokenEncoder;
use Dimkinthepro\JwtAuth\Infrastructure\Factory\DateTimeFactory;
use Faker\Factory;
use PHPUnit\Framework\TestCase;

class JwtTokenEncoderTest extends TestCase
{
    public function testEncodeBuildsThreePartToken(): void
    {
        $email = Factory::create()->email();
        $issuedAt = time();
        $expiredAt = $issuedAt + 3600;
        $fieldsEncoder = new Base64FieldsEncoder();
        $fieldsDecoder = new Base64FieldsDecoder();
        $encodedSignature = $fieldsEncoder->encode(random_bytes(64));

        $token = new JwtToken(
            AlgorithmEnum::RS512,
            TokenTypeEnum::JWT,
            $email,
            (new DateTimeFactory())->getNowDate($issuedAt),
            (new DateTimeFactory())->getNowDate($expiredAt)
        );
        $token->setSignature($encodedSignature);

        (new JwtTokenEncoder($fieldsEncoder))->encode($token);

        $parts = explode('.', $token->getEncodedToken());
        self::assertCount(3, $parts);

        $header = json_decode($fieldsDecoder->decode($parts[0]), true);
        $payload = json_decode($fieldsDecoder->decode($parts[1]), true);

        self::assertEquals(AlgorithmEnum::RS512->value, $header[TokenDictionaryEnum::ALGORITHM->value]);
        self::assertEquals(TokenTypeEnum::JWT->value, $header[TokenDictionaryEnum::TYPE->value]);
        self::assertEquals($email, $payload[TokenDictionaryEnum::IDENTIFIER->value]);
        self::assertEquals($issuedAt, $payload[TokenDictionaryEnum::ISSUED_AT->value]);
        self::assertEquals($expiredAt, $payload[TokenDictionaryEnum::EXPIRED_AT->value]);
        self::assertEquals($encodedSignature, $parts[2]);
    }
}
