<?php

declare(strict_types=1);

namespace Dimkinthepro\JwtAuth\Infrastructure\Factory;

class DateTimeFactory
{
    /**
     * @throws \Exception
     */
    public function getNowDate(int $timestamp = null): \DateTimeImmutable
    {
        $now = new \DateTimeImmutable('now', new \DateTimeZone('UTC'));
        if (null !== $timestamp) {
            return $now->setTimestamp($timestamp);
        }

        return $now;
    }
}
