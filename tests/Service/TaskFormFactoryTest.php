<?php

declare(strict_types=1);

namespace App\Tests\Service;

use App\Board\Application\CreateTask;
use App\Board\Domain\Board;
use App\Board\Domain\BoardRow;
use App\Entity\Task;
use App\Enum\TaskRiskLevel;
use App\Service\TaskFormFactory;
use DateTimeImmutable;
use InvalidArgumentException;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\Attributes\UsesClass;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\Forms;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Routing\RequestContext;

#[CoversClass(TaskFormFactory::class)]
#[UsesClass(Board::class)]
#[UsesClass(BoardRow::class)]
#[UsesClass(CreateTask::class)]
#[UsesClass(Task::class)]
#[UsesClass(TaskRiskLevel::class)]
final class TaskFormFactoryTest extends TestCase
{
    private TaskFormFactory $factory;
    private FormFactoryInterface $formFactory;

    #[\Override]
    protected function setUp(): void
    {
        $this->formFactory = Forms::createFormFactoryBuilder()->getFormFactory();
        $this->factory = new TaskFormFactory($this->formFactory, $this->createUrlGeneratorFixture());
    }

    #[Test]
    public function itBuildsCreateForm(): void
    {
        // Arrange
        $board = $this->createBoardFixtureWithId(7);
        $boardRow = $this->createBoardRowFixtureWithId(13);

        // Act
        $form = $this->factory->buildCreateForm($board, $boardRow);

        // Assert
        self::assertSame('task_create_13', $form->getName());
        self::assertSame('/boards/7/rows/13/tasks', $form->getConfig()->getOption('action'));
        self::assertSame('POST', $form->getConfig()->getOption('method'));
        self::assertSame(CreateTask::class, $form->getConfig()->getOption('data_class'));
        self::assertTrue($form->has('title'));
        self::assertTrue($form->has('riskLevel'));
        self::assertTrue($form->has('reachesBaseIn'));
    }

    #[Test]
    public function itBuildsUpdateAndDeleteForms(): void
    {
        // Arrange
        $board = $this->createBoardFixtureWithId(7);
        $task = $this->createTaskFixtureWithId(21);

        // Act
        $updateForm = $this->factory->buildUpdateForm($board, $task);
        $deleteForm = $this->factory->buildDeleteForm($board, $task);

        // Assert
        self::assertSame('task_21', $updateForm->getName());
        self::assertSame('/boards/7/tasks/21', $updateForm->getConfig()->getOption('action'));
        self::assertSame('PATCH', $updateForm->getConfig()->getOption('method'));
        self::assertTrue($updateForm->has('title'));
        self::assertTrue($updateForm->has('riskLevel'));

        self::assertSame('task_delete_21', $deleteForm->getName());
        self::assertSame('/boards/7/tasks/21', $deleteForm->getConfig()->getOption('action'));
        self::assertSame('DELETE', $deleteForm->getConfig()->getOption('method'));
        self::assertTrue($deleteForm->has('confirm'));
        self::assertInstanceOf(HiddenType::class, $deleteForm->get('confirm')->getConfig()->getType()->getInnerType());
    }

