<?php

declare(strict_types=1);

namespace Dimkinthepro\JwtAuth\Application\Component\Factory;

use Dimkinthepro\JwtAuth\Domain\Entity\RefreshToken;

interface RefreshTokenFactoryInterface
{
    public function create(string $userIdentifier): RefreshToken;

    /**
     * Issues a new token for the same device session as the previous one.
     */
    public function rotate(RefreshToken $previousToken): RefreshToken;
}
