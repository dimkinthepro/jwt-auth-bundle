<?php

declare(strict_types=1);

namespace Dimkinthepro\JwtAuth\Application\UseCase\Session;

use Dimkinthepro\JwtAuth\Application\Component\Blocklist\TokenBlocklistInterface;
use Dimkinthepro\JwtAuth\Application\Component\Exception\JwtAuthExceptionInterface;
use Dimkinthepro\JwtAuth\Application\Component\Manager\RefreshTokenManager;

readonly class SessionRevoker
{
    public function __construct(
        private RefreshTokenManager $refreshTokenManager,
        private TokenBlocklistInterface $tokenBlocklist,
    ) {
    }

    /**
     * Revokes a device session of the given user; a session of another user is reported as not found.
     * With the blocklist enabled the outstanding access tokens of the session die instantly.
     *
     * @throws JwtAuthExceptionInterface
     */
    public function revoke(string $sessionId, string $userIdentifier): void
    {
        $this->refreshTokenManager->revokeSession($sessionId, $userIdentifier);
        $this->tokenBlocklist->block($sessionId);
    }
}
