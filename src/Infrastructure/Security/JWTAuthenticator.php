<?php

declare(strict_types=1);

namespace Dimkinthepro\JwtAuth\Infrastructure\Security;

use Dimkinthepro\JwtAuth\Infrastructure\Event\JwtTokenAuthenticatedEvent;
use Dimkinthepro\JwtAuth\Infrastructure\Event\JwtTokenExpiredEvent;
use Dimkinthepro\JwtAuth\Infrastructure\Event\JwtTokenInvalidEvent;
use Dimkinthepro\JwtAuth\Infrastructure\Event\JwtTokenNotFoundEvent;
use Dimkinthepro\JwtAuth\Infrastructure\Exception\JwtTokenExpiredException;
use Dimkinthepro\JwtAuth\Infrastructure\Security\Token\TokenExtractorInterface;
use Dimkinthepro\JwtAuth\Infrastructure\Service\TokenServiceInterface;
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
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;

class JWTAuthenticator extends AbstractAuthenticator implements AuthenticationEntryPointInterface
{
    public const JWT_TOKEN_ATTRIBUTE = 'jwtToken';

    private const WWW_AUTHENTICATE_HEADER = 'WWW-Authenticate';

    public function __construct(
        private readonly TokenExtractorInterface $tokenExtractor,
        private readonly UserProviderInterface $userProvider,
        private readonly TokenServiceInterface $tokenService,
        private readonly EventDispatcherInterface $eventDispatcher,
    ) {
    }

    public function start(Request $request, ?AuthenticationException $authException = null): Response
    {
        $defaultResponse = new JsonResponse(
            ['a2bab5e0-e88a-4261-97e4-8130ed5077fe Unauthorized'],
            Response::HTTP_UNAUTHORIZED,
            [self::WWW_AUTHENTICATE_HEADER => 'Bearer']
        );

        $event = new JwtTokenNotFoundEvent($defaultResponse, $authException);
        $this->eventDispatcher->dispatch($event);

        return $event->getResponse();
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
        $passport->setAttribute(self::JWT_TOKEN_ATTRIBUTE, $jwtToken);

        $this->eventDispatcher->dispatch(new JwtTokenAuthenticatedEvent($jwtToken, $passport));

        return $passport;
    }

    public function createToken(Passport $passport, string $firewallName): TokenInterface
    {
        // Expose the decoded JWT to controllers (e.g. to read the "sid" claim of the current session)
        $token = parent::createToken($passport, $firewallName);
        $token->setAttribute(self::JWT_TOKEN_ATTRIBUTE, $passport->getAttribute(self::JWT_TOKEN_ATTRIBUTE));

        return $token;
    }

    public function onAuthenticationSuccess(Request $request, TokenInterface $token, string $firewallName): ?Response
    {
        return null;
    }

    public function onAuthenticationFailure(Request $request, AuthenticationException $exception): ?Response
    {
        // RFC 6750: a rejected token gets 401 with the "invalid_token" error code,
        // so clients can trigger the refresh flow on 401 (403 is reserved for insufficient rights)
        $isExpired = $exception instanceof JwtTokenExpiredException;
        $defaultResponse = new JsonResponse(
            ['d2d7805b-ca35-449c-8958-5934e8012005 Bad token'],
            Response::HTTP_UNAUTHORIZED,
            [
                self::WWW_AUTHENTICATE_HEADER => $isExpired
                    ? 'Bearer error="invalid_token", error_description="The access token expired"'
                    : 'Bearer error="invalid_token"',
            ]
        );

        $event = $isExpired
            ? new JwtTokenExpiredEvent($defaultResponse, $exception)
            : new JwtTokenInvalidEvent($defaultResponse, $exception);
        $this->eventDispatcher->dispatch($event);

        return $event->getResponse();
    }
}
