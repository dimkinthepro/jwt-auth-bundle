<?php

declare(strict_types=1);

namespace Dimkinthepro\JwtAuth\Tests\Unit\Infrastructure\Validator;

use Dimkinthepro\JwtAuth\Application\Component\Provider\CryptoConfigurationProvider;
use Dimkinthepro\JwtAuth\Application\Component\Provider\KeyProviderInterface;
use Dimkinthepro\JwtAuth\Domain\Entity\JwtToken;
use Dimkinthepro\JwtAuth\Domain\Enum\AlgorithmEnum;
use Dimkinthepro\JwtAuth\Domain\Enum\TokenDictionaryEnum;
use Dimkinthepro\JwtAuth\Domain\Enum\TokenTypeEnum;
use Dimkinthepro\JwtAuth\Infrastructure\Decoder\Base64FieldsDecoder;
use Dimkinthepro\JwtAuth\Infrastructure\Decoder\JwtTokenDecoder;
use Dimkinthepro\JwtAuth\Infrastructure\Encoder\Base64FieldsEncoder;
use Dimkinthepro\JwtAuth\Infrastructure\Encoder\JwtTokenEncoder;
use Dimkinthepro\JwtAuth\Infrastructure\Exception\InvalidTokenException;
use Dimkinthepro\JwtAuth\Infrastructure\Exception\JwtTokenExpiredException;
use Dimkinthepro\JwtAuth\Infrastructure\Factory\DateTimeFactory;
use Dimkinthepro\JwtAuth\Infrastructure\Provider\JwtTokenSigner;
use Dimkinthepro\JwtAuth\Infrastructure\Provider\PayloadForSignProvider;
use Dimkinthepro\JwtAuth\Infrastructure\Service\KeyPairGeneratorService;
use Dimkinthepro\JwtAuth\Infrastructure\Validator\JwtTokenValidator;
use Faker\Factory;
use PHPUnit\Framework\TestCase;

class JwtTokenValidatorTest extends TestCase
{
    private const TOKEN_TTL = 3600;

    /**
     * @dataProvider providerAlgorithms
     */
    public function testValidTokenPassesValidation(AlgorithmEnum $algorithm): void
    {
        $keyProvider = $this->createKeyProvider($algorithm);
        $token = $this->createSignedAndDecodedToken($keyProvider, $algorithm, self::TOKEN_TTL);

        $this->createValidator($keyProvider, $algorithm)->validate($token);

        self::assertEquals($algorithm, $token->getAlgorithm());
    }

    public function testTokenWithCustomClaimsAndForeignKeyOrderIsValid(): void
    {
        $email = Factory::create()->email();
        $keyPair = (new KeyPairGeneratorService())->generate(AlgorithmEnum::RS256, '');
        $fieldsEncoder = new Base64FieldsEncoder();

        // Simulate a token produced by a third-party JWT library: different JSON key order and extra claims
        $encodedHeader = $fieldsEncoder->encode((string) json_encode(['typ' => 'JWT', 'alg' => 'RS256']));
        $encodedPayload = $fieldsEncoder->encode((string) json_encode([
            'exp' => time() + 3600,
            'role' => 'admin',
            'identifier' => $email,
            'iat' => time(),
        ]));
        $signingInput = \sprintf('%s.%s', $encodedHeader, $encodedPayload);
        $privateKey = openssl_pkey_get_private($keyPair->privateKey);
        self::assertInstanceOf(\OpenSSLAsymmetricKey::class, $privateKey);
        openssl_sign($signingInput, $signature, $privateKey, 'SHA256');
        $encodedToken = \sprintf('%s.%s', $signingInput, $fieldsEncoder->encode($signature));

        $keyProvider = $this->createMock(KeyProviderInterface::class);
        $keyProvider->method('getPublicKey')->willReturn(openssl_pkey_get_public($keyPair->publicKey));

        $token = (new JwtTokenDecoder(new Base64FieldsDecoder(), new DateTimeFactory()))->decode($encodedToken);
        $this->createValidator($keyProvider, AlgorithmEnum::RS256)->validate($token);

        self::assertEquals($email, $token->getUserIdentifier());
        self::assertEquals('admin', $token->getClaim('role'));
    }

