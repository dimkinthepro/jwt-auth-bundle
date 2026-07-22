<?php

declare(strict_types=1);

namespace Dimkinthepro\JwtAuth\Tests\Feature\Application\UseCase;

use Dimkinthepro\JwtAuth\Application\Component\Manager\JwtTokenManager;
use Dimkinthepro\JwtAuth\Application\UseCase\Token\JwtTokenDecoder;
use Dimkinthepro\JwtAuth\Domain\Enum\TokenDictionaryEnum;
use Dimkinthepro\JwtAuth\Tests\Feature\EventListener\TestCustomClaimsListener;
use Faker\Factory;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

class GenerateAndVerifyJwtTokenUseCasesTest extends KernelTestCase
{
    private JwtTokenManager $jwtTokenCreator;
    private JwtTokenDecoder $jwtTokenExtractor;

    protected function setUp(): void
    {
        /** @var JwtTokenManager $jwtTokenCreator */
        $jwtTokenCreator = self::getContainer()->get(JwtTokenManager::class);
        $this->jwtTokenCreator = $jwtTokenCreator;

        /** @var JwtTokenDecoder $jwtTokenExtractor */
        $jwtTokenExtractor = self::getContainer()->get(JwtTokenDecoder::class);
        $this->jwtTokenExtractor = $jwtTokenExtractor;
    }

    public function testCreatedTokenPassesVerification(): void
    {
        $email = Factory::create()->email();

        // The test app enables the blocklist, and it requires a session id claim in every token
        $jwtToken = $this->jwtTokenCreator->create($email, bin2hex(random_bytes(16)));
        $verifiedToken = $this->jwtTokenExtractor->decodeTokenFromString($jwtToken->getEncodedToken());

        self::assertEquals($email, $verifiedToken->getUserIdentifier());
        self::assertEquals($jwtToken->getAlgorithm(), $verifiedToken->getAlgorithm());
        self::assertEquals(
            $jwtToken->getExpiredAt()->getTimestamp(),
            $verifiedToken->getExpiredAt()->getTimestamp()
        );
        // TestCustomClaimsListener is registered in the test app and adds the claim via JwtTokenCreatedEvent
        self::assertEquals(
            TestCustomClaimsListener::ROLE_VALUE,
            $verifiedToken->getClaim(TestCustomClaimsListener::ROLE_CLAIM)
        );

        // Standard claims configured in config/packages/dimkinthepro_jwt_auth.yaml
        self::assertEquals('jwt-auth-test', $verifiedToken->getClaim(TokenDictionaryEnum::ISSUER->value));
        self::assertEquals('jwt-auth-test-client', $verifiedToken->getClaim(TokenDictionaryEnum::AUDIENCE->value));
        self::assertNotEmpty($verifiedToken->getClaim(TokenDictionaryEnum::TOKEN_ID->value));
        self::assertNotEmpty($verifiedToken->getClaim(TokenDictionaryEnum::NOT_BEFORE->value));
    }
}
