<?php

declare(strict_types=1);

namespace Dimkinthepro\JwtAuth\Infrastructure\Event;

use Dimkinthepro\JwtAuth\Domain\Entity\JwtToken;
use Symfony\Component\Security\Http\Authenticator\Passport\Passport;
use Symfony\Contracts\EventDispatcher\Event;

/**
 * Dispatched when a request is authenticated with a valid JWT:
 * listeners may add passport attributes based on the token claims.
 */
final class JwtTokenAuthenticatedEvent extends Event
{
    public function __construct(
        private readonly JwtToken $jwtToken,
        private readonly Passport $passport,
    ) {
    }

    public function getJwtToken(): JwtToken
    {
        return $this->jwtToken;
    }

    public function getPassport(): Passport
    {
        return $this->passport;
    }
}
