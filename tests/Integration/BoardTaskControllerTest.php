<?php

declare(strict_types=1);

namespace App\Tests\Integration;

use App\Board\Application\CreateTask;
use App\Board\Domain\Board;
use App\Board\Domain\BoardRow;
use App\Board\UI\Http\BoardTaskController;
use App\Entity\Task;
use App\Enum\TaskRiskLevel;
use App\Service\BoardDetailFormErrors;
use App\Service\BoardDetailPageRenderer;
use App\Service\BoardRowFormFactory;
use App\Service\TaskCreator;
use App\Service\TaskFormFactory;
use App\Service\TaskRemover;
use App\Service\TaskShootFormFactory;
use App\Service\TaskShooter;
use App\Service\TaskUpdater;
use DateInterval;
use DateTimeImmutable;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\Attributes\UsesClass;
use Symfony\Component\DomCrawler\Field\ChoiceFormField;
use Symfony\Component\DomCrawler\Form;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

#[CoversClass(BoardTaskController::class)]
#[UsesClass(BoardDetailFormErrors::class)]
#[UsesClass(BoardDetailPageRenderer::class)]
#[UsesClass(BoardRowFormFactory::class)]
#[UsesClass(CreateTask::class)]
#[UsesClass(TaskCreator::class)]
#[UsesClass(TaskFormFactory::class)]
#[UsesClass(TaskRemover::class)]
#[UsesClass(TaskShootFormFactory::class)]
#[UsesClass(TaskShooter::class)]
#[UsesClass(TaskUpdater::class)]
final class BoardTaskControllerTest extends AbstractDatabaseWebTestCase
{
    #[Test]
    public function itAddsTaskFromBoardDetail(): void
    {
        // Arrange
        $board = $this->createBoard('Task Create Board', false);
        $boardId = $board->getId();
        self::assertNotNull($boardId);
        $boardRow = $this->createBoardRow($board, 'Sports');
        $boardRowId = $boardRow->getId();
        self::assertNotNull($boardRowId);

        $crawler = $this->client->request('GET', sprintf('/boards/%d', $boardId));
        self::assertResponseIsSuccessful();
        $form = $crawler->filter(sprintf('form[name="task_create_%d"]', $boardRowId))->form();
        $this->fillTaskForm($form, 'Workout', TaskRiskLevel::YELLOW, true, true);

        // Act
        $this->client->submit($form);
        $this->client->followRedirect();

        // Assert
        self::assertResponseIsSuccessful();
        self::assertSelectorTextContains('main', 'Workout');

        $this->entityManager->clear();
        $tasks = $this->entityManager->getRepository(Task::class)->findAll();
        self::assertCount(1, $tasks);

        $createdTask = $tasks[0];
        self::assertInstanceOf(Task::class, $createdTask);
        self::assertSame('Workout', $createdTask->getTitle());
        self::assertSame(TaskRiskLevel::YELLOW, $createdTask->getRiskLevel());
        self::assertSame(15, $createdTask->getRespawnsIn());
        self::assertSame(45, $createdTask->getSpawnsEvery());
        self::assertSame(90, $createdTask->getReachesBaseIn());
        self::assertTrue($createdTask->hasShield());
        self::assertTrue($createdTask->isRespawnImmediatelyAfterDeath());
        self::assertSame(2, $createdTask->getSpeedFactor());
        self::assertSame($boardRowId, $createdTask->getBoardRow()?->getId());
    }

    #[Test]
    public function itUpdatesTaskFromBoardDetail(): void
    {
        // Arrange
        $board = $this->createBoard('Task Update Board', false);
        $boardId = $board->getId();
        self::assertNotNull($boardId);
        $boardRow = $this->createBoardRow($board, 'Sports');
        $task = $this->createTask($boardRow, 'Workout', false);
        $taskId = $task->getId();
        self::assertNotNull($taskId);

        $crawler = $this->client->request('GET', sprintf('/boards/%d', $boardId));
        self::assertResponseIsSuccessful();
        $form = $crawler->filter(sprintf('form[name="task_%d"]', $taskId))->form();
        $this->fillTaskForm($form, 'Stretching', TaskRiskLevel::RED, true, false);

        // Act
        $this->client->submit($form);
        $this->client->followRedirect();

        // Assert
        self::assertResponseIsSuccessful();
        self::assertSelectorTextContains('main', 'Stretching');
        self::assertSelectorTextNotContains('main', 'Workout');

        $this->entityManager->clear();
        $updatedTask = $this->entityManager->getRepository(Task::class)->find($taskId);
        self::assertInstanceOf(Task::class, $updatedTask);
        self::assertSame('Stretching', $updatedTask->getTitle());
        self::assertSame(TaskRiskLevel::RED, $updatedTask->getRiskLevel());
        self::assertTrue($updatedTask->hasShield());
        self::assertFalse($updatedTask->isRespawnImmediatelyAfterDeath());
    }

