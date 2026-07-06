<?php

declare(strict_types=1);

namespace Dimkinthepro\JwtAuth\Tests\Unit\Application\UseCase\Session;

use Dimkinthepro\JwtAuth\Application\Component\Manager\RefreshTokenManager;
use Dimkinthepro\JwtAuth\Application\Component\Mapper\SessionDtoMapper;
use Dimkinthepro\JwtAuth\Application\DTO\SessionDto;
use Dimkinthepro\JwtAuth\Application\UseCase\Session\UserSessionsFetcher;
use Dimkinthepro\JwtAuth\Domain\Entity\RefreshToken;
use Faker\Factory;
use PHPUnit\Framework\TestCase;

class UserSessionsFetcherTest extends TestCase
{
    public function testSessionsAreExposedAsDtoWithoutTokenMaterial(): void
    {
        $email = Factory::create()->email();
        $sessionId = bin2hex(random_bytes(16));
        $validUntil = new \DateTimeImmutable('+1 month');
        $createdAt = new \DateTimeImmutable('-2 days');
        $lastUsedAt = new \DateTimeImmutable('-1 hour');

        $refreshToken = new RefreshToken(
            hash('sha256', 'raw-token'),
            $email,
            $validUntil,
            $sessionId,
            $createdAt,
            $lastUsedAt,
            'iPhone 13 Pro',
            'Mozilla/5.0',
            '203.0.113.10'
        );

        $manager = $this->createMock(RefreshTokenManager::class);
        $manager->method('findAllByUserIdentifier')->with($email)->willReturn([$refreshToken]);

        $sessions = (new UserSessionsFetcher($manager, new SessionDtoMapper()))->fetch($email);

        self::assertCount(1, $sessions);
        $session = $sessions[0];
        self::assertInstanceOf(SessionDto::class, $session);
        self::assertEquals($sessionId, $session->sessionId);
        self::assertEquals($email, $session->userIdentifier);
        self::assertEquals($validUntil, $session->validUntil);
        self::assertEquals($createdAt, $session->createdAt);
        self::assertEquals($lastUsedAt, $session->lastUsedAt);
        self::assertEquals('iPhone 13 Pro', $session->deviceName);
        self::assertEquals('Mozilla/5.0', $session->userAgent);
        self::assertEquals('203.0.113.10', $session->ip);
    }
}
