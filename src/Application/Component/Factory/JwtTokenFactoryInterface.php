<?php

declare(strict_types=1);

namespace Dimkinthepro\JwtAuth\Application\Component\Factory;

use Dimkinthepro\JwtAuth\Domain\Entity\JwtToken;

interface JwtTokenFactoryInterface
{
    /**
     * @param string|null $sessionId device session identifier put into the "sid" claim when provided
     */
    public function create(string $userIdentifier, ?string $sessionId = null): JwtToken;
}
