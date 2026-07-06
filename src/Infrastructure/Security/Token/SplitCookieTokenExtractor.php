<?php

declare(strict_types=1);

namespace Dimkinthepro\JwtAuth\Infrastructure\Security\Token;

use Symfony\Component\HttpFoundation\Request;

/**
 * Rebuilds the token from two cookies: "header.payload" (readable by JS) and the signature
 * (HttpOnly/Secure). An XSS attack can then never read a complete usable token.
 */
readonly class SplitCookieTokenExtractor implements TokenExtractorInterface
{
    public function __construct(
        private string $payloadCookieName,
        private string $signatureCookieName,
    ) {
    }

    public function extractToken(Request $request): ?string
    {
        $payload = $request->cookies->get($this->payloadCookieName);
        $signature = $request->cookies->get($this->signatureCookieName);

        if (false === \is_string($payload) || '' === $payload
            || false === \is_string($signature) || '' === $signature
        ) {
            return null;
        }

        return \sprintf('%s.%s', $payload, $signature);
    }
}
