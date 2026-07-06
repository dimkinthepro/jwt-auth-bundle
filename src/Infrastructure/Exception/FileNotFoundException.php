<?php

declare(strict_types=1);

namespace Dimkinthepro\JwtAuth\Infrastructure\Exception;

use Dimkinthepro\JwtAuth\Application\Component\Exception\JwtAuthExceptionInterface;
use Symfony\Component\Security\Core\Exception\AuthenticationException;

class FileNotFoundException extends AuthenticationException implements JwtAuthExceptionInterface
{
}
