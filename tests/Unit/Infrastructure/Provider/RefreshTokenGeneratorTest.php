<?php

declare(strict_types=1);

namespace Dimkinthepro\JwtAuth\Tests\Unit\Infrastructure\Provider;

use Dimkinthepro\JwtAuth\Infrastructure\Provider\RefreshTokenGenerator;
use Faker\Factory;
use PHPUnit\Framework\TestCase;

class RefreshTokenGeneratorTest extends TestCase
{
    public function testGeneratesHexTokenOfConfiguredLength(): void
    {
        $length = Factory::create()->numberBetween(16, 128);

        $token = (new RefreshTokenGenerator($length))->generate();

        self::assertEquals($length * 2, \strlen($token));
        self::assertMatchesRegularExpression('/^[0-9a-f]+$/', $token);
    }

    public function testGeneratesUniqueTokens(): void
    {
        $generator = new RefreshTokenGenerator(64);

        self::assertNotEquals($generator->generate(), $generator->generate());
    }
}
