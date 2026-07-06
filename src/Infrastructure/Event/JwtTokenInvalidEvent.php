<?php

declare(strict_types=1);

namespace Dimkinthepro\JwtAuth\Infrastructure\Event;

/**
 * Dispatched when authentication fails because the token is invalid.
 */
final class JwtTokenInvalidEvent extends JwtAuthenticationFailureEvent
{
}
