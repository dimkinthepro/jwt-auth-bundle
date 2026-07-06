<?php

declare(strict_types=1);

namespace Dimkinthepro\JwtAuth\Tests\Unit\Infrastructure\Security;

use Dimkinthepro\JwtAuth\Application\UseCase\JwtToken\JwtTokenCreator;
use Dimkinthepro\JwtAuth\Application\UseCase\JwtToken\JwtTokenExtractor;
use Dimkinthepro\JwtAuth\Application\UseCase\RefreshToken\RefreshTokenCreator;
use Dimkinthepro\JwtAuth\Application\UseCase\RefreshToken\RefreshTokenRefresher;
use Dimkinthepro\JwtAuth\Domain\Entity\JwtToken;
use Dimkinthepro\JwtAuth\Domain\Enum\AlgorithmEnum;
use Dimkinthepro\JwtAuth\Domain\Enum\TokenTypeEnum;
use Dimkinthepro\JwtAuth\Infrastructure\Event\JwtTokenAuthenticatedEvent;
use Dimkinthepro\JwtAuth\Infrastructure\Event\JwtTokenExpiredEvent;
use Dimkinthepro\JwtAuth\Infrastructure\Event\JwtTokenInvalidEvent;
use Dimkinthepro\JwtAuth\Infrastructure\Event\JwtTokenNotFoundEvent;
use Dimkinthepro\JwtAuth\Infrastructure\Exception\JwtTokenExpiredException;
use Dimkinthepro\JwtAuth\Infrastructure\Factory\DateTimeFactory;
use Dimkinthepro\JwtAuth\Infrastructure\Security\JWTAuthenticator;
use Dimkinthepro\JwtAuth\Infrastructure\Security\Token\BearerTokenExtractor;
use Dimkinthepro\JwtAuth\Infrastructure\Service\TokenService;
use Faker\Factory;
use PHPUnit\Framework\TestCase;
use Symfony\Component\EventDispatcher\EventDispatcher;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Core\User\UserProviderInterface;
use Symfony\Component\Security\Http\Authenticator\Passport\Badge\UserBadge;

class JWTAuthenticatorTest extends TestCase
{
    public function testSupportsRequestWithBearerToken(): void
    {
        $authenticator = $this->createAuthenticator(Factory::create()->email());

        self::assertTrue($authenticator->supports($this->createRequest('Bearer some.jwt.token')));
        self::assertFalse($authenticator->supports($this->createRequest(null)));
    }

    public function testAuthenticateBuildsPassportWithUserIdentifierFromToken(): void
    {
        $email = Factory::create()->email();
        $authenticator = $this->createAuthenticator($email);

        $passport = $authenticator->authenticate($this->createRequest('Bearer some.jwt.token'));

        /** @var UserBadge $badge */
        $badge = $passport->getBadge(UserBadge::class);
        self::assertEquals($email, $badge->getUserIdentifier());
        self::assertEquals('some.jwt.token', $passport->getAttribute('token'));
    }

    public function testStartRespondsUnauthorized(): void
    {
        $authenticator = $this->createAuthenticator(Factory::create()->email());

        $response = $authenticator->start($this->createRequest(null));

        self::assertEquals(Response::HTTP_UNAUTHORIZED, $response->getStatusCode());
        self::assertEquals('Bearer', $response->headers->get('WWW-Authenticate'));
    }

    public function testAuthenticationFailureRespondsUnauthorized(): void
    {
        $authenticator = $this->createAuthenticator(Factory::create()->email());

        $response = $authenticator->onAuthenticationFailure(
            $this->createRequest(null),
            new AuthenticationException()
        );

        self::assertEquals(Response::HTTP_UNAUTHORIZED, $response->getStatusCode());
        self::assertEquals('Bearer error="invalid_token"', $response->headers->get('WWW-Authenticate'));
    }

    public function testExpiredTokenFailureExposesExpirationInWwwAuthenticateHeader(): void
    {
        $authenticator = $this->createAuthenticator(Factory::create()->email());

        $response = $authenticator->onAuthenticationFailure(
            $this->createRequest(null),
            new JwtTokenExpiredException('expired')
        );

        self::assertEquals(Response::HTTP_UNAUTHORIZED, $response->getStatusCode());
        self::assertEquals(
            'Bearer error="invalid_token", error_description="The access token expired"',
            $response->headers->get('WWW-Authenticate')
        );
    }

