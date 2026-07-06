<?php

declare(strict_types=1);

use Dimkinthepro\JwtAuth\Domain\Enum\AlgorithmEnum;
use Dimkinthepro\JwtAuth\Infrastructure\Service\KeyPairGeneratorService;
use Symfony\Component\Dotenv\Dotenv;

require dirname(__DIR__) . '/vendor/autoload.php';

exec('composer dump-autoload -o');

if (file_exists(dirname(__DIR__) . '/config/bootstrap.php')) {
    require dirname(__DIR__) . '/config/bootstrap.php';
} elseif (method_exists(Dotenv::class, 'bootEnv')) {
    (new Dotenv())->bootEnv(dirname(__DIR__) . '/.env.test');
}

generateTestKeyPair();

function generateTestKeyPair(): void
{
    $keysDir = dirname(__DIR__) . '/var/test-keys';
    if (is_file($keysDir . '/private.pem') && is_file($keysDir . '/public.pem')) {
        return;
    }

    if (false === is_dir($keysDir) && false === mkdir($keysDir, 0777, true)) {
        throw new RuntimeException(sprintf('Cannot create test keys directory: "%s"', $keysDir));
    }

    $keyPair = (new KeyPairGeneratorService())->generate(
        AlgorithmEnum::RS256,
        $_SERVER['JWT_AUTH_PASSPHRASE'] ?? ''
    );
    file_put_contents($keysDir . '/public.pem', $keyPair->publicKey);
    file_put_contents($keysDir . '/private.pem', $keyPair->privateKey);
}
