<?php

declare(strict_types=1);

namespace Dimkinthepro\JwtAuth\Application\DTO;

/**
 * Read model of a device session: exposes no token material.
 */
final readonly class SessionDto
{
    public function __construct(
        public string $sessionId,
        public string $userIdentifier,
        public \DateTimeImmutable $validUntil,
        public \DateTimeImmutable $createdAt,
        public \DateTimeImmutable $lastUsedAt,
        public ?string $deviceName,
        public ?string $userAgent,
        public ?string $ip,
    ) {
    }
}
