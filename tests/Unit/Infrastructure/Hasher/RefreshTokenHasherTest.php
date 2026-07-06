<?php

declare(strict_types=1);

namespace Dimkinthepro\JwtAuth\Tests\Unit\Infrastructure\Hasher;

use Dimkinthepro\JwtAuth\Infrastructure\Hasher\RefreshTokenHasher;
use Faker\Factory;
use PHPUnit\Framework\TestCase;

class RefreshTokenHasherTest extends TestCase
{
    public function testHashIsDeterministicSha256(): void
    {
        $rawToken = bin2hex(random_bytes(128));
        $hasher = new RefreshTokenHasher();

        $hash = $hasher->hash($rawToken);

        self::assertEquals(hash('sha256', $rawToken), $hash);
        self::assertEquals($hash, $hasher->hash($rawToken));
        self::assertNotEquals($rawToken, $hash);
    }

    public function testDifferentTokensProduceDifferentHashes(): void
    {
        $hasher = new RefreshTokenHasher();

        self::assertNotEquals(
            $hasher->hash(Factory::create()->password() . '1'),
            $hasher->hash(Factory::create()->password() . '2')
        );
    }
}
