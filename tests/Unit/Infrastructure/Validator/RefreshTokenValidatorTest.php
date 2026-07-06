<?php

declare(strict_types=1);

namespace Dimkinthepro\JwtAuth\Tests\Unit\Infrastructure\Validator;

use Dimkinthepro\JwtAuth\Domain\Entity\RefreshToken;
use Dimkinthepro\JwtAuth\Infrastructure\Exception\RefreshTokenExpiredException;
use Dimkinthepro\JwtAuth\Infrastructure\Factory\DateTimeFactory;
use Dimkinthepro\JwtAuth\Infrastructure\Validator\RefreshTokenValidator;
use Faker\Factory;
use PHPUnit\Framework\TestCase;

class RefreshTokenValidatorTest extends TestCase
{
    public function testActiveTokenPassesValidation(): void
    {
        $token = $this->createToken(time() + 3600);

        (new RefreshTokenValidator(new DateTimeFactory()))->validate($token);

        self::assertGreaterThan(new \DateTimeImmutable(), $token->getValidUntil());
    }

    public function testExpiredTokenIsRejected(): void
    {
        $token = $this->createToken(time() - 3600);

        self::expectException(RefreshTokenExpiredException::class);

        (new RefreshTokenValidator(new DateTimeFactory()))->validate($token);
    }

    private function createToken(int $validUntilTimestamp): RefreshToken
    {
        return new RefreshToken(
            hash('sha256', Factory::create()->password()),
            Factory::create()->email(),
            (new \DateTimeImmutable())->setTimestamp($validUntilTimestamp),
            bin2hex(random_bytes(16)),
            new \DateTimeImmutable('-1 minute'),
            new \DateTimeImmutable()
        );
    }
}
