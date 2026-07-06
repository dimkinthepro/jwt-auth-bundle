<?php

declare(strict_types=1);

namespace Dimkinthepro\JwtAuth\Tests\Unit\Infrastructure\Security\Token;

use Dimkinthepro\JwtAuth\Infrastructure\Security\Token\SplitCookieTokenExtractor;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Request;

class SplitCookieTokenExtractorTest extends TestCase
{
    private const PAYLOAD_COOKIE = 'jwt_hp';
    private const SIGNATURE_COOKIE = 'jwt_sig';

    /**
     * @dataProvider providerCookies
     */
    public function testExtractToken(array $cookies, ?string $expectedToken): void
    {
        $extractor = new SplitCookieTokenExtractor(self::PAYLOAD_COOKIE, self::SIGNATURE_COOKIE);

        self::assertEquals($expectedToken, $extractor->extractToken(new Request(cookies: $cookies)));
    }

    public function providerCookies(): array
    {
        return [
            'bothCookies' => [
                [self::PAYLOAD_COOKIE => 'header.payload', self::SIGNATURE_COOKIE => 'signature'],
                'header.payload.signature',
            ],
            'payloadOnly' => [[self::PAYLOAD_COOKIE => 'header.payload'], null],
            'signatureOnly' => [[self::SIGNATURE_COOKIE => 'signature'], null],
            'emptySignature' => [[self::PAYLOAD_COOKIE => 'header.payload', self::SIGNATURE_COOKIE => ''], null],
            'noCookies' => [[], null],
        ];
    }
}
