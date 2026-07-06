<?php

declare(strict_types=1);

namespace Dimkinthepro\JwtAuth\Infrastructure\Security\Token;

use Symfony\Component\HttpFoundation\Request;

interface TokenExtractorInterface
{
    public function extractToken(Request $request): ?string;
}
