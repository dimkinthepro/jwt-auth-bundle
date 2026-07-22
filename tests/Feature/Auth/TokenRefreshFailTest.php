<?php

declare(strict_types=1);

namespace Dimkinthepro\JwtAuth\Tests\Feature\Auth;

use Dimkinthepro\JwtAuth\Application\Component\Manager\RefreshTokenManager;
use Dimkinthepro\JwtAuth\Infrastructure\Encoder\Base64FieldsEncoder;
use Dimkinthepro\JwtAuth\Tests\Feature\ResetDatabaseTrait;
use Doctrine\DBAL\Connection;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class TokenRefreshFailTest extends WebTestCase
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

    public function testUnknownTokenIsRejectedWithBadRequest(): void
    {
        $encodedToken = (new Base64FieldsEncoder())->encode(bin2hex(random_bytes(128)));
        $this->requestRefresh(['refreshToken' => $encodedToken]);

        self::assertEquals(Response::HTTP_BAD_REQUEST, $this->client->getResponse()->getStatusCode());
    }

    public function testGarbageTokenIsRejectedWithBadRequest(): void
    {
        $this->requestRefresh(['refreshToken' => 'not валидный base64!!!']);

        self::assertEquals(Response::HTTP_BAD_REQUEST, $this->client->getResponse()->getStatusCode());
    }

    public function testExpiredTokenIsRejectedWithBadRequest(): void
    {
        $encodedToken = $this->createRefreshToken();
        $this->expireAllRefreshTokens();

        $this->requestRefresh(['refreshToken' => $encodedToken]);

        self::assertEquals(Response::HTTP_BAD_REQUEST, $this->client->getResponse()->getStatusCode());
    }

    public function testRotatedTokenCannotBeReused(): void
    {
        $encodedToken = $this->createRefreshToken();

        $this->requestRefresh(['refreshToken' => $encodedToken]);
        self::assertEquals(Response::HTTP_OK, $this->client->getResponse()->getStatusCode());

        $this->requestRefresh(['refreshToken' => $encodedToken]);
        self::assertEquals(Response::HTTP_BAD_REQUEST, $this->client->getResponse()->getStatusCode());
    }

    public function testMissingTokenFieldIsRejectedWithUnprocessableEntity(): void
    {
        $this->requestRefresh([]);

        self::assertEquals(
            Response::HTTP_UNPROCESSABLE_ENTITY,
            $this->client->getResponse()->getStatusCode()
        );
    }

    public function testMalformedJsonIsRejectedWithBadRequest(): void
    {
        $this->client->request(
            Request::METHOD_POST,
            self::URL_API_TOKEN_REFRESH,
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            'not json at all'
        );

        self::assertEquals(Response::HTTP_BAD_REQUEST, $this->client->getResponse()->getStatusCode());
    }

    private function requestRefresh(array $body): void
    {
        $this->client->request(
            Request::METHOD_POST,
            self::URL_API_TOKEN_REFRESH,
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            (string) json_encode($body)
        );
    }

    private function createRefreshToken(): string
    {
        /** @var RefreshTokenManager $creator */
        $creator = self::getContainer()->get(RefreshTokenManager::class);

        return $creator->create('user@example.com')->getEncodedToken();
    }

    private function expireAllRefreshTokens(): void
    {
        /** @var Connection $connection */
        $connection = self::getContainer()->get('doctrine.dbal.default_connection');
        $connection->executeStatement(
            'UPDATE refresh_token SET valid_until = ?',
            [(new \DateTimeImmutable('-1 day'))->format('Y-m-d H:i:s')]
        );

        /** @var EntityManagerInterface $entityManager */
        $entityManager = self::getContainer()->get('doctrine.orm.entity_manager');
        $entityManager->clear();
    }
}
