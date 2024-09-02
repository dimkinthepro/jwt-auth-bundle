<?php

declare(strict_types=1);

namespace Dimkinthepro\JwtAuth\Infrastructure\Security\Token;

use Symfony\Component\HttpFoundation\Request;

class BearerTokenExtractor implements TokenExtractorInterface
{
    public function extractToken(Request $request): ?string
    {
        $authorizationHeader = $request->headers->get('authorization');
        $headerParts = explode(' ', (string) $authorizationHeader);

        if (!(2 === \count($headerParts) && 0 === strcasecmp($headerParts[0], 'Bearer'))) {
            return null;
        }

        return $headerParts[1];
    }
}
