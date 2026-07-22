<?php

declare(strict_types=1);

namespace Dimkinthepro\JwtAuth\Application\UseCase\Token;

use Dimkinthepro\JwtAuth\Application\Component\Manager\RefreshTokenManagerInterface;
use Dimkinthepro\JwtAuth\Infrastructure\Factory\DateTimeFactory;

readonly class ExpiredRefreshTokensRemover
{
    public function __construct(
        private RefreshTokenManagerInterface $refreshTokenManager,
        private DateTimeFactory $dateTimeFactory,
    ) {
    }

    /**
     * @return int number of deleted tokens
     *
     * @throws \Exception
     */
    public function removeExpiredTokens(): int
    {
        return $this->refreshTokenManager->deleteExpiredTokens($this->dateTimeFactory->getNowDate());
    }
}
