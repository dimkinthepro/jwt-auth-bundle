<?php

declare(strict_types=1);

namespace Dimkinthepro\JwtAuth\Application\Component\Factory;

use Dimkinthepro\JwtAuth\Domain\Entity\RefreshToken;

interface RefreshTokenFactoryInterface
{
    public function create(string $userIdentifier): RefreshToken;
}
