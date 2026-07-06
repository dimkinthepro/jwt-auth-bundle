<?php

declare(strict_types=1);

namespace Dimkinthepro\JwtAuth\Infrastructure\Provider;

use Dimkinthepro\JwtAuth\Application\Component\Provider\DeviceContextProviderInterface;
use Dimkinthepro\JwtAuth\Domain\ValueObject\DeviceContext;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;

/**
 * Captures device metadata from the current request: the "deviceName" field of the JSON body
 * (native clients know their exact model), the User-Agent header and the client IP.
 */
readonly class DeviceContextProvider implements DeviceContextProviderInterface
{
    private const DEVICE_NAME_BODY_KEY = 'deviceName';
    private const DEVICE_NAME_MAX_LENGTH = 255;
    private const USER_AGENT_MAX_LENGTH = 500;
    private const CONTROL_CHARS_PATTERN = '/[\x00-\x1F\x7F]/u';

    public function __construct(
        private RequestStack $requestStack,
    ) {
    }

    public function getDeviceContext(): DeviceContext
    {
        $request = $this->requestStack->getCurrentRequest();
        if (null === $request) {
            return new DeviceContext();
        }

        return new DeviceContext(
            $this->extractDeviceName($request),
            $this->sanitize($request->headers->get('User-Agent'), self::USER_AGENT_MAX_LENGTH),
            $request->getClientIp(),
        );
    }

    private function extractDeviceName(Request $request): ?string
    {
        $content = json_decode($request->getContent(), true);
        if (false === \is_array($content)) {
            return null;
        }

        $deviceName = $content[self::DEVICE_NAME_BODY_KEY] ?? null;

        return \is_string($deviceName)
            ? $this->sanitize($deviceName, self::DEVICE_NAME_MAX_LENGTH)
            : null;
    }

    private function sanitize(?string $value, int $maxLength): ?string
    {
        if (null === $value) {
            return null;
        }

        $value = trim((string) preg_replace(self::CONTROL_CHARS_PATTERN, '', $value));

        return '' === $value ? null : mb_substr($value, 0, $maxLength);
    }
}
