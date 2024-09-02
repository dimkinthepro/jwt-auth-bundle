<?php

declare(strict_types=1);

namespace Dimkinthepro\JwtAuth\Infrastructure\Service;

use Dimkinthepro\JwtAuth\Application\Component\Exception\JwtAuthExceptionInterface;
use Dimkinthepro\JwtAuth\Application\UseCase\JwtToken\JwtTokenCreator;
use Dimkinthepro\JwtAuth\Application\UseCase\JwtToken\JwtTokenExtractor;
use Dimkinthepro\JwtAuth\Application\UseCase\RefreshToken\RefreshTokenCreator;
use Dimkinthepro\JwtAuth\Application\UseCase\RefreshToken\RefreshTokenRefresher;
use Dimkinthepro\JwtAuth\Domain\Entity\JwtToken;
use Dimkinthepro\JwtAuth\Domain\Entity\RefreshToken;

class TokenService
{
    public function __construct(
        private readonly JwtTokenCreator $jwtTokenCreator,
        private readonly JwtTokenExtractor $jwtTokenExtractor,
        private readonly RefreshTokenCreator $refreshTokenCreator,
        private readonly RefreshTokenRefresher $refreshTokenRefresher,
    ) {
    }

    public function createJwtToken(string $userIdentifier): JwtToken
    {
        return $this->jwtTokenCreator->create($userIdentifier);
    }

    public function createRefreshToken(string $userIdentifier): RefreshToken
    {
        return $this->refreshTokenCreator->create($userIdentifier);
    }

    /**
     * @throws JwtAuthExceptionInterface
     */
    public function refreshRefreshToken(string $token): RefreshToken
    {
        return $this->refreshTokenRefresher->refresh($token);
    }

    /**
     * @throws JwtAuthExceptionInterface
     */
    public function extractJwtToken(string $token): JwtToken
    {
        return $this->jwtTokenExtractor->extract($token);
    }
}
