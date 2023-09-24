<?php

declare(strict_types=1);

namespace Dimkinthepro\JwtAuth\Tests\Feature\Application\UseCase;

use Dimkinthepro\JwtAuth\Application\UseCase\RefreshToken\RefreshTokenCreator;
use Dimkinthepro\JwtAuth\Application\UseCase\RefreshToken\RefreshTokenRefresher;
use Dimkinthepro\JwtAuth\Domain\Entity\RefreshToken;
use Faker\Factory;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

class GenerateAndRefreshTokenUseCasesTest extends KernelTestCase
{
    private RefreshTokenCreator $refreshTokenGenerator;
    private RefreshTokenRefresher $refreshTokenRefresher;

    protected function setUp(): void
    {
        /** @var RefreshTokenCreator $refreshTokenGenerator */
        $refreshTokenGenerator = self::getContainer()->get(RefreshTokenCreator::class);
        $this->refreshTokenGenerator = $refreshTokenGenerator;

        /** @var RefreshTokenRefresher $refreshTokenRefresher */
        $refreshTokenRefresher = self::getContainer()->get(RefreshTokenRefresher::class);
        $this->refreshTokenRefresher = $refreshTokenRefresher;
    }

    public function testCase(): void
    {
        $email = Factory::create()->email();
        $refreshToken = $this->refreshTokenGenerator->create($email);
        $refreshedToken = $this->refreshTokenRefresher->refresh($refreshToken->getEncodedToken());

        self::assertInstanceOf(RefreshToken::class, $refreshToken);
        self::assertInstanceOf(RefreshToken::class, $refreshedToken);
    }
}
