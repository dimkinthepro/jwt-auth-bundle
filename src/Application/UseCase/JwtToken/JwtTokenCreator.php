<?php

declare(strict_types=1);

namespace Dimkinthepro\JwtAuth\Application\UseCase\JwtToken;

use Dimkinthepro\JwtAuth\Application\Component\Manager\JwtTokenManager;
use Dimkinthepro\JwtAuth\Domain\Entity\JwtToken;

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