    #[Test]
    public function itBuildsFormViewsForPersistedRowsAndActiveTasks(): void
    {
        // Arrange
        $board = $this->createBoardFixtureWithId(7);
        $boardRow = $this->createBoardRowFixtureWithId(13);
        $rowWithoutId = new BoardRow();
        $rowWithoutId->setTitle('Draft');
        $rowWithoutId->setRowNumber(2);
        $board->addBoardRow($boardRow);
        $board->addBoardRow($rowWithoutId);

        $taskOne = $this->createTaskFixtureWithId(21);
        $taskTwo = $this->createTaskFixtureWithId(22);
        $completedTask = $this->createTaskFixtureWithId(23);
        $completedTask->complete(new DateTimeImmutable('2026-05-31T10:00:00+00:00'));
        $taskWithoutId = $this->createTaskFixture();
        $boardRow->addTask($taskOne);
        $boardRow->addTask($taskTwo);
        $boardRow->addTask($completedTask);
        $boardRow->addTask($taskWithoutId);
        $createErrorForm = $this->formFactory->createNamed('task_create_error_13');
        $updateErrorForm = $this->formFactory->createNamed('task_error_22');

        // Act
        $createViews = $this->factory->buildCreateFormViews($board, 13, $createErrorForm);
        $updateViews = $this->factory->buildUpdateFormViews($board, 22, $updateErrorForm);
        $deleteViews = $this->factory->buildDeleteFormViews($board);

        // Assert
        self::assertCount(1, $createViews);
        self::assertSame('task_create_error_13', $createViews[13]->vars['name']);

        self::assertCount(2, $updateViews);
        self::assertSame('task_21', $updateViews[21]->vars['name']);
        self::assertSame('task_error_22', $updateViews[22]->vars['name']);

        self::assertCount(2, $deleteViews);
        self::assertSame('task_delete_21', $deleteViews[21]->vars['name']);
        self::assertSame('task_delete_22', $deleteViews[22]->vars['name']);
    }

    private function createUrlGeneratorFixture(): UrlGeneratorInterface
    {
        return new class () implements UrlGeneratorInterface {
            private RequestContext $context;

            public function __construct()
            {
                $this->context = new RequestContext();
            }

            /**
             * @param array<string, mixed> $parameters
             */
            #[\Override]
            public function generate(
                string $name,
                array $parameters = [],
                int $referenceType = UrlGeneratorInterface::ABSOLUTE_PATH,
            ): string {
                if ($referenceType !== UrlGeneratorInterface::ABSOLUTE_PATH) {
                    throw new InvalidArgumentException('Only ABSOLUTE_PATH is supported.');
                }

                if (!isset($parameters['id']) || !is_int($parameters['id'])) {
                    throw new InvalidArgumentException('Missing board id.');
                }

                if ($name === 'board_task_create') {
                    if (!isset($parameters['rowId']) || !is_int($parameters['rowId'])) {
                        throw new InvalidArgumentException('Missing board row id.');
                    }

                    return sprintf('/boards/%d/rows/%d/tasks', $parameters['id'], $parameters['rowId']);
                }

                if (!isset($parameters['taskId']) || !is_int($parameters['taskId'])) {
                    throw new InvalidArgumentException('Missing task id.');
                }

                return match ($name) {
                    'board_task_update',
                    'board_task_delete' => sprintf('/boards/%d/tasks/%d', $parameters['id'], $parameters['taskId']),
                    default => throw new InvalidArgumentException('Unknown route.'),
                };
            }

            #[\Override]
            public function setContext(RequestContext $context): void
            {
                $this->context = $context;
            }

            #[\Override]
            public function getContext(): RequestContext
            {
                return $this->context;
            }
        };
    }

    private function createBoardFixtureWithId(int $id): Board
    {
        $board = new Board();
        $reflection = new \ReflectionProperty(Board::class, 'id');
        $reflection->setAccessible(true);
        $reflection->setValue($board, $id);

        return $board;
    }

    private function createBoardRowFixtureWithId(int $id): BoardRow
    {
        $boardRow = new BoardRow();
        $boardRow->setTitle('Row ' . $id);
        $boardRow->setRowNumber($id);
        $reflection = new \ReflectionProperty(BoardRow::class, 'id');
        $reflection->setAccessible(true);
        $reflection->setValue($boardRow, $id);

        return $boardRow;
    }

    private function createTaskFixtureWithId(int $id): Task
    {
        $task = $this->createTaskFixture();
        $reflection = new \ReflectionProperty(Task::class, 'id');
        $reflection->setAccessible(true);
        $reflection->setValue($task, $id);

        return $task;
    }

    private function createTaskFixture(): Task
    {
        $task = new Task();
        $task->setTitle('Task');
        $task->setRiskLevel(TaskRiskLevel::GREEN);
        $task->setSpawnDate(new DateTimeImmutable('2026-04-08T00:00:00+00:00'));
        $task->setRespawnsIn(10);
        $task->setSpawnsEvery(20);
        $task->setReachesBaseIn(30);

        return $task;
    }
}
