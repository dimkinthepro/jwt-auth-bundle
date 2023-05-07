<?php

declare(strict_types=1);

namespace DimkinThePro\JwtAuth\Infrastructure\Exception;

use DimkinThePro\JwtAuth\Application\Component\Exception\JwtAuthExceptionInterface;
use Symfony\Component\Security\Core\Exception\AuthenticationException;

class JwtTokenExpiredException extends AuthenticationException implements JwtAuthExceptionInterface
{
}
