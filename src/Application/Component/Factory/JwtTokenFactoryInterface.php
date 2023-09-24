<?php

declare(strict_types=1);

namespace Dimkinthepro\JwtAuth\Application\Component\Factory;

use Dimkinthepro\JwtAuth\Domain\Entity\JwtToken;

interface JwtTokenFactoryInterface
{
    public function create(string $userIdentifier): JwtToken;
}
