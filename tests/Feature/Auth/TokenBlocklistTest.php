<?php

declare(strict_types=1);

namespace Dimkinthepro\JwtAuth\Tests\Feature\Auth;

use Dimkinthepro\JwtAuth\Infrastructure\Enum\ResponseEnum;
use Dimkinthepro\JwtAuth\Infrastructure\Enum\TokenResponseEnum;
use Dimkinthepro\JwtAuth\Tests\Feature\ResetDatabaseTrait;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class TokenBlocklistTest extends WebTestCase
{
    use ResetDatabaseTrait;

    private const URL_API_LOGIN = '/api/user/login';
    private const URL_API_SESSIONS = '/api/sessions';
    private const USER_EMAIL = 'user@example.com';
    private const USER_PASSWORD = 'Password123!';

    private KernelBrowser $client;

    protected function setUp(): void
    {
        parent::setUp();

        $this->client = self::createClient();
        $this->resetDatabase();
    }

    public function testRevokedSessionAccessTokenDiesInstantly(): void
    {
        $tvJwt = $this->login('Samsung TV');
        $iphoneJwt = $this->login('iPhone 13 Pro');

        // The TV token works before revocation
        $this->requestSessions($tvJwt);
        self::assertEquals(Response::HTTP_OK, $this->client->getResponse()->getStatusCode());

        // Revoke the TV session from the iPhone
        $tvSessionId = $this->findSessionIdByDevice($iphoneJwt, 'Samsung TV');
        $this->client->request(
            Request::METHOD_DELETE,
            self::URL_API_SESSIONS . '/' . $tvSessionId,
            [],
            [],
            ['HTTP_AUTHORIZATION' => 'Bearer ' . $iphoneJwt]
        );
        self::assertEquals(Response::HTTP_NO_CONTENT, $this->client->getResponse()->getStatusCode());

        // The still-unexpired TV access token is rejected immediately
        $this->requestSessions($tvJwt);
        self::assertEquals(Response::HTTP_UNAUTHORIZED, $this->client->getResponse()->getStatusCode());

        // The iPhone token keeps working
        $this->requestSessions($iphoneJwt);
        self::assertEquals(Response::HTTP_OK, $this->client->getResponse()->getStatusCode());
    }

    public function testRevokeAllKillsEverySession(): void
    {
        $tvJwt = $this->login('Samsung TV');
        $iphoneJwt = $this->login('iPhone 13 Pro');

        $this->client->request(
            Request::METHOD_DELETE,
            self::URL_API_SESSIONS,
            [],
            [],
            ['HTTP_AUTHORIZATION' => 'Bearer ' . $iphoneJwt]
        );
        self::assertEquals(Response::HTTP_NO_CONTENT, $this->client->getResponse()->getStatusCode());

        // Both access tokens are dead instantly, including the one the request was made with
        $this->requestSessions($tvJwt);
        self::assertEquals(Response::HTTP_UNAUTHORIZED, $this->client->getResponse()->getStatusCode());
        $this->requestSessions($iphoneJwt);
        self::assertEquals(Response::HTTP_UNAUTHORIZED, $this->client->getResponse()->getStatusCode());

        // A fresh login works and sees no stale sessions
        $freshJwt = $this->login('iPhone 13 Pro');
        $sessions = $this->requestSessions($freshJwt);
        self::assertCount(1, $sessions);
    }

    private function login(string $deviceName): string
    {
        $this->client->request(
            Request::METHOD_POST,
            self::URL_API_LOGIN,
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            (string) json_encode([
                'email' => self::USER_EMAIL,
                'password' => self::USER_PASSWORD,
                'deviceName' => $deviceName,
            ])
        );

        $response = $this->client->getResponse();
        self::assertEquals(Response::HTTP_OK, $response->getStatusCode());
        $content = json_decode((string) $response->getContent(), true);

        return $content[ResponseEnum::DATA->value][TokenResponseEnum::TOKEN->value];
    }

    private function requestSessions(string $jwt): array
    {
        $this->client->request(
            Request::METHOD_GET,
            self::URL_API_SESSIONS,
            [],
            [],
            ['HTTP_AUTHORIZATION' => 'Bearer ' . $jwt]
        );

        $content = json_decode((string) $this->client->getResponse()->getContent(), true);

        return $content[ResponseEnum::DATA->value]['sessions'] ?? [];
    }

    private function findSessionIdByDevice(string $jwt, string $deviceName): string
    {
        $sessions = $this->requestSessions($jwt);

        return array_column($sessions, null, 'deviceName')[$deviceName]['sessionId'];
    }
}
