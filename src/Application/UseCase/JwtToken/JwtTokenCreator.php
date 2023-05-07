<?php

declare(strict_types=1);

namespace DimkinThePro\JwtAuth\Application\UseCase\JwtToken;

use DimkinThePro\JwtAuth\Application\Component\Manager\JwtTokenManager;
use DimkinThePro\JwtAuth\Domain\Entity\JwtToken;

class JwtTokenCreator
{
    public function __construct(
        private readonly JwtTokenManager $manager,
    ) {
    }

    public function create(string $userIdentifier): JwtToken
    {
        return $this->manager->create($userIdentifier);
    }
}
