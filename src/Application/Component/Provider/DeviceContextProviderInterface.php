<?php

declare(strict_types=1);

namespace Dimkinthepro\JwtAuth\Application\Component\Provider;

use Dimkinthepro\JwtAuth\Domain\ValueObject\DeviceContext;

interface DeviceContextProviderInterface
{
    public function getDeviceContext(): DeviceContext;
}
