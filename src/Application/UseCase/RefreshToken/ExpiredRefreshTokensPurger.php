<?php

declare(strict_types=1);

namespace Dimkinthepro\JwtAuth\Application\UseCase\RefreshToken;

use Dimkinthepro\JwtAuth\Application\Component\Manager\RefreshTokenManager;
use Dimkinthepro\JwtAuth\Infrastructure\Factory\DateTimeFactory;

readonly class ExpiredRefreshTokensPurger
{
    public function __construct(
        private RefreshTokenManager $refreshTokenManager,
        private DateTimeFactory $dateTimeFactory,
    ) {
    }

    /**
     * @return int number of deleted tokens
     *
     * @throws \Exception
     */
    public function purge(): int
    {
        return $this->refreshTokenManager->deleteExpiredTokens($this->dateTimeFactory->getNowDate());
    }
}
