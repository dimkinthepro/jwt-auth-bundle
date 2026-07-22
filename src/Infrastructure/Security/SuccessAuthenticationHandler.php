<?php

declare(strict_types=1);

namespace Dimkinthepro\JwtAuth\Infrastructure\Security;

use Dimkinthepro\JwtAuth\Application\UseCase\Token\TokenPairCreator;
use Dimkinthepro\JwtAuth\Infrastructure\Enum\TokenResponseEnum;
use Dimkinthepro\JwtAuth\Infrastructure\Event\JwtAuthenticationSuccessEvent;
use Dimkinthepro\JwtAuth\Infrastructure\Response\ResponseTrait;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Http\Authentication\AuthenticationSuccessHandlerInterface;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;

readonly class SuccessAuthenticationHandler implements AuthenticationSuccessHandlerInterface
{
    use ResponseTrait;

    public function __construct(
        private TokenPairCreator $tokenPairCreator,
        private EventDispatcherInterface $eventDispatcher,
    ) {
    }

    public function onAuthenticationSuccess(Request $request, TokenInterface $token): Response
    {
        $user = $token->getUser();
        $tokenPair = $this->tokenPairCreator->create($user->getUserIdentifier());

        $event = new JwtAuthenticationSuccessEvent([
            TokenResponseEnum::TOKEN->value => $tokenPair->token->getEncodedToken(),
            TokenResponseEnum::REFRESH_TOKEN->value => $tokenPair->refreshToken->getEncodedToken(),
        ], $user);

        $this->eventDispatcher->dispatch($event);

        return $this->successJson($event->getData());
    }
}
