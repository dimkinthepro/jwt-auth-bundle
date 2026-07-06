<?php

declare(strict_types=1);

namespace Dimkinthepro\JwtAuth\Application\Component\Event;

use Symfony\Contracts\EventDispatcher\Event;

/**
 * Dispatched before the token is signed: listeners may adjust the claims.
 * Reserved claims ("identifier", "iat", "exp") and the header are protected and cannot be changed here.
 */
final class JwtTokenCreatedEvent extends Event
{
    /**
     * @param array<string, mixed> $claims
     */
    public function __construct(
        private readonly string $userIdentifier,
        private array $claims,
    ) {
    }

    public function getUserIdentifier(): string
    {
        return $this->userIdentifier;
    }

    /**
     * @return array<string, mixed>
     */
    public function getClaims(): array
    {
        return $this->claims;
    }

    /**
     * @param array<string, mixed> $claims
     */
    public function setClaims(array $claims): void
    {
        $this->claims = $claims;
    }
}
