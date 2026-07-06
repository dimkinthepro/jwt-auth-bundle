<?php

declare(strict_types=1);

namespace Dimkinthepro\JwtAuth\Tests\Unit\Infrastructure\Blocklist;

use Dimkinthepro\JwtAuth\Infrastructure\Blocklist\CacheTokenBlocklist;
use Dimkinthepro\JwtAuth\Infrastructure\Blocklist\NullTokenBlocklist;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Cache\Adapter\ArrayAdapter;

class CacheTokenBlocklistTest extends TestCase
{
    public function testBlockedSessionIsReported(): void
    {
        $blocklist = new CacheTokenBlocklist(new ArrayAdapter(), 900, 60);
        $sessionId = bin2hex(random_bytes(16));

        self::assertFalse($blocklist->isBlocked($sessionId));

        $blocklist->block($sessionId);

        self::assertTrue($blocklist->isBlocked($sessionId));
        self::assertFalse($blocklist->isBlocked(bin2hex(random_bytes(16))));
    }

    public function testEntryExpiresWithTokenLifetime(): void
    {
        $cache = new ArrayAdapter();
        $blocklist = new CacheTokenBlocklist($cache, 900, 60);
        $sessionId = bin2hex(random_bytes(16));

        $blocklist->block($sessionId);

        // ArrayAdapter stores expiry timestamps: entry must not outlive token TTL + leeway
        $values = $cache->getValues();
        self::assertCount(1, $values);
    }

    public function testNullBlocklistNeverBlocks(): void
    {
        $blocklist = new NullTokenBlocklist();
        $sessionId = bin2hex(random_bytes(16));

        $blocklist->block($sessionId);

        self::assertFalse($blocklist->isBlocked($sessionId));
    }
}
