<?php

declare(strict_types=1);

namespace Dimkinthepro\JwtAuth\Infrastructure\Controller;

use Dimkinthepro\JwtAuth\Application\DTO\SessionDto;
use Dimkinthepro\JwtAuth\Application\UseCase\Session\UserSessionsFetcher;
use Dimkinthepro\JwtAuth\Domain\Entity\JwtToken;
use Dimkinthepro\JwtAuth\Domain\Enum\TokenDictionaryEnum;
use Dimkinthepro\JwtAuth\Infrastructure\Response\ResponseTrait;
use Dimkinthepro\JwtAuth\Infrastructure\Security\JWTAuthenticator;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

readonly class SessionListAction
{
    use ResponseTrait;

    public function __construct(
        private TokenStorageInterface $tokenStorage,
        private UserSessionsFetcher $userSessionsFetcher,
    ) {
    }

    public function __invoke(): Response
    {
        $securityToken = $this->tokenStorage->getToken();
        if (null === $securityToken) {
            return new JsonResponse(null, Response::HTTP_UNAUTHORIZED);
        }

        $currentSessionId = $this->getCurrentSessionId($securityToken->getAttributes());
        $sessions = $this->userSessionsFetcher->fetch($securityToken->getUserIdentifier());

        return $this->successJson([
            'sessions' => array_map(
                fn (SessionDto $session): array => $this->mapSession($session, $currentSessionId),
                $sessions
            ),
        ]);
    }

    private function getCurrentSessionId(array $securityTokenAttributes): ?string
    {
        $jwtToken = $securityTokenAttributes[JWTAuthenticator::JWT_TOKEN_ATTRIBUTE] ?? null;
        if (false === $jwtToken instanceof JwtToken) {
            return null;
        }

        $sessionId = $jwtToken->getClaim(TokenDictionaryEnum::SESSION_ID->value);

        return \is_string($sessionId) ? $sessionId : null;
    }

    private function mapSession(SessionDto $session, ?string $currentSessionId): array
    {
        return [
            'sessionId' => $session->sessionId,
            'deviceName' => $session->deviceName,
            'userAgent' => $session->userAgent,
            'ip' => $session->ip,
            'createdAt' => $session->createdAt->format(\DateTimeInterface::ATOM),
            'lastUsedAt' => $session->lastUsedAt->format(\DateTimeInterface::ATOM),
            'validUntil' => $session->validUntil->format(\DateTimeInterface::ATOM),
            'current' => null !== $currentSessionId && $session->sessionId === $currentSessionId,
        ];
    }
}
