<?php

declare(strict_types=1);

namespace Dimkinthepro\JwtAuth\Tests\Feature\Auth;

use Dimkinthepro\JwtAuth\Application\Component\Manager\RefreshTokenManager;
use Dimkinthepro\JwtAuth\Infrastructure\Enum\ResponseEnum;
use Dimkinthepro\JwtAuth\Infrastructure\Enum\TokenResponseEnum;
use Dimkinthepro\JwtAuth\Tests\Feature\ResetDatabaseTrait;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class SessionsEndpointTest extends WebTestCase
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

    public function testSessionListMarksCurrentSession(): void
    {
        $this->login('Samsung TV');
        $iphoneJwt = $this->login('iPhone 13 Pro');

        $sessions = $this->requestSessions($iphoneJwt);

        self::assertCount(2, $sessions);
        $byDevice = array_column($sessions, null, 'deviceName');
        self::assertTrue($byDevice['iPhone 13 Pro']['current']);
        self::assertFalse($byDevice['Samsung TV']['current']);
        self::assertNotEmpty($byDevice['Samsung TV']['sessionId']);
        self::assertNotEmpty($byDevice['Samsung TV']['createdAt']);
    }

    public function testRevokeAnotherDeviceSession(): void
    {
        $this->login('Samsung TV');
        $iphoneJwt = $this->login('iPhone 13 Pro');

        $sessions = $this->requestSessions($iphoneJwt);
        $tvSessionId = array_column($sessions, null, 'deviceName')['Samsung TV']['sessionId'];

        $this->client->request(
            Request::METHOD_DELETE,
            self::URL_API_SESSIONS . '/' . $tvSessionId,
            [],
            [],
            ['HTTP_AUTHORIZATION' => 'Bearer ' . $iphoneJwt]
        );
        self::assertEquals(Response::HTTP_NO_CONTENT, $this->client->getResponse()->getStatusCode());

        $remainingSessions = $this->requestSessions($iphoneJwt);
        self::assertCount(1, $remainingSessions);
        self::assertEquals('iPhone 13 Pro', $remainingSessions[0]['deviceName']);
    }

    public function testRevokeUnknownSessionRespondsNotFound(): void
    {
        $jwt = $this->login('iPhone 13 Pro');

        $this->client->request(
            Request::METHOD_DELETE,
            self::URL_API_SESSIONS . '/' . bin2hex(random_bytes(16)),
            [],
            [],
            ['HTTP_AUTHORIZATION' => 'Bearer ' . $jwt]
        );

        self::assertEquals(Response::HTTP_NOT_FOUND, $this->client->getResponse()->getStatusCode());
    }

    public function testForeignSessionIsReportedAsNotFound(): void
    {
        // Another user logs in: the memory provider has a single user, so simulate by direct manager call
        $anotherSession = self::getContainer()
            ->get(RefreshTokenManager::class)
            ->create('another@example.com');
        $jwt = $this->login('iPhone 13 Pro');

        $this->client->request(
            Request::METHOD_DELETE,
            self::URL_API_SESSIONS . '/' . $anotherSession->getSessionId(),
            [],
            [],
            ['HTTP_AUTHORIZATION' => 'Bearer ' . $jwt]
        );

        self::assertEquals(Response::HTTP_NOT_FOUND, $this->client->getResponse()->getStatusCode());
    }

    public function testSessionListWithoutTokenIsUnauthorized(): void
    {
        $this->client->request(Request::METHOD_GET, self::URL_API_SESSIONS);

        self::assertEquals(Response::HTTP_UNAUTHORIZED, $this->client->getResponse()->getStatusCode());
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

        $response = $this->client->getResponse();
        self::assertEquals(Response::HTTP_OK, $response->getStatusCode());
        $content = json_decode((string) $response->getContent(), true);

        return $content[ResponseEnum::DATA->value]['sessions'];
    }
}
