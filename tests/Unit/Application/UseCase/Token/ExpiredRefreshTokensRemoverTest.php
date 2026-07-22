<?php

declare(strict_types=1);

namespace Dimkinthepro\JwtAuth\Tests\Unit\Application\UseCase\Token;

use Dimkinthepro\JwtAuth\Application\Component\Manager\RefreshTokenManagerInterface;
use Dimkinthepro\JwtAuth\Application\UseCase\Token\ExpiredRefreshTokensRemover;
use Dimkinthepro\JwtAuth\Infrastructure\Factory\DateTimeFactory;
use PHPUnit\Framework\TestCase;

class ExpiredRefreshTokensRemoverTest extends TestCase
{
    private const CUTOFF_TOLERANCE_SECONDS = 5;

    public function testRemoveDeletesTokensExpiredBeforeNow(): void
    {
        $deletedCount = 3;

        $manager = $this->createMock(RefreshTokenManagerInterface::class);
        $manager
            ->expects(self::once())
            ->method('deleteExpiredTokens')
            ->with(self::callback(
                static fn (\DateTimeImmutable $expiredBefore): bool => abs($expiredBefore->getTimestamp() - time()) <= self::CUTOFF_TOLERANCE_SECONDS
            ))
            ->willReturn($deletedCount);

        $remover = new ExpiredRefreshTokensRemover($manager, new DateTimeFactory());

        self::assertEquals($deletedCount, $remover->removeExpiredTokens());
    }
}
