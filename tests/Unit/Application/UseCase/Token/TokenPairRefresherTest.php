<?php

declare(strict_types=1);

namespace Dimkinthepro\JwtAuth\Tests\Unit\Application\UseCase\Token;

use Dimkinthepro\JwtAuth\Application\Component\Decoder\RefreshTokenDecoderInterface;
use Dimkinthepro\JwtAuth\Application\Component\Manager\JwtTokenManagerInterface;
use Dimkinthepro\JwtAuth\Application\Component\Manager\RefreshTokenManagerInterface;
use Dimkinthepro\JwtAuth\Application\Component\Persistence\TransactionManagerInterface;
use Dimkinthepro\JwtAuth\Application\Component\Validator\RefreshTokenValidatorInterface;
use Dimkinthepro\JwtAuth\Application\UseCase\Token\TokenPairRefresher;
use Dimkinthepro\JwtAuth\Domain\Entity\JwtToken;
use Dimkinthepro\JwtAuth\Domain\Entity\RefreshToken;
use Dimkinthepro\JwtAuth\Domain\Enum\AlgorithmEnum;
use Dimkinthepro\JwtAuth\Domain\Enum\TokenTypeEnum;
use Dimkinthepro\JwtAuth\Infrastructure\Exception\RefreshTokenExpiredException;
use Dimkinthepro\JwtAuth\Infrastructure\Factory\DateTimeFactory;
use Faker\Factory;
use PHPUnit\Framework\TestCase;

class TokenPairRefresherTest extends TestCase
{
    public function testRefreshRotatesToken(): void
    {
        $email = Factory::create()->email();
        $rawToken = bin2hex(random_bytes(128));
        $oldToken = $this->createToken($email, time() + 3600);
        $newToken = $this->createToken($email, time() + 7200);
        $jwtToken = $this->createJwtToken($email);

        $manager = $this->createMock(RefreshTokenManagerInterface::class);
        $manager->expects(self::once())->method('findByToken')->with($rawToken)->willReturn($oldToken);
        $manager->expects(self::once())->method('rotate')->with($oldToken)->willReturn($newToken);
        $manager->expects(self::once())->method('delete')->with($oldToken);

        $jwtTokenManager = $this->createMock(JwtTokenManagerInterface::class);
        $jwtTokenManager
            ->expects(self::once())
            ->method('create')
            ->with($email, $newToken->getSessionId())
            ->willReturn($jwtToken);

        $refresher = new TokenPairRefresher(
            $jwtTokenManager,
            $this->createDecoder($rawToken),
            $manager,
            $this->createValidator(),
            $this->createTransactionManager()
        );

        $tokenPair = $refresher->getPairByRefreshToken(base64_encode($rawToken));

        self::assertSame($newToken, $tokenPair->refreshToken);
        self::assertSame($jwtToken, $tokenPair->token);
    }

    public function testExpiredTokenIsNotRotated(): void
    {
        $rawToken = bin2hex(random_bytes(128));
        $expiredToken = $this->createToken(Factory::create()->email(), time() - 3600);

        $manager = $this->createMock(RefreshTokenManagerInterface::class);
        $manager->method('findByToken')->with($rawToken)->willReturn($expiredToken);
        $manager->expects(self::never())->method('rotate');
        $manager->expects(self::never())->method('delete');

        $validator = $this->createMock(RefreshTokenValidatorInterface::class);
        $validator->method('validate')->willThrowException(new RefreshTokenExpiredException('expired'));

        $jwtTokenManager = $this->createMock(JwtTokenManagerInterface::class);
        $jwtTokenManager->expects(self::never())->method('create');

        $refresher = new TokenPairRefresher(
            $jwtTokenManager,
            $this->createDecoder($rawToken),
            $manager,
            $validator,
            $this->createTransactionManager()
        );

        self::expectException(RefreshTokenExpiredException::class);

        $refresher->getPairByRefreshToken(base64_encode($rawToken));
    }

    private function createToken(string $email, int $validUntilTimestamp): RefreshToken
    {
        return new RefreshToken(
            hash('sha256', bin2hex(random_bytes(128))),
            $email,
            (new \DateTimeImmutable())->setTimestamp($validUntilTimestamp),
            bin2hex(random_bytes(16)),
            new \DateTimeImmutable('-1 minute'),
            new \DateTimeImmutable()
        );
    }

    private function createJwtToken(string $email): JwtToken
    {
        return new JwtToken(
            AlgorithmEnum::RS256,
            TokenTypeEnum::JWT,
            $email,
            (new DateTimeFactory())->getNowDate(time()),
            (new DateTimeFactory())->getNowDate(time() + 3600)
        );
    }

    private function createDecoder(string $rawToken): RefreshTokenDecoderInterface
    {
        $decoder = $this->createMock(RefreshTokenDecoderInterface::class);
        $decoder->method('decode')->with(base64_encode($rawToken))->willReturn($rawToken);

        return $decoder;
    }

    private function createValidator(): RefreshTokenValidatorInterface
    {
        return $this->createMock(RefreshTokenValidatorInterface::class);
    }

    private function createTransactionManager(): TransactionManagerInterface
    {
        $transactionManager = $this->createMock(TransactionManagerInterface::class);
        $transactionManager
            ->expects(self::once())
            ->method('transactional')
            ->willReturnCallback(fn (\Closure $operation): mixed => $operation());

        return $transactionManager;
    }
}
