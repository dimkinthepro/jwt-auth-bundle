<?php

declare(strict_types=1);

namespace Dimkinthepro\JwtAuth\Application\UseCase\Token;

use Dimkinthepro\JwtAuth\Application\Component\Manager\JwtTokenManagerInterface;
use Dimkinthepro\JwtAuth\Application\Component\Manager\RefreshTokenManagerInterface;
use Dimkinthepro\JwtAuth\Application\DTO\TokenPair;

final readonly class TokenPairCreator
{
    public function __construct(
        private JwtTokenManagerInterface $jwtTokenManager,
        private RefreshTokenManagerInterface $refreshTokenManager
    ) {
    }

    public function create(string $userIdentifier): TokenPair
    {
        $refreshToken = $this->refreshTokenManager->create($userIdentifier);
        $jwtToken = $this->jwtTokenManager->create($userIdentifier, $refreshToken->getSessionId());

        return new TokenPair(
            token: $jwtToken,
            refreshToken: $refreshToken,
        );
    }
}
