<?php

declare(strict_types=1);

namespace Dimkinthepro\JwtAuth\Infrastructure\Console;

use Dimkinthepro\JwtAuth\Domain\Enum\AlgorithmEnum;
use Dimkinthepro\JwtAuth\Infrastructure\Service\KeyPairGeneratorService;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Filesystem\Filesystem;

#[AsCommand(
    name: 'dimkinthepro:jwt-auth:generate-key-pair',
    description: 'Generate key pair',
)]
class GenerateKeyPairCommand extends Command
{
    public function __construct(
        private readonly Filesystem $filesystem,
        private readonly KeyPairGeneratorService $keyPairGeneratorService,
        private readonly string $authPrivateKey,
        private readonly string $authPublicKey,
        private readonly string $authPassphrase,
        private readonly string $authAlgorithm
    ) {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        if (true === $this->filesystem->exists($this->authPublicKey)) {
            $io->error(sprintf('Public key already exists in path: "%s"', $this->authPublicKey));

            return Command::FAILURE;
        }

        if (true === $this->filesystem->exists($this->authPrivateKey)) {
            $io->error(sprintf('Private key already exists in path: "%s"', $this->authPrivateKey));

            return Command::FAILURE;
        }

        try {
            $algorithm = AlgorithmEnum::from($this->authAlgorithm);
            $keyPair = $this->keyPairGeneratorService->generate($algorithm, $this->authPassphrase);
            $this->filesystem->dumpFile($this->authPublicKey, $keyPair->publicKey);
            $this->filesystem->dumpFile($this->authPrivateKey, $keyPair->privateKey);
        } catch (\Throwable $e) {
            $io->error(sprintf(
                'Key pair generation error: "%s", trace: "%s"',
                $e->getMessage(),
                $e->getTraceAsString()
            ));

            return Command::FAILURE;
        }

        $io->success(sprintf(
            'Key pair successfully generated. Public key path: "%s", private key path: "%s".',
            $this->authPublicKey,
            $this->authPrivateKey
        ));

        return Command::SUCCESS;
    }
}
