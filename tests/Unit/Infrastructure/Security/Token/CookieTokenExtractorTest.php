<?php

declare(strict_types=1);

namespace Dimkinthepro\JwtAuth\Tests\Unit\Infrastructure\Security\Token;

use Dimkinthepro\JwtAuth\Infrastructure\Security\Token\CookieTokenExtractor;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Request;

class CookieTokenExtractorTest extends TestCase
{
    private const COOKIE_NAME = 'jwt_token';

    /**
     * @dataProvider providerCookies
     */
    public function testExtractToken(array $cookies, ?string $expectedToken): void
    {
        $request = new Request(cookies: $cookies);

        self::assertEquals($expectedToken, (new CookieTokenExtractor(self::COOKIE_NAME))->extractToken($request));
    }

    public function providerCookies(): array
    {
        return [
            'tokenCookie' => [[self::COOKIE_NAME => 'some.jwt.token'], 'some.jwt.token'],
            'missingCookie' => [[], null],
            'emptyCookie' => [[self::COOKIE_NAME => ''], null],
            'otherCookie' => [['session' => 'abc'], null],
        ];
    }
}
