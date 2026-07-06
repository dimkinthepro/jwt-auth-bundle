<?php

declare(strict_types=1);

namespace Dimkinthepro\JwtAuth\Tests\Unit\Infrastructure\Decoder;

use Dimkinthepro\JwtAuth\Domain\Entity\JwtToken;
use Dimkinthepro\JwtAuth\Domain\Enum\AlgorithmEnum;
use Dimkinthepro\JwtAuth\Domain\Enum\TokenTypeEnum;
use Dimkinthepro\JwtAuth\Infrastructure\Decoder\Base64FieldsDecoder;
use Dimkinthepro\JwtAuth\Infrastructure\Decoder\JwtTokenDecoder;
use Dimkinthepro\JwtAuth\Infrastructure\Encoder\Base64FieldsEncoder;
use Dimkinthepro\JwtAuth\Infrastructure\Encoder\JwtTokenEncoder;
use Dimkinthepro\JwtAuth\Infrastructure\Exception\InvalidTokenException;
use Dimkinthepro\JwtAuth\Infrastructure\Factory\DateTimeFactory;
use Faker\Factory;
use PHPUnit\Framework\TestCase;

class JwtTokenDecoderTest extends TestCase
{
    public function testDecodeRestoresEncodedToken(): void
    {
        $email = Factory::create()->email();
        $issuedAt = time();
        $expiredAt = $issuedAt + 3600;
        $signature = random_bytes(64);

        $token = new JwtToken(
            AlgorithmEnum::RS256,
            TokenTypeEnum::JWT,
            $email,
            (new DateTimeFactory())->getNowDate($issuedAt),
            (new DateTimeFactory())->getNowDate($expiredAt)
        );
        $token->setSignature((new Base64FieldsEncoder())->encode($signature));
        (new JwtTokenEncoder(new Base64FieldsEncoder()))->encode($token);

        $decodedToken = $this->createDecoder()->decode($token->getEncodedToken());

        self::assertEquals(AlgorithmEnum::RS256, $decodedToken->getAlgorithm());
        self::assertEquals(TokenTypeEnum::JWT, $decodedToken->getType());
        self::assertEquals($email, $decodedToken->getUserIdentifier());
        self::assertEquals($issuedAt, $decodedToken->getIssuedAt()->getTimestamp());
        self::assertEquals($expiredAt, $decodedToken->getExpiredAt()->getTimestamp());
        self::assertEquals($signature, $decodedToken->getSignature());
    }

    public function testDecodePreservesCustomClaimsAndSigningInput(): void
    {
        $token = new JwtToken(
            AlgorithmEnum::RS256,
            TokenTypeEnum::JWT,
            Factory::create()->email(),
            (new DateTimeFactory())->getNowDate(time()),
            (new DateTimeFactory())->getNowDate(time() + 3600),
            ['role' => 'admin', 'permissions' => ['read', 'write']]
        );
        $token->setSignature((new Base64FieldsEncoder())->encode(random_bytes(64)));
        (new JwtTokenEncoder(new Base64FieldsEncoder()))->encode($token);

        $decodedToken = $this->createDecoder()->decode($token->getEncodedToken());

        self::assertEquals('admin', $decodedToken->getClaim('role'));
        self::assertEquals(['read', 'write'], $decodedToken->getClaim('permissions'));
        self::assertNull($decodedToken->getClaim('unknown'));

        $parts = explode('.', $token->getEncodedToken());
        self::assertEquals(\sprintf('%s.%s', $parts[0], $parts[1]), $decodedToken->getSigningInput());
    }

    /**
     * @dataProvider providerInvalidTokens
     */
    public function testInvalidTokenIsRejected(string $encodedToken): void
    {
        self::expectException(InvalidTokenException::class);

        $this->createDecoder()->decode($encodedToken);
    }

    public function providerInvalidTokens(): array
    {
        $fieldsEncoder = new Base64FieldsEncoder();
        $validHeader = $fieldsEncoder->encode((string) json_encode(['alg' => 'RS256', 'typ' => 'JWT']));
        $validPayload = $fieldsEncoder->encode(
            (string) json_encode(['identifier' => 'user@example.com', 'iat' => time(), 'exp' => time() + 3600])
        );
        $signature = $fieldsEncoder->encode('signature');

        return [
            'emptyString' => [''],
            'randomString' => [Factory::create()->password()],
            'missingParts' => [\sprintf('%s.%s', $validHeader, $validPayload)],
            'tooManyParts' => [\sprintf('%s.%s.%s.%s', $validHeader, $validPayload, $signature, $signature)],
            'headerIsNotJson' => [\sprintf('%s.%s.%s', $fieldsEncoder->encode('not json'), $validPayload, $signature)],
            'unknownAlgorithm' => [\sprintf(
                '%s.%s.%s',
                $fieldsEncoder->encode((string) json_encode(['alg' => 'HS256', 'typ' => 'JWT'])),
                $validPayload,
                $signature
            )],
            'payloadWithoutIdentifier' => [\sprintf(
                '%s.%s.%s',
                $validHeader,
                $fieldsEncoder->encode((string) json_encode(['iat' => time(), 'exp' => time() + 3600])),
                $signature
            )],
        ];
    }

    private function createDecoder(): JwtTokenDecoder
    {
        return new JwtTokenDecoder(new Base64FieldsDecoder(), new DateTimeFactory());
    }
}
