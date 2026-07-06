<?php

declare(strict_types=1);

namespace Dimkinthepro\JwtAuth\Tests\Unit\Infrastructure\Security\Token;

use Dimkinthepro\JwtAuth\Infrastructure\Security\Token\BearerTokenExtractor;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Request;

class BearerTokenExtractorTest extends TestCase
{
    /**
     * @dataProvider providerAuthorizationHeaders
     */
    public function testExtractToken(?string $authorizationHeader, ?string $expectedToken): void
    {
        $request = new Request();
        if (null !== $authorizationHeader) {
            $request->headers->set('Authorization', $authorizationHeader);
        }

        self::assertEquals($expectedToken, (new BearerTokenExtractor())->extractToken($request));
    }

    public function providerAuthorizationHeaders(): array
    {
        return [
            'bearerToken' => ['Bearer some.jwt.token', 'some.jwt.token'],
            'caseInsensitiveScheme' => ['bearer some.jwt.token', 'some.jwt.token'],
            'missingHeader' => [null, null],
            'emptyHeader' => ['', null],
            'wrongScheme' => ['Basic dXNlcjpwYXNz', null],
            'schemeWithoutToken' => ['Bearer', null],
            'tooManyParts' => ['Bearer one two', null],
        ];
    }
}
