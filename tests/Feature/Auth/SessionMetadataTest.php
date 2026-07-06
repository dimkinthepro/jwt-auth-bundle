<?php

declare(strict_types=1);

namespace Dimkinthepro\JwtAuth\Tests\Feature\Auth;

use Dimkinthepro\JwtAuth\Application\Component\Manager\RefreshTokenManager;
use Dimkinthepro\JwtAuth\Tests\Feature\ResetDatabaseTrait;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class SessionMetadataTest extends WebTestCase
{
    use ResetDatabaseTrait;

    private const URL_API_LOGIN = '/api/user/login';
    private const USER_EMAIL = 'user@example.com';
    private const USER_PASSWORD = 'Password123!';
    private const DEVICE_NAME = 'iPhone 13 Pro';
    private const USER_AGENT = 'MyApp/1.0 (iOS 17)';

    private KernelBrowser $client;

    protected function setUp(): void
    {
        parent::setUp();

        $this->client = self::createClient();
        $this->resetDatabase();
    }

    public function testLoginStoresSessionWithDeviceMetadata(): void
    {
        $this->login(self::DEVICE_NAME);

        /** @var RefreshTokenManager $manager */
        $manager = self::getContainer()->get(RefreshTokenManager::class);
        $sessions = $manager->findAllByUserIdentifier(self::USER_EMAIL);

        self::assertCount(1, $sessions);
        $session = $sessions[0];
        self::assertEquals(self::DEVICE_NAME, $session->getDeviceName());
        self::assertEquals(self::USER_AGENT, $session->getUserAgent());
        self::assertNotEmpty($session->getIp());
        self::assertMatchesRegularExpression('/^[0-9a-f]{32}$/', $session->getSessionId());
        self::assertEquals($session->getCreatedAt(), $session->getLastUsedAt());
    }

    public function testEachLoginCreatesSeparateSession(): void
    {
        $this->login('iPhone 13 Pro');
        $this->login('Samsung TV');

        /** @var RefreshTokenManager $manager */
        $manager = self::getContainer()->get(RefreshTokenManager::class);
        $sessions = $manager->findAllByUserIdentifier(self::USER_EMAIL);

        self::assertCount(2, $sessions);
        $deviceNames = array_map(static fn ($session) => $session->getDeviceName(), $sessions);
        self::assertContains('iPhone 13 Pro', $deviceNames);
        self::assertContains('Samsung TV', $deviceNames);
        self::assertNotEquals($sessions[0]->getSessionId(), $sessions[1]->getSessionId());
    }

    private function login(string $deviceName): void
    {
        $this->client->request(
            Request::METHOD_POST,
            self::URL_API_LOGIN,
            [],
            [],
            [
                'CONTENT_TYPE' => 'application/json',
                'HTTP_USER_AGENT' => self::USER_AGENT,
            ],
            (string) json_encode([
                'email' => self::USER_EMAIL,
                'password' => self::USER_PASSWORD,
                'deviceName' => $deviceName,
            ])
        );

        self::assertEquals(Response::HTTP_OK, $this->client->getResponse()->getStatusCode());
    }
}
