<?php

declare(strict_types=1);

namespace Dimkinthepro\JwtAuth\Infrastructure\Exception;

interface ErrorListExceptionInterface
{
    public function getErrors(): array;
}
