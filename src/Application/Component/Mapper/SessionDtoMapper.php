<?php

declare(strict_types=1);

namespace Dimkinthepro\JwtAuth\Application\Component\Mapper;

use Dimkinthepro\JwtAuth\Application\DTO\SessionDto;
use Dimkinthepro\JwtAuth\Domain\Entity\RefreshToken;

readonly class SessionDtoMapper
{
    public function map(RefreshToken $refreshToken): SessionDto
    {
        return new SessionDto(
            $refreshToken->getSessionId(),
            $refreshToken->getUserIdentifier(),
            $refreshToken->getValidUntil(),
            $refreshToken->getCreatedAt(),
            $refreshToken->getLastUsedAt(),
            $refreshToken->getDeviceName(),
            $refreshToken->getUserAgent(),
            $refreshToken->getIp(),
        );
    }

    /**
     * @param RefreshToken[] $refreshTokens
     *
     * @return SessionDto[]
     */
    public function mapList(array $refreshTokens): array
    {
        return array_map($this->map(...), $refreshTokens);
    }
}
