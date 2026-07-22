<?php

declare(strict_types=1);

namespace Dimkinthepro\JwtAuth\Infrastructure\Console;

use Dimkinthepro\JwtAuth\Application\UseCase\Token\ExpiredRefreshTokensRemover;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
    name: 'dimkinthepro:jwt-auth:purge-expired-refresh-tokens',
    description: 'Delete expired refresh tokens from the storage',
)]
class PurgeExpiredRefreshTokensCommand extends Command
{
    public function __construct(
        private readonly ExpiredRefreshTokensRemover $expiredRefreshTokensRemover,
    ) {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        try {
            $deletedCount = $this->expiredRefreshTokensRemover->removeExpiredTokens();
        } catch (\Throwable $e) {
            $io->error(\sprintf('Expired refresh tokens purge error: "%s"', $e->getMessage()));

            return Command::FAILURE;
        }

        $io->success(\sprintf('Deleted %d expired refresh token(s).', $deletedCount));

        return Command::SUCCESS;
    }
}
