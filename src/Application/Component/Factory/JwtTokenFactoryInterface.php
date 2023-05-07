<?php

declare(strict_types=1);

namespace DimkinThePro\JwtAuth\Application\Component\Factory;

use DimkinThePro\JwtAuth\Domain\Entity\JwtToken;

interface JwtTokenFactoryInterface
{
    public function create(string $userIdentifier): JwtToken;
}
