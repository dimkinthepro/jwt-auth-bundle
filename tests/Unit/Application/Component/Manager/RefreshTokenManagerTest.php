<?php

declare(strict_types=1);

namespace Dimkinthepro\JwtAuth\Tests\Unit\Application\Component\Manager;

use Dimkinthepro\JwtAuth\Application\Component\Factory\RefreshTokenFactoryInterface;
use Dimkinthepro\JwtAuth\Application\Component\Manager\RefreshTokenManager;
use Dimkinthepro\JwtAuth\Application\Component\Provider\DeviceContextProviderInterface;
use Dimkinthepro\JwtAuth\Application\Component\Repository\RefreshTokenReadRepositoryInterface;
use Dimkinthepro\JwtAuth\Application\Component\Repository\RefreshTokenWriteRepositoryInterface;
use Dimkinthepro\JwtAuth\Domain\Entity\RefreshToken;
use Dimkinthepro\JwtAuth\Domain\ValueObject\DeviceContext;
use Dimkinthepro\JwtAuth\Infrastructure\Decoder\Base64FieldsDecoder;
use Dimkinthepro\JwtAuth\Infrastructure\Encoder\Base64FieldsEncoder;
use Dimkinthepro\JwtAuth\Infrastructure\Exception\RefreshTokenNotFoundException;
use Dimkinthepro\JwtAuth\Infrastructure\Factory\DateTimeFactory;
use Dimkinthepro\JwtAuth\Infrastructure\Factory\RefreshTokenFactory;
use Dimkinthepro\JwtAuth\Infrastructure\Hasher\RefreshTokenHasher;
use Dimkinthepro\JwtAuth\Infrastructure\Provider\RefreshTokenGenerator;
use Faker\Factory;
use PHPUnit\Framework\TestCase;

class RefreshTokenManagerTest extends TestCase
{
    private const TOKEN_TTL = 3600;
    private const TOKEN_LENGTH = 128;

    /** @var array<string, RefreshToken> */
    private array $storage = [];

    protected function setUp(): void
    {
        $this->storage = [];
    }

    public function testCreatePersistsHashAndExposesRawTokenToClient(): void
    {
        $email = Factory::create()->email();
        $manager = $this->createManager();

        $refreshToken = $manager->create($email);

        $rawToken = (new Base64FieldsDecoder())->decode($refreshToken->getEncodedToken());
        self::assertCount(1, $this->storage);
        self::assertArrayHasKey(hash('sha256', $rawToken), $this->storage);
        self::assertEquals(hash('sha256', $rawToken), $refreshToken->getTokenHash());
        self::assertEquals($email, $refreshToken->getUserIdentifier());
    }

    public function testFindByTokenLooksUpByHashOfRawToken(): void
    {
        $manager = $this->createManager();
        $created = $manager->create(Factory::create()->email());
        $rawToken = (new Base64FieldsDecoder())->decode($created->getEncodedToken());

        $found = $manager->findByToken($rawToken);

        self::assertSame($created, $found);
    }

    public function testFindByUnknownTokenThrowsException(): void
    {
        $manager = $this->createManager();

        self::expectException(RefreshTokenNotFoundException::class);

        $manager->findByToken(bin2hex(random_bytes(self::TOKEN_LENGTH)));
    }

    public function testDeleteRemovesToken(): void
    {
        $manager = $this->createManager();
        $created = $manager->create(Factory::create()->email());

        $manager->delete($created);

        self::assertCount(0, $this->storage);
    }

    public function testRevokeSessionDeletesOwnSession(): void
    {
        $email = Factory::create()->email();
        $manager = $this->createManager();
        $created = $manager->create($email);

        $manager->revokeSession($created->getSessionId(), $email);

        self::assertCount(0, $this->storage);
    }

    public function testForeignSessionCannotBeRevoked(): void
    {
        $manager = $this->createManager();
        $created = $manager->create(Factory::create()->email());

        self::expectException(RefreshTokenNotFoundException::class);

        $manager->revokeSession($created->getSessionId(), 'attacker@example.com');
    }

    public function testCreateFailsLoudlyWhenUniqueTokenCannotBeGenerated(): void
    {
        $collision = new RefreshToken(
            hash('sha256', 'collision'),
            Factory::create()->email(),
            new \DateTimeImmutable('+1 hour'),
            bin2hex(random_bytes(16)),
            new \DateTimeImmutable('-1 minute'),
            new \DateTimeImmutable()
        );

        $factory = $this->createMock(RefreshTokenFactoryInterface::class);
        $factory->expects(self::exactly(3))->method('create')->willReturn($collision);

        $readRepository = $this->createMock(RefreshTokenReadRepositoryInterface::class);
        $readRepository->method('findByTokenHash')->willReturn($collision);

        $manager = new RefreshTokenManager(
            $factory,
            new RefreshTokenHasher(),
            $readRepository,
            $this->createWriteRepository()
        );

        self::expectException(\RuntimeException::class);
        self::expectExceptionMessage('Failed to generate a unique refresh token after 3 attempts');

        $manager->create(Factory::create()->email());
    }

    private function createManager(): RefreshTokenManager
    {
        $deviceContextProvider = $this->createMock(DeviceContextProviderInterface::class);
        $deviceContextProvider->method('getDeviceContext')->willReturn(new DeviceContext());

        $factory = new RefreshTokenFactory(
            self::TOKEN_TTL,
            new RefreshTokenGenerator(self::TOKEN_LENGTH),
            new RefreshTokenHasher(),
            new Base64FieldsEncoder(),
            new DateTimeFactory(),
            $deviceContextProvider
        );

        return new RefreshTokenManager(
            $factory,
            new RefreshTokenHasher(),
            $this->createReadRepository(),
            $this->createWriteRepository()
        );
    }

    private function createReadRepository(): RefreshTokenReadRepositoryInterface
    {
        $repository = $this->createMock(RefreshTokenReadRepositoryInterface::class);
        $repository->method('findByTokenHash')->willReturnCallback(
            fn (string $tokenHash): ?RefreshToken => $this->storage[$tokenHash] ?? null
        );
        $repository->method('findByTokenHashOrThrowException')->willReturnCallback(
            fn (string $tokenHash): RefreshToken => $this->storage[$tokenHash]
                ?? throw new RefreshTokenNotFoundException('Refresh token not found')
        );
        $repository->method('findBySessionId')->willReturnCallback(
            function (string $sessionId): ?RefreshToken {
                foreach ($this->storage as $token) {
                    if ($token->getSessionId() === $sessionId) {
                        return $token;
                    }
                }

                return null;
            }
        );

        return $repository;
    }

    private function createWriteRepository(): RefreshTokenWriteRepositoryInterface
    {
        $repository = $this->createMock(RefreshTokenWriteRepositoryInterface::class);
        $repository->method('save')->willReturnCallback(
            function (RefreshToken $token): void {
                $this->storage[$token->getTokenHash()] = $token;
            }
        );
        $repository->method('delete')->willReturnCallback(
            function (RefreshToken $token): void {
                unset($this->storage[$token->getTokenHash()]);
            }
        );

        return $repository;
    }
}
