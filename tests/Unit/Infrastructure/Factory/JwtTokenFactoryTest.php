<?php

declare(strict_types=1);

namespace Dimkinthepro\JwtAuth\Tests\Unit\Infrastructure\Factory;

use Dimkinthepro\JwtAuth\Application\Component\Event\JwtTokenCreatedEvent;
use Dimkinthepro\JwtAuth\Domain\Entity\JwtToken;
use Dimkinthepro\JwtAuth\Domain\Enum\AlgorithmEnum;
use Dimkinthepro\JwtAuth\Domain\Enum\TokenDictionaryEnum;
use Dimkinthepro\JwtAuth\Domain\Enum\TokenTypeEnum;
use Dimkinthepro\JwtAuth\Infrastructure\Factory\DateTimeFactory;
use Dimkinthepro\JwtAuth\Infrastructure\Factory\JwtTokenFactory;
use Faker\Factory;
use PHPUnit\Framework\TestCase;
use Symfony\Component\EventDispatcher\EventDispatcher;

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

    public function testReservedClaimsCannotBeOverriddenByListeners(): void
    {
        $eventDispatcher = new EventDispatcher();
        $eventDispatcher->addListener(JwtTokenCreatedEvent::class, static function (JwtTokenCreatedEvent $event): void {
            $event->setClaims([
                TokenDictionaryEnum::IDENTIFIER->value => 'attacker@example.com',
                TokenDictionaryEnum::EXPIRED_AT->value => \PHP_INT_MAX,
            ]);
        });

        $email = Factory::create()->email();
        $factory = new JwtTokenFactory(AlgorithmEnum::RS256->value, 3600, new DateTimeFactory(), $eventDispatcher);

        $token = $factory->create($email);
        $payload = $token->getPayload();

        self::assertEquals($email, $payload[TokenDictionaryEnum::IDENTIFIER->value]);
        self::assertNotEquals(\PHP_INT_MAX, $payload[TokenDictionaryEnum::EXPIRED_AT->value]);
    }

    public function testCreatedEventExposesStandardClaimsToListeners(): void
    {
        $seenClaims = null;
        $eventDispatcher = new EventDispatcher();
        $eventDispatcher->addListener(
            JwtTokenCreatedEvent::class,
            static function (JwtTokenCreatedEvent $event) use (&$seenClaims): void {
                $seenClaims = $event->getClaims();
            }
        );

        $factory = new JwtTokenFactory(
            AlgorithmEnum::RS256->value,
            3600,
            new DateTimeFactory(),
            $eventDispatcher,
            'my-app'
        );
        $factory->create(Factory::create()->email());

        self::assertArrayHasKey(TokenDictionaryEnum::TOKEN_ID->value, $seenClaims);
        self::assertArrayHasKey(TokenDictionaryEnum::NOT_BEFORE->value, $seenClaims);
        self::assertEquals('my-app', $seenClaims[TokenDictionaryEnum::ISSUER->value]);
    }

    public function testCreateTokenSetsStandardClaims(): void
    {
        $factory = new JwtTokenFactory(
            AlgorithmEnum::RS256->value,
            3600,
            new DateTimeFactory(),
            new EventDispatcher(),
            'my-app',
            'web-client'
        );

        $token = $factory->create(Factory::create()->email());
        $payload = $token->getPayload();

        self::assertMatchesRegularExpression('/^[0-9a-f]{32}$/', $payload[TokenDictionaryEnum::TOKEN_ID->value]);
        self::assertEquals($payload[TokenDictionaryEnum::ISSUED_AT->value], $payload[TokenDictionaryEnum::NOT_BEFORE->value]);
        self::assertEquals('my-app', $payload[TokenDictionaryEnum::ISSUER->value]);
        self::assertEquals('web-client', $payload[TokenDictionaryEnum::AUDIENCE->value]);
    }

    public function testIssuerAndAudienceClaimsAreOmittedWhenNotConfigured(): void
    {
        $token = $this->getFactory(AlgorithmEnum::RS256, 3600)->create(Factory::create()->email());
        $payload = $token->getPayload();

        self::assertArrayNotHasKey(TokenDictionaryEnum::ISSUER->value, $payload);
        self::assertArrayNotHasKey(TokenDictionaryEnum::AUDIENCE->value, $payload);
        self::assertArrayHasKey(TokenDictionaryEnum::TOKEN_ID->value, $payload);
        self::assertArrayHasKey(TokenDictionaryEnum::NOT_BEFORE->value, $payload);
    }

    public function testSessionIdClaimIsSetWhenProvided(): void
    {
        $sessionId = bin2hex(random_bytes(16));
        $factory = $this->getFactory(AlgorithmEnum::RS256, 3600);

        $tokenWithSession = $factory->create(Factory::create()->email(), $sessionId);
        $tokenWithoutSession = $factory->create(Factory::create()->email());

        self::assertEquals($sessionId, $tokenWithSession->getClaim(TokenDictionaryEnum::SESSION_ID->value));
        self::assertNull($tokenWithoutSession->getClaim(TokenDictionaryEnum::SESSION_ID->value));
    }

    public function testTokenIdIsUniquePerToken(): void
    {
        $factory = $this->getFactory(AlgorithmEnum::RS256, 3600);

        self::assertNotEquals(
            $factory->create(Factory::create()->email())->getClaim(TokenDictionaryEnum::TOKEN_ID->value),
            $factory->create(Factory::create()->email())->getClaim(TokenDictionaryEnum::TOKEN_ID->value)
        );
    }

    public function testCreatedEventListenerCanModifyClaims(): void
    {
        $eventDispatcher = new EventDispatcher();
        $eventDispatcher->addListener(JwtTokenCreatedEvent::class, static function (JwtTokenCreatedEvent $event): void {
            $claims = $event->getClaims();
            $claims['addedByListener'] = $event->getUserIdentifier();
            $event->setClaims($claims);
        });

        $email = Factory::create()->email();
        $factory = new JwtTokenFactory(AlgorithmEnum::RS256->value, 3600, new DateTimeFactory(), $eventDispatcher);

        $token = $factory->create($email);

        self::assertEquals($email, $token->getClaim('addedByListener'));
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
        return new JwtTokenFactory($algorithm->value, $ttl, new DateTimeFactory(), new EventDispatcher());
    }
}
