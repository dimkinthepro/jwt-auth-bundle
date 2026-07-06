<?php

declare(strict_types=1);

namespace Dimkinthepro\JwtAuth\Tests\Unit\Application\Component\Manager;

use Dimkinthepro\JwtAuth\Application\Component\Encoder\JwtTokenEncoderInterface;
use Dimkinthepro\JwtAuth\Application\Component\Factory\JwtTokenFactoryInterface;
use Dimkinthepro\JwtAuth\Application\Component\Manager\JwtTokenManager;
use Dimkinthepro\JwtAuth\Application\Component\Provider\JwtTokenSignerInterface;
use Dimkinthepro\JwtAuth\Domain\Entity\JwtToken;
use Dimkinthepro\JwtAuth\Domain\Enum\AlgorithmEnum;
use Dimkinthepro\JwtAuth\Domain\Enum\TokenTypeEnum;
use Dimkinthepro\JwtAuth\Infrastructure\Factory\DateTimeFactory;
use Faker\Factory;
use PHPUnit\Framework\TestCase;

class JwtTokenManagerTest extends TestCase
{
    public function testCreateBuildsSignsAndEncodesToken(): void
    {
        $email = Factory::create()->email();
        $token = new JwtToken(
            AlgorithmEnum::RS256,
            TokenTypeEnum::JWT,
            $email,
            (new DateTimeFactory())->getNowDate(time()),
            (new DateTimeFactory())->getNowDate(time() + 3600)
        );

        $factory = $this->createMock(JwtTokenFactoryInterface::class);
        $factory->expects(self::once())->method('create')->with($email)->willReturn($token);

        $signer = $this->createMock(JwtTokenSignerInterface::class);
        $signer->expects(self::once())->method('sign')->with($token);

        $encoder = $this->createMock(JwtTokenEncoderInterface::class);
        $encoder->expects(self::once())->method('encode')->with($token);

        $manager = new JwtTokenManager($factory, $signer, $encoder);

        self::assertSame($token, $manager->create($email));
    }
}