    #[Test]
    public function itRemovesTaskFromBoardDetailWithoutDeletingTaskRecord(): void
    {
        // Arrange
        $board = $this->createBoard('Task Remove Board', false);
        $boardId = $board->getId();
        self::assertNotNull($boardId);
        $boardRow = $this->createBoardRow($board, 'Household');
        $task = $this->createTask($boardRow, 'Pay bills', false);
        $taskId = $task->getId();
        self::assertNotNull($taskId);

        $crawler = $this->client->request('GET', sprintf('/boards/%d', $boardId));
        self::assertResponseIsSuccessful();
        $form = $crawler->filter(sprintf('form[name="task_delete_%d"]', $taskId))->form();

        // Act
        $this->client->submit($form);
        $this->client->followRedirect();

        // Assert
        self::assertResponseIsSuccessful();
        self::assertSelectorTextNotContains('main', 'Pay bills');

        $this->entityManager->clear();
        $updatedTask = $this->entityManager->getRepository(Task::class)->find($taskId);
        self::assertInstanceOf(Task::class, $updatedTask);
        self::assertNull($updatedTask->getBoardRow());
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
        $updatedTask = $this->entityManager->getRepository(Task::class)->find($taskId);
        self::assertInstanceOf(Task::class, $updatedTask);
        self::assertSame($taskId, $updatedTask->getId());
        self::assertTrue($updatedTask->isCompleted());
        self::assertNotNull($updatedTask->getCompletedAt());
        self::assertNotNull($updatedTask->getBoardRow());
        self::assertCount(1, $this->entityManager->getRepository(Task::class)->findAll());
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
        self::assertTrue($updatedTask->isCompleted());
        self::assertNotNull($updatedTask->getCompletedAt());
        self::assertCount(1, $this->entityManager->getRepository(Task::class)->findAll());

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
    public function itReturnsNotFoundForTaskFromAnotherBoard(): void
    {
        // Arrange
        $board = $this->createBoard('Task Owner Board', false);
        $otherBoard = $this->createBoard('Other Task Board', false);
        $otherBoardRow = $this->createBoardRow($otherBoard, 'Other');
        $otherTask = $this->createTask($otherBoardRow, 'Other Task', false);
        $boardId = $board->getId();
        $otherTaskId = $otherTask->getId();
        self::assertNotNull($boardId);
        self::assertNotNull($otherTaskId);

        $this->client->catchExceptions(false);
        $this->expectException(NotFoundHttpException::class);

        // Act
        $this->client->request('PATCH', sprintf('/boards/%d/tasks/%d', $boardId, $otherTaskId));
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

    private function fillTaskForm(
        Form $form,
        string $title,
        TaskRiskLevel $riskLevel,
        bool $hasShield,
        bool $respawnImmediatelyAfterDeath
    ): void {
        $formName = $form->getName();
        $form[sprintf('%s[title]', $formName)] = $title;
        $form[sprintf('%s[riskLevel]', $formName)] = $riskLevel->value;
        $form[sprintf('%s[respawnsIn]', $formName)] = '15';
        $form[sprintf('%s[spawnsEvery]', $formName)] = '45';
        $form[sprintf('%s[reachesBaseIn]', $formName)] = '90';
        $form[sprintf('%s[speedFactor]', $formName)] = '2';

        $shieldInput = $form[sprintf('%s[hasShield]', $formName)];
        self::assertInstanceOf(ChoiceFormField::class, $shieldInput);
        $hasShield ? $shieldInput->tick() : $shieldInput->untick();

        $respawnInput = $form[sprintf('%s[respawnImmediatelyAfterDeath]', $formName)];
        self::assertInstanceOf(ChoiceFormField::class, $respawnInput);
        $respawnImmediatelyAfterDeath ? $respawnInput->tick() : $respawnInput->untick();
    }
}
