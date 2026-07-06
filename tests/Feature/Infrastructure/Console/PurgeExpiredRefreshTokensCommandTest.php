<?php

declare(strict_types=1);

namespace Dimkinthepro\JwtAuth\Tests\Feature\Infrastructure\Console;

use Dimkinthepro\JwtAuth\Application\Component\Repository\RefreshTokenReadRepositoryInterface;
use Dimkinthepro\JwtAuth\Application\Component\Repository\RefreshTokenWriteRepositoryInterface;
use Dimkinthepro\JwtAuth\Domain\Entity\RefreshToken;
use Dimkinthepro\JwtAuth\Infrastructure\Console\PurgeExpiredRefreshTokensCommand;
use Dimkinthepro\JwtAuth\Tests\Feature\ResetDatabaseTrait;
use Faker\Factory;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Tester\CommandTester;

class PurgeExpiredRefreshTokensCommandTest extends KernelTestCase
{
    use ResetDatabaseTrait;

    public function testPurgeDeletesOnlyExpiredTokens(): void
    {
        $this->resetDatabase();

        /** @var RefreshTokenWriteRepositoryInterface $writeRepository */
        $writeRepository = self::getContainer()->get(RefreshTokenWriteRepositoryInterface::class);
        $expiredToken = $this->createToken(new \DateTimeImmutable('-1 hour'));
        $activeToken = $this->createToken(new \DateTimeImmutable('+1 hour'));
        $writeRepository->save($expiredToken);
        $writeRepository->save($activeToken);

        /** @var PurgeExpiredRefreshTokensCommand $command */
        $command = self::getContainer()->get(PurgeExpiredRefreshTokensCommand::class);
        $commandTester = new CommandTester($command);
        $exitCode = $commandTester->execute([]);

        /** @var RefreshTokenReadRepositoryInterface $readRepository */
        $readRepository = self::getContainer()->get(RefreshTokenReadRepositoryInterface::class);

        self::assertEquals(Command::SUCCESS, $exitCode);
        self::assertStringContainsString('Deleted 1 expired refresh token(s).', $commandTester->getDisplay());
        self::assertNull($readRepository->findByTokenHash($expiredToken->getTokenHash()));
        self::assertNotNull($readRepository->findByTokenHash($activeToken->getTokenHash()));
    }

    private function createToken(\DateTimeImmutable $validUntil): RefreshToken
    {
        return new RefreshToken(
            hash('sha256', bin2hex(random_bytes(128))),
            Factory::create()->email(),
            $validUntil,
            bin2hex(random_bytes(16)),
            new \DateTimeImmutable('-1 minute'),
            new \DateTimeImmutable()
        );
    }
}
