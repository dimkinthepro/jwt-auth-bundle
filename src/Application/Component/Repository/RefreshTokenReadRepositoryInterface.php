<?php

declare(strict_types=1);

namespace Dimkinthepro\JwtAuth\Application\Component\Repository;

use Dimkinthepro\JwtAuth\Application\Component\Exception\JwtAuthExceptionInterface;
use Dimkinthepro\JwtAuth\Domain\Entity\RefreshToken;

interface RefreshTokenReadRepositoryInterface
{
    public function findByToken(string $token): ?RefreshToken;

    /**
     * @throws JwtAuthExceptionInterface
     */
    public function findByTokenOrThrowException(string $token): RefreshToken;
}
