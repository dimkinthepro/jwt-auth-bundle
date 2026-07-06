<?php

declare(strict_types=1);

namespace Dimkinthepro\JwtAuth\Application\Component\Event;

use Dimkinthepro\JwtAuth\Domain\Entity\JwtToken;
use Symfony\Contracts\EventDispatcher\Event;

/**
 * Dispatched after a token passed signature and claims validation:
 * listeners may run additional checks and reject the token.
 */
final class JwtTokenDecodedEvent extends Event
{
    private bool $markedAsInvalid = false;

    public function __construct(
        private readonly JwtToken $jwtToken,
    ) {
    }

    public function getJwtToken(): JwtToken
    {
        return $this->jwtToken;
    }

    public function markAsInvalid(): void
    {
        $this->markedAsInvalid = true;
    }

    public function isMarkedAsInvalid(): bool
    {
        return $this->markedAsInvalid;
    }
}