    public function testTokenAlgorithmMismatchingConfigurationIsRejected(): void
    {
        $keyProvider = $this->createKeyProvider(AlgorithmEnum::RS384);
        $token = $this->createSignedAndDecodedToken($keyProvider, AlgorithmEnum::RS384, self::TOKEN_TTL);

        self::expectException(InvalidTokenException::class);
        self::expectExceptionMessage('Unexpected token algorithm');

        $this->createValidator($keyProvider, AlgorithmEnum::RS256)->validate($token);
    }

    public function testExpiredTokenIsRejected(): void
    {
        $keyProvider = $this->createKeyProvider(AlgorithmEnum::RS256);
        $token = $this->createSignedAndDecodedToken($keyProvider, AlgorithmEnum::RS256, -self::TOKEN_TTL);

        self::expectException(JwtTokenExpiredException::class);

        $this->createValidator($keyProvider, AlgorithmEnum::RS256)->validate($token);
    }

    public function testTamperedSignatureIsRejected(): void
    {
        $keyProvider = $this->createKeyProvider(AlgorithmEnum::RS256);
        $token = $this->createSignedAndDecodedToken($keyProvider, AlgorithmEnum::RS256, self::TOKEN_TTL);
        $token->setSignature(random_bytes(256));

        self::expectException(InvalidTokenException::class);

        $this->createValidator($keyProvider, AlgorithmEnum::RS256)->validate($token);
    }

    public function testTamperedPayloadIsRejected(): void
    {
        $keyProvider = $this->createKeyProvider(AlgorithmEnum::RS256);
        $token = $this->createSignedAndDecodedToken($keyProvider, AlgorithmEnum::RS256, self::TOKEN_TTL);

        $forgedToken = new JwtToken(
            $token->getAlgorithm(),
            $token->getType(),
            'attacker@example.com',
            $token->getIssuedAt(),
            $token->getExpiredAt()
        );
        $forgedToken->setSignature($token->getSignature());

        self::expectException(InvalidTokenException::class);

        $this->createValidator($keyProvider, AlgorithmEnum::RS256)->validate($forgedToken);
    }

    public function testTokenExpiredWithinLeewayPasses(): void
    {
        $keyProvider = $this->createKeyProvider(AlgorithmEnum::RS256);
        $token = $this->createSignedAndDecodedToken($keyProvider, AlgorithmEnum::RS256, -30);

        $this->createValidator($keyProvider, AlgorithmEnum::RS256, 60)->validate($token);

        self::assertLessThan(new \DateTimeImmutable(), $token->getExpiredAt());
    }

    public function testTokenNotYetValidIsRejected(): void
    {
        $keyProvider = $this->createKeyProvider(AlgorithmEnum::RS256);
        $token = $this->createSignedAndDecodedToken($keyProvider, AlgorithmEnum::RS256, self::TOKEN_TTL, [
            TokenDictionaryEnum::NOT_BEFORE->value => time() + 3600,
        ]);

        self::expectException(InvalidTokenException::class);
        self::expectExceptionMessage('Token not yet valid');

        $this->createValidator($keyProvider, AlgorithmEnum::RS256)->validate($token);
    }

    public function testNotBeforeWithinLeewayPasses(): void
    {
        $keyProvider = $this->createKeyProvider(AlgorithmEnum::RS256);
        $token = $this->createSignedAndDecodedToken($keyProvider, AlgorithmEnum::RS256, self::TOKEN_TTL, [
            TokenDictionaryEnum::NOT_BEFORE->value => time() + 30,
        ]);

        $this->createValidator($keyProvider, AlgorithmEnum::RS256, 60)->validate($token);

        self::assertIsInt($token->getClaim(TokenDictionaryEnum::NOT_BEFORE->value));
    }

    public function testIssuerMismatchIsRejected(): void
    {
        $keyProvider = $this->createKeyProvider(AlgorithmEnum::RS256);
        $token = $this->createSignedAndDecodedToken($keyProvider, AlgorithmEnum::RS256, self::TOKEN_TTL, [
            TokenDictionaryEnum::ISSUER->value => 'another-app',
        ]);

        self::expectException(InvalidTokenException::class);
        self::expectExceptionMessage('Unexpected token issuer');

        $this->createValidator($keyProvider, AlgorithmEnum::RS256, 60, 'my-app')->validate($token);
    }

