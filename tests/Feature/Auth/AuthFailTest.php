<?php

declare(strict_types=1);

namespace Dimkinthepro\JwtAuth\Tests\Feature\Auth;

use Dimkinthepro\JwtAuth\Tests\Feature\ResetDatabaseTrait;
use Faker\Factory;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class AuthFailTest extends WebTestCase
{
    use ResetDatabaseTrait;

    private const URL_API_LOGIN = '/api/user/login';

    private KernelBrowser $client;

    protected function setUp(): void
    {
        parent::setUp();

        $this->client = self::createClient();
        $this->client->catchExceptions(true);
        $this->resetDatabase();
    }

    public function testLoginWithoutBodyIsRejected(): void
    {
        $this->client->request(
            Request::METHOD_POST,
            self::URL_API_LOGIN,
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
        );

        self::assertEquals(Response::HTTP_BAD_REQUEST, $this->client->getResponse()->getStatusCode());
    }

    public function testLoginWithInvalidCredentialsIsRejected(): void
    {
        $this->client->request(
            Request::METHOD_POST,
            self::URL_API_LOGIN,
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            (string) json_encode([
                'email' => Factory::create()->email(),
                'password' => Factory::create()->password(),
            ])
        );

        self::assertEquals(Response::HTTP_UNAUTHORIZED, $this->client->getResponse()->getStatusCode());
    }
}
