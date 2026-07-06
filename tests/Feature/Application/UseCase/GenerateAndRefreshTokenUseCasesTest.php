<?php

declare(strict_types=1);

namespace Dimkinthepro\JwtAuth\Tests\Feature\Application\UseCase;

use Dimkinthepro\JwtAuth\Application\UseCase\RefreshToken\RefreshTokenCreator;
use Dimkinthepro\JwtAuth\Application\UseCase\RefreshToken\RefreshTokenRefresher;
use Dimkinthepro\JwtAuth\Infrastructure\Exception\RefreshTokenNotFoundException;
use Dimkinthepro\JwtAuth\Tests\Feature\ResetDatabaseTrait;
use Faker\Factory;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

class GenerateAndRefreshTokenUseCasesTest extends KernelTestCase
{
    use ResetDatabaseTrait;

    private RefreshTokenCreator $refreshTokenCreator;
    private RefreshTokenRefresher $refreshTokenRefresher;

    protected function setUp(): void
    {
        /** @var RefreshTokenCreator $refreshTokenCreator */
        $refreshTokenCreator = self::getContainer()->get(RefreshTokenCreator::class);
        $this->refreshTokenCreator = $refreshTokenCreator;

        /** @var RefreshTokenRefresher $refreshTokenRefresher */
        $refreshTokenRefresher = self::getContainer()->get(RefreshTokenRefresher::class);
        $this->refreshTokenRefresher = $refreshTokenRefresher;

        $this->resetDatabase();
    }

    public function testRefreshRotatesToken(): void
    {
        $email = Factory::create()->email();

        $refreshToken = $this->refreshTokenCreator->create($email);
        $refreshedToken = $this->refreshTokenRefresher->refresh($refreshToken->getEncodedToken());

        self::assertEquals($email, $refreshedToken->getUserIdentifier());
        self::assertNotEquals($refreshToken->getEncodedToken(), $refreshedToken->getEncodedToken());
        // The device session identity survives rotation
        self::assertEquals($refreshToken->getSessionId(), $refreshedToken->getSessionId());
        self::assertEquals($refreshToken->getCreatedAt(), $refreshedToken->getCreatedAt());
        self::assertGreaterThanOrEqual($refreshToken->getLastUsedAt(), $refreshedToken->getLastUsedAt());
    }

    public function testRotatedTokenCannotBeReused(): void
    {
        $refreshToken = $this->refreshTokenCreator->create(Factory::create()->email());
        $this->refreshTokenRefresher->refresh($refreshToken->getEncodedToken());

        self::expectException(RefreshTokenNotFoundException::class);

        $this->refreshTokenRefresher->refresh($refreshToken->getEncodedToken());
    }
}