    private function createAuthenticator(string $email, ?EventDispatcher $eventDispatcher = null): JWTAuthenticator
    {
        return new JWTAuthenticator(
            new BearerTokenExtractor(),
            $this->createMock(UserProviderInterface::class),
            $this->createTokenService($email),
            $eventDispatcher ?? new EventDispatcher()
        );
    }

    public function testNotFoundEventListenerCanReplaceStartResponse(): void
    {
        $eventDispatcher = new EventDispatcher();
        $eventDispatcher->addListener(JwtTokenNotFoundEvent::class, static function (JwtTokenNotFoundEvent $event): void {
            $event->setResponse(new JsonResponse(['error' => 'custom unauthorized'], Response::HTTP_UNAUTHORIZED));
        });

        $authenticator = $this->createAuthenticator(Factory::create()->email(), $eventDispatcher);

        $response = $authenticator->start($this->createRequest(null));

        self::assertStringContainsString('custom unauthorized', (string) $response->getContent());
    }

    public function testInvalidEventListenerCanReplaceFailureResponse(): void
    {
        $eventDispatcher = new EventDispatcher();
        $eventDispatcher->addListener(JwtTokenInvalidEvent::class, static function (JwtTokenInvalidEvent $event): void {
            $event->setResponse(new JsonResponse(['error' => 'custom invalid'], Response::HTTP_UNAUTHORIZED));
        });

        $authenticator = $this->createAuthenticator(Factory::create()->email(), $eventDispatcher);

        $response = $authenticator->onAuthenticationFailure(
            $this->createRequest(null),
            new AuthenticationException()
        );

        self::assertEquals(Response::HTTP_UNAUTHORIZED, $response->getStatusCode());
        self::assertStringContainsString('custom invalid', (string) $response->getContent());
    }

    public function testExpiredTokenFailureDispatchesExpiredEvent(): void
    {
        $dispatchedEvent = null;
        $eventDispatcher = new EventDispatcher();
        $eventDispatcher->addListener(JwtTokenExpiredEvent::class, static function (JwtTokenExpiredEvent $event) use (&$dispatchedEvent): void {
            $dispatchedEvent = $event;
        });

        $authenticator = $this->createAuthenticator(Factory::create()->email(), $eventDispatcher);

        $authenticator->onAuthenticationFailure(
            $this->createRequest(null),
            new JwtTokenExpiredException('expired')
        );

        self::assertInstanceOf(JwtTokenExpiredEvent::class, $dispatchedEvent);
        self::assertInstanceOf(JwtTokenExpiredException::class, $dispatchedEvent->getException());
    }

    public function testAuthenticatedEventListenerCanAddPassportAttributes(): void
    {
        $eventDispatcher = new EventDispatcher();
        $eventDispatcher->addListener(
            JwtTokenAuthenticatedEvent::class,
            static function (JwtTokenAuthenticatedEvent $event): void {
                $event->getPassport()->setAttribute('userIdentifier', $event->getJwtToken()->getUserIdentifier());
            }
        );

        $email = Factory::create()->email();
        $authenticator = $this->createAuthenticator($email, $eventDispatcher);

        $passport = $authenticator->authenticate($this->createRequest('Bearer some.jwt.token'));

        self::assertEquals($email, $passport->getAttribute('userIdentifier'));
    }

    private function createTokenService(string $email): TokenService
    {
        $jwtToken = new JwtToken(
            AlgorithmEnum::RS256,
            TokenTypeEnum::JWT,
            $email,
            (new DateTimeFactory())->getNowDate(time()),
            (new DateTimeFactory())->getNowDate(time() + 3600)
        );

        $jwtTokenExtractor = $this->createMock(JwtTokenExtractor::class);
        $jwtTokenExtractor->method('extract')->willReturn($jwtToken);

        return new TokenService(
            $this->createMock(JwtTokenCreator::class),
            $jwtTokenExtractor,
            $this->createMock(RefreshTokenCreator::class),
            $this->createMock(RefreshTokenRefresher::class)
        );
    }

    private function createRequest(?string $authorizationHeader): Request
    {
        $request = new Request();
        if (null !== $authorizationHeader) {
            $request->headers->set('Authorization', $authorizationHeader);
        }

        return $request;
    }
}
