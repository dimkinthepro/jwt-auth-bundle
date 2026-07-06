<?php

declare(strict_types=1);

namespace Dimkinthepro\JwtAuth\Infrastructure\Blocklist;

use Dimkinthepro\JwtAuth\Application\Component\Blocklist\TokenBlocklistInterface;
use Psr\Cache\CacheItemPoolInterface;

/**
 * PSR-6 backed blocklist. Entries live only as long as the longest outstanding
 * access token of the session could (token TTL + clock skew leeway), so the
 * blocklist cleans itself up.
 */
readonly class CacheTokenBlocklist implements TokenBlocklistInterface
{
    private const CACHE_KEY_PREFIX = 'dimkinthepro_jwt_auth.blocklist.';

    public function __construct(
        private CacheItemPoolInterface $cache,
        private int $authJwtTokenTtl,
        private int $authClockSkewLeeway,
    ) {
    }

    public function block(string $sessionId): void
    {
        $item = $this->cache->getItem(self::CACHE_KEY_PREFIX . $sessionId);
        $item->set(true);
        $item->expiresAfter($this->authJwtTokenTtl + $this->authClockSkewLeeway);
        $this->cache->save($item);
    }

    public function isBlocked(string $sessionId): bool
    {
        return $this->cache->getItem(self::CACHE_KEY_PREFIX . $sessionId)->isHit();
    }
}
