<?php

declare(strict_types=1);

namespace App\Tests\Integration;

use App\Controller\BoardController;
use App\Entity\Board;
use App\Repository\BoardRepository;
use App\Service\BoardCreator;
use App\Service\BoardDeleter;
use App\Service\BoardUpdater;
use Doctrine\DBAL\Connection;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Tools\SchemaTool;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\DomCrawler\Field\ChoiceFormField;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

#[CoversClass(BoardController::class)]
#[CoversClass(BoardCreator::class)]
#[CoversClass(BoardDeleter::class)]
#[CoversClass(BoardUpdater::class)]
final class BoardControllerTest extends WebTestCase
{
    private KernelBrowser $client;
    private EntityManagerInterface $entityManager;
    private BoardRepository $boards;

    #[\Override]
    public static function setUpBeforeClass(): void
    {
        $options = [
                    'environment' => 'test',
                    'debug'       => true,
                   ];

        self::bootKernel($options);

        $container = self::getContainer();
        /** @var EntityManagerInterface $entityManager */
        $entityManager = $container->get(EntityManagerInterface::class);
        $connection = $entityManager->getConnection();

        self::waitForDatabase($connection);
        self::resetSchema($entityManager);

        $entityManager->close();
        self::ensureKernelShutdown();
    }

    #[\Override]
    protected function setUp(): void
    {
        self::ensureKernelShutdown();
        $options = [
                    'environment' => 'test',
                    'debug'       => true,
                   ];
        $this->client = static::createClient($options);
        $this->client->disableReboot();
        $this->client->catchExceptions(true);

        $container = self::getContainer();
        /** @var EntityManagerInterface $entityManager */
        $entityManager = $container->get(EntityManagerInterface::class);
        $this->entityManager = $entityManager;
        /** @var BoardRepository $boards */
        $boards = $container->get(BoardRepository::class);
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

        unset($this->client, $this->entityManager, $this->boards);

        parent::tearDown();
    }

    #[Test]
    public function itCreatesBoardFromForm(): void
    {
        $crawler = $this->client->request('GET', '/boards');

        self::assertResponseIsSuccessful();

        $form = $crawler->selectButton('Create')->form();
        $formName = $form->getName();
        $titleField = sprintf('%s[title]', $formName);
        $turretField = sprintf('%s[isTurretMode]', $formName);

        $form[$titleField] = 'Alpha Board';
        $turretInput = $form[$turretField];
        self::assertInstanceOf(ChoiceFormField::class, $turretInput);
        $turretInput->tick();

        $this->client->submit($form);

        self::assertResponseRedirects('/boards');
        $this->client->followRedirect();

        $board = $this->boards->findOneBy(['title' => 'Alpha Board']);

        self::assertNotNull($board);
        self::assertTrue($board->isTurretMode());
    }

    #[Test]
    public function itUpdatesBoardAndCanUnsetTurretMode(): void
    {
        $board = $this->createBoard('Beta Board', true);
        $crawler = $this->client->request('GET', '/boards');
        self::assertResponseIsSuccessful();

        $formNode = $crawler->filter(sprintf('form[name="board_%d"]', $board->getId()));
        self::assertCount(1, $formNode);

        $form = $formNode->form();
        $formName = $form->getName();
        $titleField = sprintf('%s[title]', $formName);
        $turretField = sprintf('%s[isTurretMode]', $formName);

        $form[$titleField] = 'Beta Board Updated';
        $turretInput = $form[$turretField];
        self::assertInstanceOf(ChoiceFormField::class, $turretInput);
        $turretInput->untick();

        $this->client->submit($form);

        self::assertResponseRedirects('/boards');
        $this->client->followRedirect();

        $updated = $this->boards->find($board->getId());

        self::assertNotNull($updated);
        self::assertSame('Beta Board Updated', $updated->getTitle());
        self::assertFalse($updated->isTurretMode());
    }

