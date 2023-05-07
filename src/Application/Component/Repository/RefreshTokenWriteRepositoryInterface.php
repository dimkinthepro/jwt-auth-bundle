<?php

declare(strict_types=1);

namespace DimkinThePro\JwtAuth\Application\Component\Repository;

use DimkinThePro\JwtAuth\Domain\Entity\RefreshToken;

interface RefreshTokenWriteRepositoryInterface
{
    public function save(RefreshToken $token): void;

    public function delete(RefreshToken $token): void;
}
