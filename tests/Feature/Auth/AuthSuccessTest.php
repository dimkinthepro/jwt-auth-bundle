<?php

declare(strict_types=1);

namespace DimkinThePro\JwtAuth\Tests\Feature\Auth;

use App\Domain\Entity\User;
use DimkinThePro\JwtAuth\Infrastructure\Enum\ResponseEnum;
use DimkinThePro\JwtAuth\Infrastructure\Enum\TokenResponseEnum;
use DimkinThePro\JwtAuth\Tests\Feature\LoadFixturesTrait;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class AuthSuccessTest extends WebTestCase
{
    use LoadFixturesTrait;

    private const URL_API_LOGIN = '/api/user/login';

    private KernelBrowser $client;

    protected function setUp(): void
    {
        parent::setUp();

        $this->client = self::createClient();
        $this->loadFixtures([
            'src/App/Test/Fixture/user.yaml',
        ]);
    }

    public function testSuccessLogin(): void
    {
        /** @var User $loadedUser */
        $loadedUser = $this->loadedFixtures['user_1'];

        $this->client->request(
            Request::METHOD_POST,
            self::URL_API_LOGIN,
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            (string) json_encode([
                'email' => $loadedUser->getEmail(),
                'password' => $loadedUser->getPassword(),
            ])
        );

        $response = $this->client->getResponse();
        $content = json_decode((string) $response->getContent(), true);

        self::assertEquals(Response::HTTP_OK, $response->getStatusCode());
        self::assertIsArray($content[ResponseEnum::DATA->value]);
        self::assertIsString($content[ResponseEnum::DATA->value][TokenResponseEnum::TOKEN->value]);
        self::assertIsString($content[ResponseEnum::DATA->value][TokenResponseEnum::REFRESH_TOKEN->value]);
    }
}
