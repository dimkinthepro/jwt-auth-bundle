<?php

declare(strict_types=1);

namespace Dimkinthepro\JwtAuth\Application\Component\Manager;

use Dimkinthepro\JwtAuth\Application\Component\Exception\JwtAuthExceptionInterface;
use Dimkinthepro\JwtAuth\Domain\Entity\RefreshToken;

interface RefreshTokenManagerInterface
{
    /**
     * @throws JwtAuthExceptionInterface
     */
    public function findByToken(string $rawToken): RefreshToken;

    /**
     * Device sessions of the user, most recently used first.
     *
     * @return RefreshToken[]
     */
    public function findAllByUserIdentifier(string $userIdentifier): array;

    /**
     * @throws JwtAuthExceptionInterface
     */
    public function create(string $userIdentifier): RefreshToken;

    /**
     * Issues a new token for the same device session as the previous one.
     *
     * @throws JwtAuthExceptionInterface
     */
    public function rotate(RefreshToken $previousToken): RefreshToken;

    /**
     * @throws JwtAuthExceptionInterface
     */
    public function delete(RefreshToken $token): void;

    /**
     * Revokes a device session of the given user. A session of another user is reported
     * as not found to avoid leaking session identifiers.
     *
     * @throws JwtAuthExceptionInterface
     */
    public function revokeSession(string $sessionId, string $userIdentifier): void;

    /**
     * Device sessions of the user, locked for update until the transaction ends.
     * Must be called inside an active transaction.
     *
     * @return RefreshToken[]
     */
    public function findAllByUserIdentifierForUpdate(string $userIdentifier): array;

    /**
     * @param RefreshToken[] $sessions
     *
     * @return int number of deleted sessions
     */
    public function deleteSessions(array $sessions): int;

    /**
     * @return int number of deleted tokens
     */
    public function deleteExpiredTokens(\DateTimeImmutable $expiredBefore): int;
}
