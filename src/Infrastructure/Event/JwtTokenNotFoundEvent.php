<?php

declare(strict_types=1);

namespace Dimkinthepro\JwtAuth\Infrastructure\Event;

/**
 * Dispatched when a protected endpoint is hit without a token.
 */
final class JwtTokenNotFoundEvent extends JwtAuthenticationFailureEvent
{
}
