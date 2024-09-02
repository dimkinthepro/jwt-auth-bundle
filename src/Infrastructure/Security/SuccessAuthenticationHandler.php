<?php

declare(strict_types=1);

namespace Dimkinthepro\JwtAuth\Infrastructure\Security;

use Dimkinthepro\JwtAuth\Infrastructure\Service\TokenService;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Http\Authentication\AuthenticationSuccessHandlerInterface;

class SuccessAuthenticationHandler implements AuthenticationSuccessHandlerInterface
{
    public function __construct(
        private readonly TokenService $tokenService
    ) {
    }

    public function onAuthenticationSuccess(Request $request, TokenInterface $token): Response
    {
        $jwtToken = $this->tokenService->createJwtToken($token->getUser()->getUserIdentifier());
        $refreshToken = $this->tokenService->createRefreshToken($token->getUser()->getUserIdentifier());

        return new JsonResponse([
            'jwtToken' => $jwtToken->getEncodedToken(),
            'refreshToken' => $refreshToken->getEncodedToken(),
        ]);
    }
}
