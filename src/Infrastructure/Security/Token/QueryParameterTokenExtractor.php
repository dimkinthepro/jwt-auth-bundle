<?php

declare(strict_types=1);

namespace Dimkinthepro\JwtAuth\Infrastructure\Security\Token;

use Symfony\Component\HttpFoundation\Request;

/**
 * Intended for transports where headers cannot be set (WebSocket, SSE, direct download links).
 * Keep it disabled otherwise: tokens passed in the URL end up in access logs and browser history.
 */
readonly class QueryParameterTokenExtractor implements TokenExtractorInterface
{
    public function __construct(
        private string $parameterName,
    ) {
    }

    public function extractToken(Request $request): ?string
    {
        $token = $request->query->get($this->parameterName);

        return \is_string($token) && '' !== $token ? $token : null;
    }
}
