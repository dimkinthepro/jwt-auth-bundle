<?php

declare(strict_types=1);

namespace DimkinThePro\JwtAuth\Application\Component\Provider;

use DimkinThePro\JwtAuth\Domain\Entity\JwtToken;

interface JwtTokenSignerInterface
{
    public function sign(JwtToken $jwtToken): void;
}
