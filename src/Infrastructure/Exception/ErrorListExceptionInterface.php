<?php

declare(strict_types=1);

namespace DimkinThePro\JwtAuth\Infrastructure\Exception;

interface ErrorListExceptionInterface
{
    public function getErrors(): array;
}
