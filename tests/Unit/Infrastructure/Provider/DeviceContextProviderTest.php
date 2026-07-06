<?php

declare(strict_types=1);

namespace Dimkinthepro\JwtAuth\Tests\Unit\Infrastructure\Provider;

use Dimkinthepro\JwtAuth\Infrastructure\Provider\DeviceContextProvider;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;

class DeviceContextProviderTest extends TestCase
{
    public function testCapturesDeviceNameUserAgentAndIp(): void
    {
        $request = new Request(
            server: [
                'HTTP_USER_AGENT' => 'Mozilla/5.0 (iPhone)',
                'REMOTE_ADDR' => '203.0.113.10',
            ],
            content: (string) json_encode(['email' => 'user@example.com', 'deviceName' => 'iPhone 13 Иры'])
        );

        $context = $this->createProvider($request)->getDeviceContext();

        self::assertEquals('iPhone 13 Иры', $context->deviceName);
        self::assertEquals('Mozilla/5.0 (iPhone)', $context->userAgent);
        self::assertEquals('203.0.113.10', $context->ip);
    }

    public function testReturnsEmptyContextWithoutRequest(): void
    {
        $context = (new DeviceContextProvider(new RequestStack()))->getDeviceContext();

        self::assertNull($context->deviceName);
        self::assertNull($context->userAgent);
        self::assertNull($context->ip);
    }

    /**
     * @dataProvider providerMalformedDeviceNames
     */
    public function testMalformedDeviceNameIsIgnoredOrSanitized(string $content, ?string $expectedDeviceName): void
    {
        $context = $this->createProvider(new Request(content: $content))->getDeviceContext();

        self::assertEquals($expectedDeviceName, $context->deviceName);
    }

    public function providerMalformedDeviceNames(): array
    {
        return [
            'notJson' => ['not json', null],
            'deviceNameIsArray' => [(string) json_encode(['deviceName' => ['a' => 'b']]), null],
            'deviceNameIsNumber' => [(string) json_encode(['deviceName' => 42]), null],
            'emptyDeviceName' => [(string) json_encode(['deviceName' => '  ']), null],
            'controlCharsStripped' => [(string) json_encode(['deviceName' => "iPhone\x00\x1F 13"]), 'iPhone 13'],
            'longNameTruncated' => [
                (string) json_encode(['deviceName' => str_repeat('a', 300)]),
                str_repeat('a', 255),
            ],
        ];
    }

    private function createProvider(Request $request): DeviceContextProvider
    {
        $requestStack = new RequestStack();
        $requestStack->push($request);

        return new DeviceContextProvider($requestStack);
    }
}
