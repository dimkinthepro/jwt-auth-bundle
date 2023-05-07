<?php

declare(strict_types=1);

namespace DimkinThePro\JwtAuth\Application\Component\Factory;

use DimkinThePro\JwtAuth\Domain\Entity\RefreshToken;

interface RefreshTokenFactoryInterface
{
    public function create(string $userIdentifier): RefreshToken;
}
