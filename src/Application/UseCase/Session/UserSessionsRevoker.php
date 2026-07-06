<?php

declare(strict_types=1);

namespace Dimkinthepro\JwtAuth\Application\UseCase\Session;

use Dimkinthepro\JwtAuth\Application\Component\Blocklist\TokenBlocklistInterface;
use Dimkinthepro\JwtAuth\Application\Component\Manager\RefreshTokenManager;
use Dimkinthepro\JwtAuth\Application\Component\Persistence\TransactionManagerInterface;

readonly class UserSessionsRevoker
{
    public function __construct(
        private RefreshTokenManager $refreshTokenManager,
        private TokenBlocklistInterface $tokenBlocklist,
        private TransactionManagerInterface $transactionManager,
    ) {
    }

    /**
     * Revokes every device session of the user (e.g. on account compromise).
     * With the blocklist enabled all outstanding access tokens die instantly.
     *
     * The rows are locked for update and exactly the locked ones are deleted, so a concurrent
     * rotation cannot slip a session past the blocklist; a login committed after the lock
     * is a new session that survives the revocation.
     *
     * @return int number of revoked sessions
     */
    public function revokeAll(string $userIdentifier): int
    {
        return $this->transactionManager->transactional(function () use ($userIdentifier): int {
            $sessions = $this->refreshTokenManager->findAllByUserIdentifierForUpdate($userIdentifier);

            // Block before deleting: a spurious blocklist entry left by a rollback expires on its
            // own, while a deleted-but-not-blocked session would keep access until the JWT TTL
            foreach ($sessions as $session) {
                $this->tokenBlocklist->block($session->getSessionId());
            }

            return $this->refreshTokenManager->deleteSessions($sessions);
        });
    }
}
