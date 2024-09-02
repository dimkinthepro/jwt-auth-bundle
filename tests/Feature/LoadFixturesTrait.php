<?php

declare(strict_types=1);

namespace Dimkinthepro\JwtAuth\Tests\Feature;

use App\Infrastructure\Faker\Provider\ArrayCollectionProvider;
use App\Infrastructure\Faker\Provider\EmailValueObjectProvider;
use App\Infrastructure\Faker\Provider\UuidObjectProvider;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Tools\SchemaTool;
use Faker\Factory as FakerGeneratorFactory;
use Faker\Generator as FakerGenerator;
use Nelmio\Alice\Faker\Provider\AliceProvider;
use Nelmio\Alice\Loader\NativeLoader;

trait LoadFixturesTrait
{
    protected array $loadedFixtures;

    protected function loadFixtures(array $fixtureFiles): void
    {
        $projectDir = static::$kernel->getProjectDir();
        /** @var EntityManagerInterface $entityManager */
        $entityManager = self::getContainer()->get('doctrine.orm.entity_manager');

        // Flush database
        $metaData = $entityManager->getMetadataFactory()->getAllMetadata();
        $schemaTool = new SchemaTool($entityManager);
        $schemaTool->dropDatabase();
        $schemaTool->createSchema($metaData);

        $fixtureFiles = array_map(function ($path) use ($projectDir) {
            return $projectDir . '/' . $path;
        }, $fixtureFiles);

        $this->loadedFixtures = $this->getLoader()->loadFiles($fixtureFiles)->getObjects();
        foreach ($this->loadedFixtures as $entity) {
            $entityManager->persist($entity);
        }

        $entityManager->flush();
    }

    private function getLoader(): NativeLoader
    {
        return new NativeLoader($this->createFakerGenerator());
    }

    private function createFakerGenerator(): FakerGenerator
    {
        $generator = FakerGeneratorFactory::create('en_US');
        $generator->addProvider(new AliceProvider());
        $generator->addProvider(new EmailValueObjectProvider());
        $generator->addProvider(new UuidObjectProvider());
        $generator->addProvider(new ArrayCollectionProvider());
        $generator->seed();

        return $generator;
    }
}
