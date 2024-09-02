<?php

declare(strict_types=1);

namespace Dimkinthepro\JwtAuth\Tests\Feature\Application\UseCase;

use Dimkinthepro\JwtAuth\Application\UseCase\JwtToken\JwtTokenCreator;
use Dimkinthepro\JwtAuth\Application\UseCase\JwtToken\JwtTokenExtractor;
use Dimkinthepro\JwtAuth\Domain\Entity\JwtToken;
use Faker\Factory;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

class GenerateAndVerifyJwtTokenUseCasesTest extends KernelTestCase
{
    private JwtTokenCreator $jwtTokenGenerator;
    private JwtTokenExtractor $jwtTokenValidator;

    protected function setUp(): void
    {
        /** @var JwtTokenCreator $jwtTokenGenerator */
        $jwtTokenGenerator = self::getContainer()->get(JwtTokenCreator::class);
        $this->jwtTokenGenerator = $jwtTokenGenerator;

        /** @var JwtTokenExtractor $jwtTokenValidator */
        $jwtTokenValidator = self::getContainer()->get(JwtTokenExtractor::class);
        $this->jwtTokenValidator = $jwtTokenValidator;
    }

    public function testCase(): void
    {
        $email = Factory::create()->email();
        $jwtToken = $this->jwtTokenGenerator->create($email);
        $verifiedToken = $this->jwtTokenValidator->extract($jwtToken->getEncodedToken());

        self::assertInstanceOf(JwtToken::class, $jwtToken);
        self::assertInstanceOf(JwtToken::class, $verifiedToken);
    }
}
