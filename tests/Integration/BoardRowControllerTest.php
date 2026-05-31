<?php

declare(strict_types=1);

namespace App\Tests\Integration;

use App\Board\Application\CreateBoardRow;
use App\Board\Domain\Board;
use App\Board\Domain\BoardRow;
use App\Board\UI\Http\BoardRowController;
use App\Entity\Task;
use App\Enum\TaskRiskLevel;
use App\Service\BoardDetailFormErrors;
use App\Service\BoardDetailPageRenderer;
use App\Service\BoardRowCreator;
use App\Service\BoardRowDeleter;
use App\Service\BoardRowFormFactory;
use App\Service\BoardRowUpdater;
use App\Service\TaskFormFactory;
use App\Service\TaskShootFormFactory;
use DateTimeImmutable;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\Attributes\UsesClass;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

#[CoversClass(BoardRowController::class)]
#[UsesClass(BoardDetailFormErrors::class)]
#[UsesClass(BoardDetailPageRenderer::class)]
#[UsesClass(BoardRowCreator::class)]
#[UsesClass(BoardRowDeleter::class)]
#[UsesClass(BoardRowFormFactory::class)]
#[UsesClass(BoardRowUpdater::class)]
#[UsesClass(CreateBoardRow::class)]
#[UsesClass(TaskFormFactory::class)]
#[UsesClass(TaskShootFormFactory::class)]
final class BoardRowControllerTest extends AbstractDatabaseWebTestCase
{
    #[Test]
    public function itAddsRowFromBoardDetail(): void
    {
        // Arrange
        $board = $this->createBoard('Row Create Board', false);
        $boardId = $board->getId();
        self::assertNotNull($boardId);

        $crawler = $this->client->request('GET', sprintf('/boards/%d', $boardId));
        self::assertResponseIsSuccessful();
        $form = $crawler->filter('form[name="board_row_create"]')->form();
        $formName = $form->getName();
        $form[sprintf('%s[title]', $formName)] = 'Errands';

        // Act
        $this->client->submit($form);
        $this->client->followRedirect();

        // Assert
        self::assertResponseIsSuccessful();
        self::assertSelectorTextContains('main', 'Errands');

        $this->entityManager->clear();
        $updatedBoard = $this->boards->find($boardId);
        self::assertInstanceOf(Board::class, $updatedBoard);
        $rows = array_values($updatedBoard->getBoardRows()->toArray());
        self::assertCount(1, $rows);
        self::assertSame('Errands', $rows[0]->getTitle());
        self::assertSame(1, $rows[0]->getRowNumber());
    }

    #[Test]
    public function itUpdatesRowFromBoardDetail(): void
    {
        // Arrange
        $board = $this->createBoard('Row Update Board', false);
        $boardId = $board->getId();
        self::assertNotNull($boardId);

        $boardRow = $this->createBoardRow($board, 'Errands');
        $boardRowId = $boardRow->getId();
        self::assertNotNull($boardRowId);

        $crawler = $this->client->request('GET', sprintf('/boards/%d', $boardId));
        self::assertResponseIsSuccessful();
        $form = $crawler->filter(sprintf('form[name="board_row_%d"]', $boardRowId))->form();
        $formName = $form->getName();
        $form[sprintf('%s[title]', $formName)] = 'Admin';

        // Act
        $this->client->submit($form);
        $this->client->followRedirect();

        // Assert
        self::assertResponseIsSuccessful();
        self::assertSelectorTextContains('main', 'Admin');
        self::assertSelectorTextNotContains('main', 'Errands');

        $this->entityManager->clear();
        $updatedRow = $this->entityManager->getRepository(BoardRow::class)->find($boardRowId);
        self::assertInstanceOf(BoardRow::class, $updatedRow);
        self::assertSame('Admin', $updatedRow->getTitle());
    }

    #[Test]
    public function itRemovesRowFromBoardDetailAndDetachesTasks(): void
    {
        // Arrange
        $board = $this->createBoard('Row Delete Board', false);
        $boardId = $board->getId();
        self::assertNotNull($boardId);

        $boardRow = $this->createBoardRow($board, 'Errands');
        $boardRowId = $boardRow->getId();
        self::assertNotNull($boardRowId);

        $task = $this->createTask('Pay bills');
        $boardRow->addTask($task);
        $this->entityManager->flush();
        $taskId = $task->getId();
        self::assertNotNull($taskId);

        $crawler = $this->client->request('GET', sprintf('/boards/%d', $boardId));
        self::assertResponseIsSuccessful();
        $form = $crawler->filter(sprintf('form[name="board_row_delete_%d"]', $boardRowId))->form();

        // Act
        $this->client->submit($form);
        $this->client->followRedirect();

        // Assert
        self::assertResponseIsSuccessful();
        self::assertSelectorTextNotContains('main', 'Errands');
        self::assertSelectorTextNotContains('main', 'Pay bills');

        $this->entityManager->clear();
        self::assertNull($this->entityManager->getRepository(BoardRow::class)->find($boardRowId));

        $updatedTask = $this->entityManager->getRepository(Task::class)->find($taskId);
        self::assertInstanceOf(Task::class, $updatedTask);
        self::assertNull($updatedTask->getBoardRow());
    }

    #[Test]
    public function itReturnsNotFoundForRowFromAnotherBoard(): void
    {
        // Arrange
        $board = $this->createBoard('Row Owner Board', false);
        $otherBoard = $this->createBoard('Other Board', false);
        $otherRow = $this->createBoardRow($otherBoard, 'Other');
        $boardId = $board->getId();
        $otherRowId = $otherRow->getId();
        self::assertNotNull($boardId);
        self::assertNotNull($otherRowId);

        $this->client->catchExceptions(false);
        $this->expectException(NotFoundHttpException::class);

        // Act
        $this->client->request('PATCH', sprintf('/boards/%d/rows/%d', $boardId, $otherRowId));
    }

    private function createBoardRow(Board $board, string $title): BoardRow
    {
        $boardRow = new BoardRow();
        $boardRow->setTitle($title);
        $boardRow->setRowNumber(1);
        $board->addBoardRow($boardRow);

        $this->entityManager->flush();

        return $boardRow;
    }

    private function createTask(string $title): Task
    {
        $task = new Task();
        $task->setTitle($title);
        $task->setRiskLevel(TaskRiskLevel::GREEN);
        $task->setSpawnDate(new DateTimeImmutable('2026-04-08T00:00:00+00:00'));
        $task->setRespawnsIn(10);
        $task->setSpawnsEvery(20);
        $task->setReachesBaseIn(30);

        return $task;
    }
}
