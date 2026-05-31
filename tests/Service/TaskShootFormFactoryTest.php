<?php

declare(strict_types=1);

namespace App\Tests\Service;

use App\Board\Domain\Board;
use App\Board\Domain\BoardRow;
use App\Entity\Task;
use App\Service\TaskShootFormFactory;
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

#[CoversClass(TaskShootFormFactory::class)]
#[UsesClass(Board::class)]
#[UsesClass(BoardRow::class)]
#[UsesClass(Task::class)]
final class TaskShootFormFactoryTest extends TestCase
{
    private FormFactoryInterface $formFactory;
    private TaskShootFormFactory $factory;

    #[\Override]
    protected function setUp(): void
    {
        $this->formFactory = Forms::createFormFactoryBuilder()->getFormFactory();
        $this->factory = new TaskShootFormFactory($this->formFactory, $this->createUrlGeneratorFixture());
    }

    #[Test]
    public function itBuildsShootForm(): void
    {

        $board = $this->createBoardFixtureWithId(7);
        $task = $this->createTaskFixtureWithId(13);


        $form = $this->factory->buildShootForm($board, $task);


        self::assertSame('task_shoot_13', $form->getName());
        self::assertSame('/boards/7/tasks/13/shoot', $form->getConfig()->getOption('action'));
        self::assertSame('POST', $form->getConfig()->getOption('method'));
        self::assertTrue($form->has('confirm'));
        self::assertInstanceOf(HiddenType::class, $form->get('confirm')->getConfig()->getType()->getInnerType());
    }

    #[Test]
    public function itBuildsShootFormViewsForPersistedTasks(): void
    {

        $board = $this->createBoardFixtureWithId(7);
        $boardRow = new BoardRow();
        $boardRow->setTitle('Sports');
        $boardRow->setRowNumber(1);
        $board->addBoardRow($boardRow);
        $taskOne = $this->createTaskFixtureWithId(13);
        $taskTwo = $this->createTaskFixtureWithId(14);
        $completedTask = $this->createTaskFixtureWithId(15);
        $completedTask->complete(new DateTimeImmutable('2026-05-31T10:00:00+00:00'));
        $taskWithoutId = new Task();
        $boardRow->addTask($taskOne);
        $boardRow->addTask($taskTwo);
        $boardRow->addTask($completedTask);
        $boardRow->addTask($taskWithoutId);
        $errorForm = $this->createErrorFormFixture();


        $views = $this->factory->buildShootFormViews($board, 14, $errorForm);


        self::assertCount(2, $views);
        self::assertSame('task_shoot_13', $views[13]->vars['name']);
        self::assertSame('task_error_14', $views[14]->vars['name']);
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

                if ($name !== 'board_task_shoot') {
                    throw new InvalidArgumentException('Unknown route.');
                }

                if (!isset($parameters['id']) || !is_int($parameters['id'])) {
                    throw new InvalidArgumentException('Missing board id.');
                }

                if (!isset($parameters['taskId']) || !is_int($parameters['taskId'])) {
                    throw new InvalidArgumentException('Missing task id.');
                }

                return sprintf('/boards/%d/tasks/%d/shoot', $parameters['id'], $parameters['taskId']);
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

    private function createTaskFixtureWithId(int $id): Task
    {
        $task = new Task();
        $reflection = new \ReflectionProperty(Task::class, 'id');
        $reflection->setAccessible(true);
        $reflection->setValue($task, $id);

        return $task;
    }

    private function createErrorFormFixture(): FormInterface
    {
        return $this->formFactory->createNamed('task_error_14');
    }
}
