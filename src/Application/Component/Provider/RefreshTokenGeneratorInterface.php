<?php

declare(strict_types=1);

namespace Dimkinthepro\JwtAuth\Application\Component\Provider;

interface RefreshTokenGeneratorInterface
{
    public function generate(): string;
}
