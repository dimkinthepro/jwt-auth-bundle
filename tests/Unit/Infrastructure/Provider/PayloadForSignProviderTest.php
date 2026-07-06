<?php

declare(strict_types=1);

namespace Dimkinthepro\JwtAuth\Tests\Unit\Infrastructure\Provider;

use Dimkinthepro\JwtAuth\Domain\Entity\JwtToken;
use Dimkinthepro\JwtAuth\Domain\Enum\AlgorithmEnum;
use Dimkinthepro\JwtAuth\Domain\Enum\TokenTypeEnum;
use Dimkinthepro\JwtAuth\Infrastructure\Encoder\Base64FieldsEncoder;
use Dimkinthepro\JwtAuth\Infrastructure\Factory\DateTimeFactory;
use Dimkinthepro\JwtAuth\Infrastructure\Provider\PayloadForSignProvider;
use Faker\Factory;
use PHPUnit\Framework\TestCase;

class PayloadForSignProviderTest extends TestCase
{
    public function testPayloadIsEncodedHeaderAndPayloadJoinedByDot(): void
    {
        $token = new JwtToken(
            AlgorithmEnum::RS256,
            TokenTypeEnum::JWT,
            Factory::create()->email(),
            (new DateTimeFactory())->getNowDate(time()),
            (new DateTimeFactory())->getNowDate(time() + 3600)
        );

        $fieldsEncoder = new Base64FieldsEncoder();
        $payload = (new PayloadForSignProvider($fieldsEncoder))->getPayload($token);

        $expectedHeader = $fieldsEncoder->encode((string) json_encode($token->getHeader(), \JSON_UNESCAPED_SLASHES));
        $expectedPayload = $fieldsEncoder->encode((string) json_encode($token->getPayload(), \JSON_UNESCAPED_SLASHES));
        self::assertEquals(\sprintf('%s.%s', $expectedHeader, $expectedPayload), $payload);
    }

    public function testRawSigningInputIsPreferredForDecodedTokens(): void
    {
        $token = new JwtToken(
            AlgorithmEnum::RS256,
            TokenTypeEnum::JWT,
            Factory::create()->email(),
            (new DateTimeFactory())->getNowDate(time()),
            (new DateTimeFactory())->getNowDate(time() + 3600)
        );
        $rawSigningInput = 'rawHeader.rawPayload';
        $token->setSigningInput($rawSigningInput);

        $payload = (new PayloadForSignProvider(new Base64FieldsEncoder()))->getPayload($token);

        self::assertEquals($rawSigningInput, $payload);
    }
}
