<?php

declare(strict_types=1);

namespace DimkinThePro\JwtAuth\Application\UseCase\RefreshToken;

use DimkinThePro\JwtAuth\Application\Component\Manager\RefreshTokenManager;
use DimkinThePro\JwtAuth\Domain\Entity\RefreshToken;

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
