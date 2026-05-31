<?php

declare(strict_types=1);

namespace App\Tests\Integration;

use App\Board\Domain\Board;
use App\Board\Domain\BoardRow;
use App\Board\UI\Http\BoardController;
use App\Entity\Task;
use App\Enum\TaskRiskLevel;
use App\Service\TaskShootFormFactory;
use App\Service\TaskShooter;
use DateInterval;
use DateTimeImmutable;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\Attributes\UsesClass;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

#[CoversClass(BoardController::class)]
#[UsesClass(TaskShootFormFactory::class)]
#[UsesClass(TaskShooter::class)]
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
        self::assertSelectorExists(sprintf('form[name="task_shoot_%d"]', $task->getId()));
    }

    #[Test]
    public function itShootsTaskFromBoardDetail(): void
    {
        // Arrange
        $board = $this->createBoard('Shooting Board', true);
        $boardId = $board->getId();
        self::assertNotNull($boardId);

        $boardRow = $this->createBoardRow($board, 'Sports');
        $task = $this->createTask($boardRow, 'Workout', false);
        $taskId = $task->getId();
        self::assertNotNull($taskId);

        $crawler = $this->client->request('GET', sprintf('/boards/%d', $boardId));
        self::assertResponseIsSuccessful();
        $form = $crawler->filter(sprintf('form[name="task_shoot_%d"]', $taskId))->form();

        // Act
        $this->client->submit($form);
        $this->client->followRedirect();

        // Assert
        self::assertResponseIsSuccessful();
        self::assertSelectorTextNotContains('main', 'Workout');

        $this->entityManager->clear();
        self::assertNull($this->entityManager->getRepository(Task::class)->find($taskId));
    }

    #[Test]
    public function itRespawnsImmediatelyConfiguredTaskByUpdatingSpawnTiming(): void
    {
        // Arrange
        $board = $this->createBoard('Respawn Board', true);
        $boardId = $board->getId();
        self::assertNotNull($boardId);

        $boardRow = $this->createBoardRow($board, 'Household');
        $task = $this->createTask(
            $boardRow,
            'Mop the floor',
            true,
            new DateTimeImmutable('2026-04-08T00:00:00+00:00'),
            15,
            45
        );
        $taskId = $task->getId();
        self::assertNotNull($taskId);

        $crawler = $this->client->request('GET', sprintf('/boards/%d', $boardId));
        self::assertResponseIsSuccessful();
        $form = $crawler->filter(sprintf('form[name="task_shoot_%d"]', $taskId))->form();
        $shotStartedAt = new DateTimeImmutable();

        // Act
        $this->client->submit($form);
        $shotEndedAt = new DateTimeImmutable();
        $this->client->followRedirect();

        // Assert
        self::assertResponseIsSuccessful();
        self::assertSelectorTextContains('main', 'Mop the floor');

        $this->entityManager->clear();
        $updatedTask = $this->entityManager->getRepository(Task::class)->find($taskId);
        self::assertInstanceOf(Task::class, $updatedTask);
        self::assertSame($taskId, $updatedTask->getId());

        $spawnTimestamp = $updatedTask->getSpawnDate()->getTimestamp();
        self::assertGreaterThanOrEqual(
            $shotStartedAt->add(new DateInterval('PT15M'))->getTimestamp(),
            $spawnTimestamp
        );
        self::assertLessThanOrEqual(
            $shotEndedAt->add(new DateInterval('PT15M'))->getTimestamp(),
            $spawnTimestamp
        );
        self::assertSame(
            $updatedTask->getSpawnDate()->add(new DateInterval('PT45M'))->getTimestamp(),
            $updatedTask->getBaseDate()->getTimestamp()
        );
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

    private function createBoardRow(Board $board, string $title): BoardRow
    {
        $boardRow = new BoardRow();
        $boardRow->setTitle($title);
        $boardRow->setRowNumber(1);
        $board->addBoardRow($boardRow);

        $this->entityManager->flush();

        return $boardRow;
    }

    private function createTask(
        BoardRow $boardRow,
        string $title,
        bool $respawnImmediatelyAfterDeath,
        ?DateTimeImmutable $spawnDate = null,
        int $respawnsIn = 10,
        int $reachesBaseIn = 30
    ): Task {
        $task = new Task();
        $task->setTitle($title);
        $task->setRiskLevel(TaskRiskLevel::GREEN);
        $task->setSpawnDate($spawnDate ?? new DateTimeImmutable('2026-04-08T00:00:00+00:00'));
        $task->setRespawnsIn($respawnsIn);
        $task->setSpawnsEvery(20);
        $task->setReachesBaseIn($reachesBaseIn);
        $task->setRespawnImmediatelyAfterDeath($respawnImmediatelyAfterDeath);
        $boardRow->addTask($task);

        $this->entityManager->flush();

        return $task;
    }
}
