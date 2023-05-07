<?php

declare(strict_types=1);

namespace DimkinThePro\JwtAuth\Application\Component\Provider;

use DimkinThePro\JwtAuth\Domain\Entity\JwtToken;

interface PayloadForSignProviderInterface
{
    public function getPayload(JwtToken $jwtToken): string;
}
