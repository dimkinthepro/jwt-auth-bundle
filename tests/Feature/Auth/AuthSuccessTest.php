<?php

declare(strict_types=1);

namespace Dimkinthepro\JwtAuth\Tests\Feature\Auth;

use Dimkinthepro\JwtAuth\Infrastructure\Enum\ResponseEnum;
use Dimkinthepro\JwtAuth\Infrastructure\Enum\TokenResponseEnum;
use Dimkinthepro\JwtAuth\Tests\Feature\EventListener\TestAuthenticationSuccessListener;
use Dimkinthepro\JwtAuth\Tests\Feature\ResetDatabaseTrait;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class AuthSuccessTest extends WebTestCase
{
    use ResetDatabaseTrait;

    private const URL_API_LOGIN = '/api/user/login';
    private const URL_API_TOKEN_REFRESH = '/api/token-refresh';
    private const USER_EMAIL = 'user@example.com';
    private const USER_PASSWORD = 'Password123!';

    private KernelBrowser $client;

    protected function setUp(): void
    {
        parent::setUp();

        $this->client = self::createClient();
        $this->resetDatabase();
    }

    public function testSuccessLogin(): void
    {
        $content = $this->login();

        self::assertIsString($content[ResponseEnum::DATA->value][TokenResponseEnum::TOKEN->value]);
        self::assertIsString($content[ResponseEnum::DATA->value][TokenResponseEnum::REFRESH_TOKEN->value]);
        // Added by TestAuthenticationSuccessListener via JwtAuthenticationSuccessEvent
        self::assertEquals(
            self::USER_EMAIL,
            $content[ResponseEnum::DATA->value][TestAuthenticationSuccessListener::USER_EMAIL_KEY]
        );
    }

    public function testTokenRefreshRotatesTokens(): void
    {
        $loginContent = $this->login();
        $refreshToken = $loginContent[ResponseEnum::DATA->value][TokenResponseEnum::REFRESH_TOKEN->value];

        $this->client->request(
            Request::METHOD_POST,
            self::URL_API_TOKEN_REFRESH,
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            (string) json_encode(['refreshToken' => $refreshToken])
        );

        $response = $this->client->getResponse();
        $content = json_decode((string) $response->getContent(), true);

        self::assertEquals(Response::HTTP_OK, $response->getStatusCode());
        self::assertIsString($content[ResponseEnum::DATA->value][TokenResponseEnum::TOKEN->value]);
        self::assertIsString($content[ResponseEnum::DATA->value][TokenResponseEnum::REFRESH_TOKEN->value]);
        self::assertNotEquals(
            $refreshToken,
            $content[ResponseEnum::DATA->value][TokenResponseEnum::REFRESH_TOKEN->value]
        );
    }

    private function login(): array
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
            ])
        );

        $response = $this->client->getResponse();
        self::assertEquals(Response::HTTP_OK, $response->getStatusCode());

        return json_decode((string) $response->getContent(), true);
    }
}
