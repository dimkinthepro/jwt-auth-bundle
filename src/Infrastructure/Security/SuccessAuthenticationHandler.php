<?php

declare(strict_types=1);

namespace Dimkinthepro\JwtAuth\Infrastructure\Security;

use Dimkinthepro\JwtAuth\Infrastructure\Enum\TokenResponseEnum;
use Dimkinthepro\JwtAuth\Infrastructure\Event\JwtAuthenticationSuccessEvent;
use Dimkinthepro\JwtAuth\Infrastructure\Response\ResponseTrait;
use Dimkinthepro\JwtAuth\Infrastructure\Service\TokenServiceInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Http\Authentication\AuthenticationSuccessHandlerInterface;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;

readonly class SuccessAuthenticationHandler implements AuthenticationSuccessHandlerInterface
{
    use ResponseTrait;

    public function __construct(
        private TokenServiceInterface $tokenService,
        private EventDispatcherInterface $eventDispatcher,
    ) {
    }

    public function onAuthenticationSuccess(Request $request, TokenInterface $token): Response
    {
        $user = $token->getUser();
        // The refresh token is created first: the JWT carries its session id in the "sid" claim
        $refreshToken = $this->tokenService->createRefreshToken($user->getUserIdentifier());
        $jwtToken = $this->tokenService->createJwtToken($user->getUserIdentifier(), $refreshToken->getSessionId());

        $event = new JwtAuthenticationSuccessEvent([
            TokenResponseEnum::TOKEN->value => $jwtToken->getEncodedToken(),
            TokenResponseEnum::REFRESH_TOKEN->value => $refreshToken->getEncodedToken(),
        ], $user);
        $this->eventDispatcher->dispatch($event);

        return $this->successJson($event->getData());
    }
}
