<?php

declare(strict_types=1);

namespace App\Tests\Integration;

use App\Board\Domain\BoardRow;
use App\Board\UI\Http\BoardController;
use App\Entity\Task;
use App\Enum\TaskRiskLevel;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

#[CoversClass(BoardController::class)]
final class BoardControllerTest extends AbstractDatabaseWebTestCase
{
    #[Test]
    public function itShowsBoardById(): void
    {
        // Arrange
        $board = $this->createBoard('Show Board', true);
        $boardId = $board->getId();
        self::assertNotNull($boardId);

        $boardRow = new BoardRow();
        $boardRow->setTitle('Sports');
        $boardRow->setRowNumber(1);
        $board->addBoardRow($boardRow);

        $task = new Task();
        $task->setTitle('Workout');
        $task->setRiskLevel(TaskRiskLevel::GREEN);
        $task->setSpawnDate(new \DateTimeImmutable('2026-04-08T00:00:00+00:00'));
        $task->setRespawnsIn(10);
        $task->setSpawnsEvery(20);
        $task->setReachesBaseIn(30);
        $boardRow->addTask($task);

        $this->entityManager->flush();

        // Act
        $this->client->request('GET', sprintf('/boards/%d', $boardId));

        // Assert
        self::assertResponseIsSuccessful();
        self::assertSelectorTextContains('h1', 'Show Board');
        self::assertSelectorTextContains('main', 'Sports');
        self::assertSelectorTextContains('main', 'Workout');
    }

    #[Test]
    public function itReturnsNotFoundForMissingBoard(): void
    {
        // Arrange
        $this->client->catchExceptions(false);
        $this->expectException(NotFoundHttpException::class);

        // Act
        $this->client->request('GET', '/boards/99999');
    }
}
