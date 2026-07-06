<?php

declare(strict_types=1);

namespace Dimkinthepro\JwtAuth\Application\Component\Repository;

use Dimkinthepro\JwtAuth\Application\Component\Exception\JwtAuthExceptionInterface;
use Dimkinthepro\JwtAuth\Domain\Entity\RefreshToken;

interface RefreshTokenReadRepositoryInterface
{
    public function findByTokenHash(string $tokenHash): ?RefreshToken;

    /**
     * @throws JwtAuthExceptionInterface
     */
    public function findByTokenHashOrThrowException(string $tokenHash): RefreshToken;

    public function findBySessionId(string $sessionId): ?RefreshToken;

    /**
     * @return RefreshToken[] sessions of the user ordered by last usage, most recent first
     */
    public function findAllByUserIdentifier(string $userIdentifier): array;

    /**
     * Same as findAllByUserIdentifier() but locks the rows for update until the transaction ends.
     * Must be called inside an active transaction.
     *
     * @return RefreshToken[]
     */
    public function findAllByUserIdentifierForUpdate(string $userIdentifier): array;
}