    public function testMissingIssuerWhenRequiredIsRejected(): void
    {
        $keyProvider = $this->createKeyProvider(AlgorithmEnum::RS256);
        $token = $this->createSignedAndDecodedToken($keyProvider, AlgorithmEnum::RS256, self::TOKEN_TTL);

        self::expectException(InvalidTokenException::class);
        self::expectExceptionMessage('Unexpected token issuer');

        $this->createValidator($keyProvider, AlgorithmEnum::RS256, 60, 'my-app')->validate($token);
    }

    public function testMatchingIssuerAndAudiencePass(): void
    {
        $keyProvider = $this->createKeyProvider(AlgorithmEnum::RS256);
        $token = $this->createSignedAndDecodedToken($keyProvider, AlgorithmEnum::RS256, self::TOKEN_TTL, [
            TokenDictionaryEnum::ISSUER->value => 'my-app',
            TokenDictionaryEnum::AUDIENCE->value => ['mobile-client', 'web-client'],
        ]);

        $this->createValidator($keyProvider, AlgorithmEnum::RS256, 60, 'my-app', 'web-client')->validate($token);

        self::assertEquals('my-app', $token->getClaim(TokenDictionaryEnum::ISSUER->value));
    }

    public function testAudienceMismatchIsRejected(): void
    {
        $keyProvider = $this->createKeyProvider(AlgorithmEnum::RS256);
        $token = $this->createSignedAndDecodedToken($keyProvider, AlgorithmEnum::RS256, self::TOKEN_TTL, [
            TokenDictionaryEnum::AUDIENCE->value => 'mobile-client',
        ]);

        self::expectException(InvalidTokenException::class);
        self::expectExceptionMessage('Unexpected token audience');

        $this->createValidator($keyProvider, AlgorithmEnum::RS256, 60, null, 'web-client')->validate($token);
    }

    public function providerAlgorithms(): array
    {
        $result = [];
        foreach (AlgorithmEnum::cases() as $algorithm) {
            $result[$algorithm->value] = ['algorithm' => $algorithm];
        }

        return $result;
    }

    private function createValidator(
        KeyProviderInterface $keyProvider,
        AlgorithmEnum $configuredAlgorithm,
        int $clockSkewLeeway = 60,
        ?string $issuer = null,
        ?string $audience = null
    ): JwtTokenValidator {
        return new JwtTokenValidator(
            $configuredAlgorithm->value,
            $keyProvider,
            new CryptoConfigurationProvider(),
            new PayloadForSignProvider(new Base64FieldsEncoder()),
            new DateTimeFactory(),
            $clockSkewLeeway,
            $issuer,
            $audience
        );
    }

    private function createSignedAndDecodedToken(
        KeyProviderInterface $keyProvider,
        AlgorithmEnum $algorithm,
        int $ttl,
        array $customClaims = []
    ): JwtToken {
        $fieldsEncoder = new Base64FieldsEncoder();
        $dateTimeFactory = new DateTimeFactory();

        $token = new JwtToken(
            $algorithm,
            TokenTypeEnum::JWT,
            Factory::create()->email(),
            $dateTimeFactory->getNowDate(time()),
            $dateTimeFactory->getNowDate(time() + $ttl),
            $customClaims
        );

        $signer = new JwtTokenSigner(
            $keyProvider,
            new PayloadForSignProvider($fieldsEncoder),
            new CryptoConfigurationProvider(),
            $fieldsEncoder
        );
        $signer->sign($token);
        (new JwtTokenEncoder($fieldsEncoder))->encode($token);

        return (new JwtTokenDecoder(new Base64FieldsDecoder(), $dateTimeFactory))->decode($token->getEncodedToken());
    }

    private function createKeyProvider(AlgorithmEnum $algorithm): KeyProviderInterface
    {
        $keyPair = (new KeyPairGeneratorService())->generate($algorithm, '');

        $keyProvider = $this->createMock(KeyProviderInterface::class);
        $keyProvider->method('getPublicKey')->willReturn(openssl_pkey_get_public($keyPair->publicKey));
        $keyProvider->method('getPrivateKey')->willReturn(openssl_pkey_get_private($keyPair->privateKey));

        return $keyProvider;
    }
}
