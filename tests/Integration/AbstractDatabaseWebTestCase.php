<?php

declare(strict_types=1);

namespace App\Tests\Integration;

use App\Board\Domain\Board;
use App\Board\Infrastructure\Persistence\DoctrineBoardRepository;
use Doctrine\DBAL\Connection;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Tools\SchemaTool;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpKernel\KernelInterface;

abstract class AbstractDatabaseWebTestCase extends WebTestCase
{
    protected KernelBrowser $client;
    protected KernelInterface $kernelInstance;
    protected EntityManagerInterface $entityManager;
    protected DoctrineBoardRepository $boards;

    #[\Override]
    public static function setUpBeforeClass(): void
    {
        $kernel = self::createKernel([
                                      'environment' => 'test',
                                      'debug'       => true,
                                     ]);
        $kernel->boot();

        /** @var ManagerRegistry $registry */
        $registry = $kernel->getContainer()->get('doctrine');
        $entityManager = $registry->getManager();
        self::assertInstanceOf(EntityManagerInterface::class, $entityManager);

        self::waitForDatabase($entityManager->getConnection());
        self::resetSchema($entityManager);

        $entityManager->close();
        $kernel->shutdown();
    }

    #[\Override]
    protected function setUp(): void
    {
        $this->kernelInstance = self::createKernel([
                                                    'environment' => 'test',
                                                    'debug'       => true,
                                                   ]);
        $this->kernelInstance->boot();

        $this->client = new KernelBrowser($this->kernelInstance);
        self::getClient($this->client);
        $this->client->disableReboot();
        $this->client->catchExceptions(true);

        /** @var ManagerRegistry $registry */
        $registry = $this->kernelInstance->getContainer()->get('doctrine');
        $entityManager = $registry->getManager();
        self::assertInstanceOf(EntityManagerInterface::class, $entityManager);
        $this->entityManager = $entityManager;

        $boards = $entityManager->getRepository(Board::class);
        self::assertInstanceOf(DoctrineBoardRepository::class, $boards);
        $this->boards = $boards;

        $connection = $this->entityManager->getConnection();
        if (!$connection->isTransactionActive()) {
            $connection->beginTransaction();
        }
    }

    #[\Override]
    protected function tearDown(): void
    {
        $connection = $this->entityManager->getConnection();
        if ($connection->isTransactionActive()) {
            $connection->rollBack();
        }

        $this->entityManager->clear();
        $this->entityManager->close();
        $this->kernelInstance->shutdown();

        parent::tearDown();
    }

    protected function createBoard(string $title, bool $isTurretMode): Board
    {
        $board = new Board();
        $board->setTitle($title);
        $board->setIsTurretMode($isTurretMode);

        $this->entityManager->persist($board);
        $this->entityManager->flush();

        return $board;
    }

    private static function resetSchema(EntityManagerInterface $entityManager): void
    {
        $metadata = $entityManager->getMetadataFactory()->getAllMetadata();
        if ($metadata === []) {
            return;
        }

        $schemaTool = new SchemaTool($entityManager);
        $schemaTool->dropDatabase();
        $schemaTool->createSchema($metadata);
    }

    private static function waitForDatabase(Connection $connection): void
    {
        $attempts = 0;
        $maxAttempts = 25;
        $sleepUs = 200_000;

        while (true) {
            try {
                $connection->executeQuery('SELECT 1')->fetchOne();
                return;
            } catch (\Throwable $exception) {
                $attempts++;
                if ($attempts >= $maxAttempts) {
                    throw $exception;
                }

                $connection->close();
                usleep($sleepUs);
            }
        }
    }
}
