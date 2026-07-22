<?php

declare(strict_types=1);

namespace Dimkinthepro\JwtAuth\Tests\Unit\Application\UseCase\Token;

use Dimkinthepro\JwtAuth\Application\Component\Blocklist\TokenBlocklistInterface;
use Dimkinthepro\JwtAuth\Application\Component\Decoder\JwtTokenDecoderInterface;
use Dimkinthepro\JwtAuth\Application\Component\Event\JwtTokenDecodedEvent;
use Dimkinthepro\JwtAuth\Application\Component\Validator\JwtTokenValidatorInterface;
use Dimkinthepro\JwtAuth\Application\UseCase\Token\JwtTokenDecoder;
use Dimkinthepro\JwtAuth\Domain\Entity\JwtToken;
use Dimkinthepro\JwtAuth\Domain\Enum\AlgorithmEnum;
use Dimkinthepro\JwtAuth\Domain\Enum\TokenDictionaryEnum;
use Dimkinthepro\JwtAuth\Domain\Enum\TokenTypeEnum;
use Dimkinthepro\JwtAuth\Infrastructure\Blocklist\NullTokenBlocklist;
use Dimkinthepro\JwtAuth\Infrastructure\Exception\InvalidTokenException;
use Dimkinthepro\JwtAuth\Infrastructure\Factory\DateTimeFactory;
use Faker\Factory;
use PHPUnit\Framework\TestCase;
use Symfony\Component\EventDispatcher\EventDispatcher;

class JwtTokenExtractorTest extends TestCase
{
    public function testExtractReturnsDecodedAndValidatedToken(): void
    {
        $jwtToken = $this->createToken();
        $extractor = $this->createExtractor($jwtToken, new EventDispatcher());

        self::assertSame($jwtToken, $extractor->decodeTokenFromString('encoded.jwt.token'));
    }

    public function testDecodedEventListenerCanRejectToken(): void
    {
        $eventDispatcher = new EventDispatcher();
        $eventDispatcher->addListener(JwtTokenDecodedEvent::class, static function (JwtTokenDecodedEvent $event): void {
            if (null === $event->getJwtToken()->getClaim('role')) {
                $event->markAsInvalid();
            }
        });

        $extractor = $this->createExtractor($this->createToken(), $eventDispatcher);

        self::expectException(InvalidTokenException::class);
        self::expectExceptionMessage('Token rejected by listener');

        $extractor->decodeTokenFromString('encoded.jwt.token');
    }

    private function createToken(): JwtToken
    {
        return new JwtToken(
            AlgorithmEnum::RS256,
            TokenTypeEnum::JWT,
            Factory::create()->email(),
            (new DateTimeFactory())->getNowDate(time()),
            (new DateTimeFactory())->getNowDate(time() + 3600)
        );
    }

    public function testTokenOfBlockedSessionIsRejected(): void
    {
        $sessionId = bin2hex(random_bytes(16));
        $jwtToken = new JwtToken(
            AlgorithmEnum::RS256,
            TokenTypeEnum::JWT,
            Factory::create()->email(),
            (new DateTimeFactory())->getNowDate(time()),
            (new DateTimeFactory())->getNowDate(time() + 3600),
            [TokenDictionaryEnum::SESSION_ID->value => $sessionId]
        );

        $blocklist = new class implements TokenBlocklistInterface {
            public function block(string $sessionId): void
            {
            }

            public function isBlocked(string $sessionId): bool
            {
                return true;
            }
        };

        $extractor = $this->createExtractor($jwtToken, new EventDispatcher(), $blocklist, true);

        self::expectException(InvalidTokenException::class);
        self::expectExceptionMessage('Token revoked');

        $extractor->decodeTokenFromString('encoded.jwt.token');
    }

    public function testTokenWithoutSessionIdIsRejectedWhenBlocklistIsEnabled(): void
    {
        // A token without a session id can never be revoked, so it must not bypass the blocklist
        $extractor = $this->createExtractor($this->createToken(), new EventDispatcher(), null, true);

        self::expectException(InvalidTokenException::class);
        self::expectExceptionMessage('Token has no session id claim');

        $extractor->decodeTokenFromString('encoded.jwt.token');
    }

    public function testTokenWithoutSessionIdIsAcceptedWhenBlocklistIsDisabled(): void
    {
        $jwtToken = $this->createToken();
        $extractor = $this->createExtractor($jwtToken, new EventDispatcher());

        self::assertSame($jwtToken, $extractor->decodeTokenFromString('encoded.jwt.token'));
    }

    private function createExtractor(
        JwtToken $jwtToken,
        EventDispatcher $eventDispatcher,
        ?TokenBlocklistInterface $blocklist = null,
        bool $blocklistEnabled = false
    ): JwtTokenDecoder {
        $decoder = $this->createMock(JwtTokenDecoderInterface::class);
        $decoder->method('decode')->willReturn($jwtToken);

        return new JwtTokenDecoder(
            $decoder,
            $this->createMock(JwtTokenValidatorInterface::class),
            $eventDispatcher,
            $blocklist ?? new NullTokenBlocklist(),
            $blocklistEnabled
        );
    }
}
