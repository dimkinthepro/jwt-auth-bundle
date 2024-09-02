<?php

declare(strict_types=1);

namespace Dimkinthepro\JwtAuth\Tests\Unit\Infrastructure\Factory;

use Dimkinthepro\JwtAuth\Domain\Entity\JwtToken;
use Dimkinthepro\JwtAuth\Domain\Enum\AlgorithmEnum;
use Dimkinthepro\JwtAuth\Domain\Enum\TokenDictionaryEnum;
use Dimkinthepro\JwtAuth\Domain\Enum\TokenTypeEnum;
use Dimkinthepro\JwtAuth\Infrastructure\Factory\DateTimeFactory;
use Dimkinthepro\JwtAuth\Infrastructure\Factory\JwtTokenFactory;
use Faker\Factory;
use PHPUnit\Framework\TestCase;

class JwtTokenFactoryTest extends TestCase
{
    /**
     * @dataProvider providerTestCreateToken
     */
    public function testCreateToken(
        AlgorithmEnum $algorithm
    ): void {
        $ttl = rand();
        $email = Factory::create()->email();

        $factory = $this->getFactory($algorithm, $ttl);
        $token = $factory->create($email);

        self::assertInstanceOf(JwtToken::class, $token);
        self::assertEquals($algorithm->value, $token->getHeader()[TokenDictionaryEnum::ALGORITHM->value]);
        self::assertEquals(TokenTypeEnum::JWT->value, $token->getHeader()[TokenDictionaryEnum::TYPE->value]);
        self::assertEquals($email, $token->getPayload()[TokenDictionaryEnum::IDENTIFIER->value]);
        $tokenTtl = $token->getPayload()[TokenDictionaryEnum::EXPIRED_AT->value]
            - $token->getPayload()[TokenDictionaryEnum::ISSUED_AT->value];
        self::assertEquals($ttl, $tokenTtl);
    }

    public function providerTestCreateToken(): array
    {
        $result = [];
        foreach (AlgorithmEnum::cases() as $algorithm) {
            $result[] = [$algorithm];
        }

        return $result;
    }

    private function getFactory(AlgorithmEnum $algorithm, int $ttl): JwtTokenFactory
    {
        return new JwtTokenFactory($algorithm->value, $ttl, new DateTimeFactory());
    }
}
