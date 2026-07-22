<?php

declare(strict_types=1);

namespace Dimkinthepro\JwtAuth\Tests\Feature\Application\UseCase;

use Dimkinthepro\JwtAuth\Application\Component\Manager\RefreshTokenManager;
use Dimkinthepro\JwtAuth\Application\UseCase\Token\TokenPairRefresher;
use Dimkinthepro\JwtAuth\Infrastructure\Exception\RefreshTokenNotFoundException;
use Dimkinthepro\JwtAuth\Tests\Feature\ResetDatabaseTrait;
use Faker\Factory;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

class GenerateAndRefreshTokenUseCasesTest extends KernelTestCase
{
    use ResetDatabaseTrait;

    private RefreshTokenManager $refreshTokenCreator;
    private TokenPairRefresher $tokenPairRefresher;

    protected function setUp(): void
    {
        /** @var RefreshTokenManager $refreshTokenCreator */
        $refreshTokenCreator = self::getContainer()->get(RefreshTokenManager::class);
        $this->refreshTokenCreator = $refreshTokenCreator;

        /** @var TokenPairRefresher $tokenPairRefresher */
        $tokenPairRefresher = self::getContainer()->get(TokenPairRefresher::class);
        $this->tokenPairRefresher = $tokenPairRefresher;

        $this->resetDatabase();
    }

    public function testRefreshRotatesToken(): void
    {
        $email = Factory::create()->email();

        $refreshToken = $this->refreshTokenCreator->create($email);
        $refreshedToken = $this->tokenPairRefresher
            ->getPairByRefreshToken($refreshToken->getEncodedToken())
            ->refreshToken;

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
        $this->tokenPairRefresher->getPairByRefreshToken($refreshToken->getEncodedToken());

        self::expectException(RefreshTokenNotFoundException::class);

        $this->tokenPairRefresher->getPairByRefreshToken($refreshToken->getEncodedToken());
    }
}
