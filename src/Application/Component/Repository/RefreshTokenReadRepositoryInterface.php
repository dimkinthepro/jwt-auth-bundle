<?php

declare(strict_types=1);

namespace DimkinThePro\JwtAuth\Application\Component\Repository;

use DimkinThePro\JwtAuth\Application\Component\Exception\JwtAuthExceptionInterface;
use DimkinThePro\JwtAuth\Domain\Entity\RefreshToken;

interface RefreshTokenReadRepositoryInterface
{
    public function findByToken(string $token): ?RefreshToken;

    /**
     * @throws JwtAuthExceptionInterface
     */
    public function findByTokenOrThrowException(string $token): RefreshToken;
}
