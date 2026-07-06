<?php

declare(strict_types=1);

namespace Dimkinthepro\JwtAuth\Application\UseCase\Session;

use Dimkinthepro\JwtAuth\Application\Component\Manager\RefreshTokenManager;
use Dimkinthepro\JwtAuth\Application\Component\Mapper\SessionDtoMapper;
use Dimkinthepro\JwtAuth\Application\DTO\SessionDto;

readonly class UserSessionsFetcher
{
    public function __construct(
        private RefreshTokenManager $refreshTokenManager,
        private SessionDtoMapper $sessionDtoMapper,
    ) {
    }

    /**
     * Device sessions of the user, most recently used first.
     *
     * @return SessionDto[]
     */
    public function fetch(string $userIdentifier): array
    {
        return $this->sessionDtoMapper->mapList(
            $this->refreshTokenManager->findAllByUserIdentifier($userIdentifier)
        );
    }
}
