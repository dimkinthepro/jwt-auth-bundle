<?php

declare(strict_types=1);

namespace Dimkinthepro\JwtAuth\Infrastructure\Security\Token;

use Symfony\Component\HttpFoundation\Request;

readonly class CookieTokenExtractor implements TokenExtractorInterface
{
    public function __construct(
        private string $cookieName,
    ) {
    }

    public function extractToken(Request $request): ?string
    {
        $token = $request->cookies->get($this->cookieName);

        return \is_string($token) && '' !== $token ? $token : null;
    }
}
