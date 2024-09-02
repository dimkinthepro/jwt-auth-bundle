<?php

declare(strict_types=1);

namespace Dimkinthepro\JwtAuth\Infrastructure\Security;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Http\Authentication\AuthenticationFailureHandlerInterface;

class FailAuthenticationHandler implements AuthenticationFailureHandlerInterface
{
    public function onAuthenticationFailure(Request $request, AuthenticationException $exception): Response
    {
        $statusCode = $this->mapExceptionCodeToStatusCode($exception->getCode());

        throw new AuthenticationException($exception->getMessage(), $statusCode, $exception);
    }

    private function mapExceptionCodeToStatusCode(int $exceptionCode): int
    {
        $canMapToStatusCode = $exceptionCode >= 400 && $exceptionCode < 500;

        return $canMapToStatusCode
            ? $exceptionCode
            : Response::HTTP_UNAUTHORIZED;
    }
}
