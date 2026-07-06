<?php

declare(strict_types=1);

namespace Dimkinthepro\JwtAuth\Application\Component\Repository;

use Dimkinthepro\JwtAuth\Application\Component\Exception\JwtAuthExceptionInterface;
use Dimkinthepro\JwtAuth\Domain\Entity\RefreshToken;

interface RefreshTokenWriteRepositoryInterface
{
    public function save(RefreshToken $token): void;

    /**
     * @throws JwtAuthExceptionInterface when the token has already been deleted (e.g. by a concurrent refresh)
     */
    public function delete(RefreshToken $token): void;

    /**
     * @return int number of deleted tokens
     */
    public function deleteExpired(\DateTimeImmutable $expiredBefore): int;

    /**
     * @param RefreshToken[] $tokens
     *
     * @return int number of deleted tokens
     */
    public function deleteAll(array $tokens): int;
}
