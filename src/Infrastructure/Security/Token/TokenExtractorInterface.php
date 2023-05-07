<?php

declare(strict_types=1);

namespace DimkinThePro\JwtAuth\Infrastructure\Security\Token;

use Symfony\Component\HttpFoundation\Request;

interface TokenExtractorInterface
{
    public function extractToken(Request $request): ?string;
}
