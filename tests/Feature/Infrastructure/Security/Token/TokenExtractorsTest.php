<?php

declare(strict_types=1);

namespace Dimkinthepro\JwtAuth\Tests\Feature\Infrastructure\Security\Token;

use Dimkinthepro\JwtAuth\Application\Component\Manager\JwtTokenManager;
use Dimkinthepro\JwtAuth\Infrastructure\Security\Token\ChainTokenExtractor;
use Dimkinthepro\JwtAuth\Infrastructure\Security\Token\TokenExtractorInterface;
use Dimkinthepro\JwtAuth\Tests\Feature\ResetDatabaseTrait;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\BrowserKit\Cookie;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class TokenExtractorsTest extends WebTestCase
{
    use ResetDatabaseTrait;

    private const URL_API_TOKEN_REFRESH = '/api/token-refresh';

    private KernelBrowser $client;

    protected function setUp(): void
    {
        parent::setUp();

        $this->client = self::createClient();
        $this->resetDatabase();
    }

    public function testTokenExtractorIsAliasedToChain(): void
    {
        self::assertInstanceOf(
            ChainTokenExtractor::class,
            self::getContainer()->get(TokenExtractorInterface::class)
        );
    }

    public function testRequestIsAuthenticatedWithTokenFromCookie(): void
    {
        $this->client->getCookieJar()->set(new Cookie('jwt_token', $this->createJwtToken()));

        $this->requestProtectedEndpoint();

        // A valid cookie token authenticates the request; an invalid one would produce 401
        self::assertEquals(Response::HTTP_BAD_REQUEST, $this->client->getResponse()->getStatusCode());
    }

    public function testRequestIsAuthenticatedWithSplitCookieToken(): void
    {
        $encodedToken = $this->createJwtToken();
        $signatureOffset = (int) strrpos($encodedToken, '.');
        $this->client->getCookieJar()->set(new Cookie('jwt_hp', substr($encodedToken, 0, $signatureOffset)));
        $this->client->getCookieJar()->set(new Cookie('jwt_sig', substr($encodedToken, $signatureOffset + 1)));

        $this->requestProtectedEndpoint();

        self::assertEquals(Response::HTTP_BAD_REQUEST, $this->client->getResponse()->getStatusCode());
    }

    public function testRequestWithInvalidCookieTokenIsRejected(): void
    {
        $this->client->getCookieJar()->set(new Cookie('jwt_token', 'garbage-token'));

        $this->requestProtectedEndpoint();

        self::assertEquals(Response::HTTP_UNAUTHORIZED, $this->client->getResponse()->getStatusCode());
    }

    private function createJwtToken(): string
    {
        /** @var JwtTokenManager $creator */
        $creator = self::getContainer()->get(JwtTokenManager::class);

        // The test app enables the blocklist, and it requires a session id claim in every token
        return $creator->create('user@example.com', bin2hex(random_bytes(16)))->getEncodedToken();
    }

    /**
     * POST /api/token-refresh is behind the ^/api/ firewall: with a token present the authenticator
     * runs first, and only an authenticated request reaches the controller (which returns 400
     * for the empty refresh-token body used here).
     */
    private function requestProtectedEndpoint(): void
    {
        $this->client->request(
            Request::METHOD_POST,
            self::URL_API_TOKEN_REFRESH,
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            (string) json_encode(['refreshToken' => 'unknown-refresh-token'])
        );
    }
}
