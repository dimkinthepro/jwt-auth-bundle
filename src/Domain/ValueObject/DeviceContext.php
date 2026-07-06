<?php

declare(strict_types=1);

namespace Dimkinthepro\JwtAuth\Domain\ValueObject;

/**
 * Device metadata captured when a session is created or refreshed.
 */
final readonly class DeviceContext
{
    public function __construct(
        public ?string $deviceName = null,
        public ?string $userAgent = null,
        public ?string $ip = null,
    ) {
    }
}
