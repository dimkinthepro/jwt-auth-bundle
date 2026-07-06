<?php

declare(strict_types=1);

namespace Dimkinthepro\JwtAuth\Application\Component\Provider;

use Dimkinthepro\JwtAuth\Domain\Entity\JwtToken;

interface PayloadForSignProviderInterface
{
    public function getPayload(JwtToken $jwtToken): string;
}
