<?php

declare(strict_types=1);

namespace Dimkinthepro\JwtAuth\Infrastructure\Security\Token;

use Symfony\Component\HttpFoundation\Request;

/**
 * Runs the enabled extractors in priority order and returns the first token found.
 */
readonly class ChainTokenExtractor implements TokenExtractorInterface
{
    /**
     * @param iterable<TokenExtractorInterface> $extractors
     */
    public function __construct(
        private iterable $extractors,
    ) {
    }

    public function extractToken(Request $request): ?string
    {
        foreach ($this->extractors as $extractor) {
            $token = $extractor->extractToken($request);
            if (null !== $token) {
                return $token;
            }
        }

        return null;
    }
}