    #[Test]
    #[DataProvider('invalidTitleScenarioProvider')]
    public function itRejectsInvalidTitleType(string $scenario): void
    {
        $board = null;
        if ($scenario === 'update') {
            $board = $this->createBoard('Gamma Board', false);
        }

        $crawler = $this->client->request('GET', '/boards');
        self::assertResponseIsSuccessful();

        if ($scenario === 'create') {
            $form = $crawler->selectButton('Create')->form();
            $formName = $form->getName();
            /** @var array<string, array<string, mixed>> $values */
            $values = $form->getPhpValues();
            $values[$formName]['title'] = ['invalid'];

            $this->client->request('POST', '/boards', $values);

            self::assertResponseStatusCodeSame(Response::HTTP_BAD_REQUEST);
            self::assertCount(0, $this->boards->findAll());

            return;
        }

        self::assertNotNull($board);
        $action = sprintf('/boards/%d', $board->getId());
        $formNode = $crawler->filter(sprintf('form[name="board_%d"]', $board->getId()));
        self::assertCount(1, $formNode);

        $form = $formNode->form();
        $formName = $form->getName();
        /** @var array<string, array<string, mixed>> $values */
        $values = $form->getPhpValues();
        $values[$formName]['title'] = ['invalid'];

        $this->client->request('PATCH', $action, $values);

        self::assertResponseStatusCodeSame(Response::HTTP_BAD_REQUEST);

        $updated = $this->boards->find($board->getId());
        self::assertNotNull($updated);
        self::assertSame('Gamma Board', $updated->getTitle());
    }

    #[Test]
    public function itDeletesBoardFromForm(): void
    {
        $board = $this->createBoard('Delete Board', false);
        $crawler = $this->client->request('GET', '/boards');

        self::assertResponseIsSuccessful();

        $selector = sprintf('form[name="board_delete_%d"]', $board->getId());
        $formNode = $crawler->filter($selector);
        self::assertCount(1, $formNode);

        $form = $formNode->form();
        $this->client->submit($form);

        self::assertResponseRedirects('/boards');
        $this->client->followRedirect();

        self::assertNull($this->boards->find($board->getId()));
    }

    #[Test]
    public function itRejectsDeleteWithoutFormSubmission(): void
    {
        $board = $this->createBoard('Reject Delete Board', false);
        $action = sprintf('/boards/%d', $board->getId());

        $this->client->request('DELETE', $action);

        self::assertResponseStatusCodeSame(Response::HTTP_BAD_REQUEST);
        self::assertNotNull($this->boards->find($board->getId()));
    }

    #[Test]
    public function itReturnsNotFoundWhenUpdatingMissingBoard(): void
    {
        $this->client->catchExceptions(false);
        $this->expectException(NotFoundHttpException::class);

        $this->client->request('PATCH', '/boards/99999');
    }

    #[Test]
    public function itReturnsNotFoundWhenDeletingMissingBoard(): void
    {
        $this->client->catchExceptions(false);
        $this->expectException(NotFoundHttpException::class);

        $this->client->request('DELETE', '/boards/99999');
    }

    /**
     * @return array<string, array{string}>
     */
    public static function invalidTitleScenarioProvider(): array
    {
        return [
                'create' => ['create'],
                'update' => ['update'],
               ];
    }

    private static function resetSchema(EntityManagerInterface $entityManager): void
    {
        $metadata = $entityManager->getMetadataFactory()->getAllMetadata();
        if ($metadata === []) {
            return;
        }

        $schemaTool = new SchemaTool($entityManager);
        $schemaTool->dropSchema($metadata);
        $schemaTool->createSchema($metadata);
    }

    private function createBoard(string $title, bool $isTurretMode): Board
    {
        $board = new Board();
        $board->setTitle($title);
        $board->setIsTurretMode($isTurretMode);

        $this->entityManager->persist($board);
        $this->entityManager->flush();

        return $board;
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
