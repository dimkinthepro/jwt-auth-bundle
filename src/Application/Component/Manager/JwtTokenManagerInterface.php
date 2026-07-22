<?php

declare(strict_types=1);

namespace Dimkinthepro\JwtAuth\Application\Component\Manager;

use Dimkinthepro\JwtAuth\Domain\Entity\JwtToken;

interface JwtTokenManagerInterface
{
    public function create(string $userIdentifier, ?string $sessionId = null): JwtToken;
}
