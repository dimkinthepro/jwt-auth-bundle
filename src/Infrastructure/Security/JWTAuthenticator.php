<?php

declare(strict_types=1);

namespace Dimkinthepro\JwtAuth\Infrastructure\Security;

use Dimkinthepro\JwtAuth\Infrastructure\Security\Token\TokenExtractorInterface;
use Dimkinthepro\JwtAuth\Infrastructure\Service\TokenService;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Core\User\UserProviderInterface;
use Symfony\Component\Security\Http\Authenticator\AbstractAuthenticator;
use Symfony\Component\Security\Http\Authenticator\Passport\Badge\UserBadge;
use Symfony\Component\Security\Http\Authenticator\Passport\Passport;
use Symfony\Component\Security\Http\Authenticator\Passport\SelfValidatingPassport;
use Symfony\Component\Security\Http\EntryPoint\AuthenticationEntryPointInterface;

class JWTAuthenticator extends AbstractAuthenticator implements AuthenticationEntryPointInterface
{
    public function __construct(
        private readonly TokenExtractorInterface $tokenExtractor,
        private readonly UserProviderInterface $userProvider,
        private readonly TokenService $tokenService
    ) {
    }

    public function start(Request $request, AuthenticationException $authException = null): Response
    {
        return new JsonResponse(['a2bab5e0-e88a-4261-97e4-8130ed5077fe Unauthorized'], Response::HTTP_UNAUTHORIZED);
    }

    public function supports(Request $request): ?bool
    {
        return null !== $this->tokenExtractor->extractToken($request);
    }

    public function authenticate(Request $request): Passport
    {
        $token = $this->tokenExtractor->extractToken($request);
        $jwtToken = $this->tokenService->extractJwtToken($token);

        $passport = new SelfValidatingPassport(
            new UserBadge(
                $jwtToken->getUserIdentifier(),
                function ($userIdentifier) {
                    return $this->userProvider->loadUserByIdentifier($userIdentifier);
                }
            )
        );

        $passport->setAttribute('token', $token);

        return $passport;
    }

    public function onAuthenticationSuccess(Request $request, TokenInterface $token, string $firewallName): ?Response
    {
        return null;
    }

    public function onAuthenticationFailure(Request $request, AuthenticationException $exception): ?Response
    {
        return new JsonResponse(['d2d7805b-ca35-449c-8958-5934e8012005 Bad token'], Response::HTTP_FORBIDDEN);
    }
}
