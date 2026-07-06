<?php

declare(strict_types=1);

namespace Dimkinthepro\JwtAuth\Infrastructure\Event;

/**
 * Dispatched when authentication fails because the token is expired.
 */
final class JwtTokenExpiredEvent extends JwtAuthenticationFailureEvent
{
}
