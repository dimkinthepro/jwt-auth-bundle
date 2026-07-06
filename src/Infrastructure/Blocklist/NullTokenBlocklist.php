<?php

declare(strict_types=1);

namespace Dimkinthepro\JwtAuth\Infrastructure\Blocklist;

use Dimkinthepro\JwtAuth\Application\Component\Blocklist\TokenBlocklistInterface;

/**
 * Used when the blocklist feature is disabled: nothing is blocked, nothing is stored.
 */
readonly class NullTokenBlocklist implements TokenBlocklistInterface
{
    public function block(string $sessionId): void
    {
    }

    public function isBlocked(string $sessionId): bool
    {
        return false;
    }
}
