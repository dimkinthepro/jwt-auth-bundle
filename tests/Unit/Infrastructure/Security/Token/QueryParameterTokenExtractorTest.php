<?php

declare(strict_types=1);

namespace Dimkinthepro\JwtAuth\Tests\Unit\Infrastructure\Security\Token;

use Dimkinthepro\JwtAuth\Infrastructure\Security\Token\QueryParameterTokenExtractor;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Request;

class QueryParameterTokenExtractorTest extends TestCase
{
    private const PARAMETER_NAME = 'jwt_token';

    /**
     * @dataProvider providerQueryParameters
     */
    public function testExtractToken(array $query, ?string $expectedToken): void
    {
        $request = new Request($query);

        self::assertEquals(
            $expectedToken,
            (new QueryParameterTokenExtractor(self::PARAMETER_NAME))->extractToken($request)
        );
    }

    public function providerQueryParameters(): array
    {
        return [
            'tokenParameter' => [[self::PARAMETER_NAME => 'some.jwt.token'], 'some.jwt.token'],
            'missingParameter' => [[], null],
            'emptyParameter' => [[self::PARAMETER_NAME => ''], null],
        ];
    }
}
