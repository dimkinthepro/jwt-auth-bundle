<?php

declare(strict_types=1);

namespace Dimkinthepro\JwtAuth\Infrastructure\Controller;

use Dimkinthepro\JwtAuth\Application\UseCase\Session\UserSessionsRevoker;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

readonly class SessionRevokeAllAction
{
    public function __construct(
        private TokenStorageInterface $tokenStorage,
        private UserSessionsRevoker $userSessionsRevoker,
    ) {
    }

    public function __invoke(): Response
    {
        $securityToken = $this->tokenStorage->getToken();
        if (null === $securityToken) {
            return new JsonResponse(null, Response::HTTP_UNAUTHORIZED);
        }

        $this->userSessionsRevoker->revokeAll($securityToken->getUserIdentifier());

        return new JsonResponse(null, Response::HTTP_NO_CONTENT);
    }
}
