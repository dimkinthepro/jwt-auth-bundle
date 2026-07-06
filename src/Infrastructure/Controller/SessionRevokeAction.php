<?php

declare(strict_types=1);

namespace Dimkinthepro\JwtAuth\Infrastructure\Controller;

use Dimkinthepro\JwtAuth\Application\Component\Exception\JwtAuthExceptionInterface;
use Dimkinthepro\JwtAuth\Application\UseCase\Session\SessionRevoker;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

readonly class SessionRevokeAction
{
    public function __construct(
        private TokenStorageInterface $tokenStorage,
        private SessionRevoker $sessionRevoker,
    ) {
    }

    /**
     * @throws NotFoundHttpException
     */
    public function __invoke(string $sessionId): Response
    {
        $securityToken = $this->tokenStorage->getToken();
        if (null === $securityToken) {
            return new JsonResponse(null, Response::HTTP_UNAUTHORIZED);
        }

        try {
            $this->sessionRevoker->revoke($sessionId, $securityToken->getUserIdentifier());
        } catch (JwtAuthExceptionInterface $e) {
            throw new NotFoundHttpException('c47d90b2-1f3e-4a68-9c05-7b8e2f6d1a54 Session not found');
        }

        return new JsonResponse(null, Response::HTTP_NO_CONTENT);
    }
}
