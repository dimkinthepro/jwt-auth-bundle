<?php

declare(strict_types=1);

namespace Dimkinthepro\JwtAuth\Tests\Feature\Auth;

use Dimkinthepro\JwtAuth\Infrastructure\Enum\ResponseEnum;
use Faker\Factory;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class AuthFailTest extends WebTestCase
{
    private const URL_API_LOGIN = '/api/user/login';
    private const MSG_INVALID_REQUEST = 'Invalid request.';
    private const MSG_INVALID_CREDENTIALS = 'Bad credentials.';

    public function testLoginGetInvalidRequest(): void
    {
        $client = self::createClient();
        $client->request(
            Request::METHOD_POST,
            self::URL_API_LOGIN,
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
        );

        $response = $client->getResponse();
        $content = json_decode((string) $response->getContent(), true);

        self::assertEquals(Response::HTTP_UNPROCESSABLE_ENTITY, $response->getStatusCode());
        self::assertEquals(self::MSG_INVALID_REQUEST, $content[ResponseEnum::MESSAGE->value]);
    }

    public function testLoginGetInvalidCredentials(): void
    {
        $email = Factory::create()->email();
        $password = Factory::create()->password();

        $client = self::createClient();
        $client->request(
            Request::METHOD_POST,
            self::URL_API_LOGIN,
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            (string) json_encode([
                'email' => $email,
                'password' => $password,
            ])
        );

        $response = $client->getResponse();
        $content = json_decode((string) $response->getContent(), true);

        self::assertEquals(Response::HTTP_UNAUTHORIZED, $response->getStatusCode());
        self::assertEquals(self::MSG_INVALID_CREDENTIALS, $content[ResponseEnum::MESSAGE->value]);
    }
}
