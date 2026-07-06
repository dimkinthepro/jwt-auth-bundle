<?php

declare(strict_types=1);

namespace Dimkinthepro\JwtAuth\Infrastructure\Service;

use Dimkinthepro\JwtAuth\Application\Component\Exception\JwtAuthExceptionInterface;
use Dimkinthepro\JwtAuth\Domain\Entity\JwtToken;
use Dimkinthepro\JwtAuth\Domain\Entity\RefreshToken;

interface TokenServiceInterface
{
    /**
     * @param string|null $sessionId device session identifier put into the "sid" claim when provided
     */
    public function createJwtToken(string $userIdentifier, ?string $sessionId = null): JwtToken;

    public function createRefreshToken(string $userIdentifier): RefreshToken;

    /**
     * @throws JwtAuthExceptionInterface
     */
    public function refreshRefreshToken(string $token): RefreshToken;

    /**
     * @throws JwtAuthExceptionInterface
     */
    public function extractJwtToken(string $token): JwtToken;
}
