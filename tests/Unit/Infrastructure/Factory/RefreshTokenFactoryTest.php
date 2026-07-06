<?php

declare(strict_types=1);

namespace Dimkinthepro\JwtAuth\Tests\Unit\Infrastructure\Factory;

use Dimkinthepro\JwtAuth\Application\Component\Provider\DeviceContextProviderInterface;
use Dimkinthepro\JwtAuth\Application\Component\Provider\RefreshTokenGeneratorInterface;
use Dimkinthepro\JwtAuth\Domain\Entity\RefreshToken;
use Dimkinthepro\JwtAuth\Domain\ValueObject\DeviceContext;
use Dimkinthepro\JwtAuth\Infrastructure\Decoder\Base64FieldsDecoder;
use Dimkinthepro\JwtAuth\Infrastructure\Encoder\Base64FieldsEncoder;
use Dimkinthepro\JwtAuth\Infrastructure\Factory\DateTimeFactory;
use Dimkinthepro\JwtAuth\Infrastructure\Factory\RefreshTokenFactory;
use Dimkinthepro\JwtAuth\Infrastructure\Hasher\RefreshTokenHasher;
use Dimkinthepro\JwtAuth\Infrastructure\Provider\RefreshTokenGenerator;
use Faker\Factory;
use PHPUnit\Framework\TestCase;

class RefreshTokenFactoryTest extends TestCase
{
    public function testCreateToken(): void
    {
        $ttl = rand();
        $email = Factory::create()->email();
        $tokenValue = Factory::create()->password();

        $factory = $this->getFactory($ttl, $tokenValue);
        $token = $factory->create($email);

        self::assertInstanceOf(RefreshToken::class, $token);
        self::assertEquals($email, $token->getUserIdentifier());
        self::assertEquals(hash('sha256', $tokenValue), $token->getTokenHash());
        self::assertEquals($tokenValue, (new Base64FieldsDecoder())->decode($token->getEncodedToken()));
        $validDate = (new \DateTimeImmutable())->setTimestamp(time() + $ttl);
        self::assertEquals($validDate, $token->getValidUntil());
    }

    public function testCreateStartsNewSessionWithDeviceContext(): void
    {
        $deviceContext = new DeviceContext('iPhone 13', 'Mozilla/5.0', '203.0.113.10');
        $factory = $this->getFactory(3600, Factory::create()->password(), $deviceContext);

        $token = $factory->create(Factory::create()->email());

        self::assertMatchesRegularExpression('/^[0-9a-f]{32}$/', $token->getSessionId());
        self::assertEquals($token->getCreatedAt(), $token->getLastUsedAt());
        self::assertEquals('iPhone 13', $token->getDeviceName());
        self::assertEquals('Mozilla/5.0', $token->getUserAgent());
        self::assertEquals('203.0.113.10', $token->getIp());
    }

    public function testRotateKeepsSessionIdentityAndIssuesNewTokenValue(): void
    {
        $email = Factory::create()->email();
        $previousToken = new RefreshToken(
            hash('sha256', 'previous-token'),
            $email,
            new \DateTimeImmutable('+1 month'),
            bin2hex(random_bytes(16)),
            new \DateTimeImmutable('-2 days'),
            new \DateTimeImmutable('-1 hour'),
            'iPhone 13',
            'Mozilla/5.0 (old)',
            '203.0.113.10'
        );

        $newContext = new DeviceContext(null, 'Mozilla/5.0 (new)', '198.51.100.7');
        $factory = new RefreshTokenFactory(
            3600,
            new RefreshTokenGenerator(128),
            new RefreshTokenHasher(),
            new Base64FieldsEncoder(),
            new DateTimeFactory(),
            $this->getDeviceContextProvider($newContext)
        );

        $rotatedToken = $factory->rotate($previousToken);

        self::assertEquals($previousToken->getSessionId(), $rotatedToken->getSessionId());
        self::assertEquals($previousToken->getCreatedAt(), $rotatedToken->getCreatedAt());
        self::assertEquals($email, $rotatedToken->getUserIdentifier());
        self::assertNotEquals($previousToken->getTokenHash(), $rotatedToken->getTokenHash());
        self::assertGreaterThan($previousToken->getLastUsedAt(), $rotatedToken->getLastUsedAt());
        // Device name is inherited when the current request does not provide one; UA and IP are refreshed
        self::assertEquals('iPhone 13', $rotatedToken->getDeviceName());
        self::assertEquals('Mozilla/5.0 (new)', $rotatedToken->getUserAgent());
        self::assertEquals('198.51.100.7', $rotatedToken->getIp());
    }

    private function getFactory(
        int $ttl,
        string $token,
        ?DeviceContext $deviceContext = null
    ): RefreshTokenFactory {
        return new RefreshTokenFactory(
            $ttl,
            $this->getRefreshTokenGeneratorService($token),
            new RefreshTokenHasher(),
            new Base64FieldsEncoder(),
            new DateTimeFactory(),
            $this->getDeviceContextProvider($deviceContext ?? new DeviceContext())
        );
    }

    private function getRefreshTokenGeneratorService(string $token): RefreshTokenGeneratorInterface
    {
        $generator = $this->createMock(RefreshTokenGeneratorInterface::class);
        $generator->method('generate')->willReturn($token);

        return $generator;
    }

    private function getDeviceContextProvider(DeviceContext $deviceContext): DeviceContextProviderInterface
    {
        $provider = $this->createMock(DeviceContextProviderInterface::class);
        $provider->method('getDeviceContext')->willReturn($deviceContext);

        return $provider;
    }
}
