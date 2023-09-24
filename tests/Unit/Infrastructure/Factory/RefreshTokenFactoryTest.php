<?php

declare(strict_types=1);

namespace Dimkinthepro\JwtAuth\Tests\Unit\Infrastructure\Factory;

use Dimkinthepro\JwtAuth\Application\Component\Provider\RefreshTokenGeneratorInterface;
use Dimkinthepro\JwtAuth\Domain\Entity\RefreshToken;
use Dimkinthepro\JwtAuth\Infrastructure\Factory\DateTimeFactory;
use Dimkinthepro\JwtAuth\Infrastructure\Factory\RefreshTokenFactory;
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
        self::assertEquals($tokenValue, $token->getToken());
        $validDate = (new \DateTimeImmutable())->setTimestamp(time() + $ttl);
        self::assertEquals($validDate, $token->getValidUntil());
    }

    private function getFactory(int $ttl, string $token): RefreshTokenFactory
    {
        return new RefreshTokenFactory($ttl, $this->getRefreshTokenGeneratorService($token), new DateTimeFactory());
    }

    private function getRefreshTokenGeneratorService(string $token): RefreshTokenGeneratorInterface
    {
        $generator = $this->createMock(RefreshTokenGeneratorInterface::class);
        $generator->method('generate')->willReturn($token);

        return $generator;
    }
}
