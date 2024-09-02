<?php

declare(strict_types=1);

namespace Dimkinthepro\JwtAuth\Application\Component\Repository;

use Dimkinthepro\JwtAuth\Domain\Entity\RefreshToken;

interface RefreshTokenWriteRepositoryInterface
{
    public function save(RefreshToken $token): void;

    public function delete(RefreshToken $token): void;
}
