<?php

declare(strict_types=1);

namespace Dimkinthepro\JwtAuth\Application\Component\Blocklist;

/**
 * Instant revocation of access tokens by their "sid" claim: a blocked session id
 * invalidates every outstanding JWT of that device session.
 */
interface TokenBlocklistInterface
{
    public function block(string $sessionId): void;

    public function isBlocked(string $sessionId): bool;
}
