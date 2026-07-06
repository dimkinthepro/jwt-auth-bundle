<?php

declare(strict_types=1);

namespace Dimkinthepro\JwtAuth\Tests\Unit\Infrastructure\Factory;

use Dimkinthepro\JwtAuth\Infrastructure\Factory\DateTimeFactory;
use Faker\Factory;
use PHPUnit\Framework\TestCase;

class DateTimeFactoryTest extends TestCase
{
    public function testReturnsCurrentUtcDateWithoutTimestamp(): void
    {
        $now = (new DateTimeFactory())->getNowDate();

        self::assertEquals('UTC', $now->getTimezone()->getName());
        self::assertEqualsWithDelta(time(), $now->getTimestamp(), 2);
    }

    public function testReturnsDateForGivenTimestamp(): void
    {
        $timestamp = Factory::create()->unixTime();

        $date = (new DateTimeFactory())->getNowDate($timestamp);

        self::assertEquals($timestamp, $date->getTimestamp());
        self::assertEquals('UTC', $date->getTimezone()->getName());
    }
}
