<?php

declare(strict_types=1);

namespace Dimkinthepro\JwtAuth\Tests\Unit\Application\UseCase\Token;

use Dimkinthepro\JwtAuth\Application\Component\Manager\JwtTokenManagerInterface;
use Dimkinthepro\JwtAuth\Application\Component\Manager\RefreshTokenManagerInterface;
use Dimkinthepro\JwtAuth\Application\UseCase\Token\TokenPairCreator;
use Dimkinthepro\JwtAuth\Domain\Entity\JwtToken;
use Dimkinthepro\JwtAuth\Domain\Entity\RefreshToken;
use Dimkinthepro\JwtAuth\Domain\Enum\AlgorithmEnum;
use Dimkinthepro\JwtAuth\Domain\Enum\TokenTypeEnum;
use Dimkinthepro\JwtAuth\Infrastructure\Factory\DateTimeFactory;
use Faker\Factory;
use PHPUnit\Framework\TestCase;

class TokenPairCreatorTest extends TestCase
{
    public function testCreateBuildsPairWithSessionIdFromRefreshToken(): void
    {
        $email = Factory::create()->email();
        $refreshToken = $this->createRefreshToken($email);
        $jwtToken = $this->createJwtToken($email);

        $refreshTokenManager = $this->createMock(RefreshTokenManagerInterface::class);
        $refreshTokenManager
            ->expects(self::once())
            ->method('create')
            ->with($email)
            ->willReturn($refreshToken);

        // The JWT must carry the session id of the refresh token in the "sid" claim
        $jwtTokenManager = $this->createMock(JwtTokenManagerInterface::class);
        $jwtTokenManager
            ->expects(self::once())
            ->method('create')
            ->with($email, $refreshToken->getSessionId())
            ->willReturn($jwtToken);

        $creator = new TokenPairCreator($jwtTokenManager, $refreshTokenManager);

        $tokenPair = $creator->create($email);

        self::assertSame($jwtToken, $tokenPair->token);
        self::assertSame($refreshToken, $tokenPair->refreshToken);
    }

    private function createRefreshToken(string $email): RefreshToken
    {
        return new RefreshToken(
            hash('sha256', bin2hex(random_bytes(128))),
            $email,
            new \DateTimeImmutable('+1 month'),
            bin2hex(random_bytes(16)),
            new \DateTimeImmutable(),
            new \DateTimeImmutable()
        );
    }

    private function createJwtToken(string $email): JwtToken
    {
        return new JwtToken(
            AlgorithmEnum::RS256,
            TokenTypeEnum::JWT,
            $email,
            (new DateTimeFactory())->getNowDate(time()),
            (new DateTimeFactory())->getNowDate(time() + 3600)
        );
    }
}
