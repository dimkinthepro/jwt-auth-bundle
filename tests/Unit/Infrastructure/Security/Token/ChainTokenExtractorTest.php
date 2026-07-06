<?php

declare(strict_types=1);

namespace Dimkinthepro\JwtAuth\Tests\Unit\Infrastructure\Security\Token;

use Dimkinthepro\JwtAuth\Infrastructure\Security\Token\BearerTokenExtractor;
use Dimkinthepro\JwtAuth\Infrastructure\Security\Token\ChainTokenExtractor;
use Dimkinthepro\JwtAuth\Infrastructure\Security\Token\CookieTokenExtractor;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Request;

class ChainTokenExtractorTest extends TestCase
{
    public function testFirstMatchingExtractorWins(): void
    {
        $chain = new ChainTokenExtractor([
            new BearerTokenExtractor(),
            new CookieTokenExtractor('jwt_token'),
        ]);

        $request = new Request(cookies: ['jwt_token' => 'cookie.jwt.token']);
        $request->headers->set('Authorization', 'Bearer header.jwt.token');

        self::assertEquals('header.jwt.token', $chain->extractToken($request));
    }

    public function testFallsBackToNextExtractor(): void
    {
        $chain = new ChainTokenExtractor([
            new BearerTokenExtractor(),
            new CookieTokenExtractor('jwt_token'),
        ]);

        $request = new Request(cookies: ['jwt_token' => 'cookie.jwt.token']);

        self::assertEquals('cookie.jwt.token', $chain->extractToken($request));
    }

    public function testReturnsNullWhenNoExtractorMatches(): void
    {
        $chain = new ChainTokenExtractor([new BearerTokenExtractor(), new CookieTokenExtractor('jwt_token')]);

        self::assertNull($chain->extractToken(new Request()));
    }

    public function testEmptyChainReturnsNull(): void
    {
        self::assertNull((new ChainTokenExtractor([]))->extractToken(new Request()));
    }
}
