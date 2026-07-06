<?php

declare(strict_types=1);

namespace Dimkinthepro\JwtAuth\Application\UseCase\RefreshToken;

use Dimkinthepro\JwtAuth\Application\Component\Manager\RefreshTokenManager;
use Dimkinthepro\JwtAuth\Domain\Entity\RefreshToken;

class RefreshTokenCreator
{
    public function __construct(
        private readonly RefreshTokenManager $manager
    ) {
    }

    public function create(string $userIdentifier): RefreshToken
    {
        return $this->manager->create($userIdentifier);
    }
}
