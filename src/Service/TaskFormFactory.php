<?php

declare(strict_types=1);

namespace App\Service;

use App\Board\Application\CreateTask;
use App\Board\Domain\Board;
use App\Board\Domain\BoardRow;
use App\Entity\Task;
use App\Form\TaskType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormView;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

final readonly class TaskFormFactory
{
    public function __construct(
        private FormFactoryInterface $formFactory,
        private UrlGeneratorInterface $urlGenerator,
    ) {
    }

    public function buildCreateForm(Board $board, BoardRow $boardRow, ?CreateTask $command = null): FormInterface
    {
        $command ??= new CreateTask();
        $options = [
                    'action'     => $this->urlGenerator->generate(
                        'board_task_create',
                        [
                         'id'    => $board->getId(),
                         'rowId' => $boardRow->getId(),
                        ]
                    ),
                    'method'     => 'POST',
                    'data_class' => CreateTask::class,
                   ];

        return $this->formFactory->createNamed(
            'task_create_' . $boardRow->getId(),
            TaskType::class,
            $command,
            $options
        );
    }

    public function buildUpdateForm(Board $board, Task $task): FormInterface
    {
        $options = [
                    'action' => $this->urlGenerator->generate(
                        'board_task_update',
                        [
                         'id'     => $board->getId(),
                         'taskId' => $task->getId(),
                        ]
                    ),
                    'method' => 'PATCH',
                   ];

        return $this->formFactory->createNamed('task_' . $task->getId(), TaskType::class, $task, $options);
    }

    public function buildDeleteForm(Board $board, Task $task): FormInterface
    {
        $options = [
                    'action' => $this->urlGenerator->generate(
                        'board_task_delete',
                        [
                         'id'     => $board->getId(),
                         'taskId' => $task->getId(),
                        ]
                    ),
                    'method' => 'DELETE',
                   ];

        $name = 'task_delete_' . $task->getId();

        $builder = $this->formFactory->createNamedBuilder($name, options: $options);
        $builder->add('confirm', HiddenType::class, ['data' => '1']);

        return $builder->getForm();
    }

    /**
     * @return array<int, FormView>
     */
    public function buildCreateFormViews(
        Board $board,
        ?int $errorRowId = null,
        ?FormInterface $errorForm = null
    ): array {
        $forms = [];
        foreach ($board->getBoardRows() as $boardRow) {
            $boardRowId = $boardRow->getId();
            if ($boardRowId === null) {
                continue;
            }

            if ($errorRowId !== null && $boardRowId === $errorRowId && $errorForm !== null) {
                $forms[$boardRowId] = $errorForm->createView();
                continue;
            }

            $forms[$boardRowId] = $this->buildCreateForm($board, $boardRow)->createView();
        }

        return $forms;
    }

    /**
     * @return array<int, FormView>
     */
    public function buildUpdateFormViews(
        Board $board,
        ?int $errorTaskId = null,
        ?FormInterface $errorForm = null
    ): array {
        $forms = [];
        foreach ($this->activeBoardTasks($board) as $task) {
            $taskId = $task->getId();
            if ($taskId === null) {
                continue;
            }

            if ($errorTaskId !== null && $taskId === $errorTaskId && $errorForm !== null) {
                $forms[$taskId] = $errorForm->createView();
                continue;
            }

            $forms[$taskId] = $this->buildUpdateForm($board, $task)->createView();
        }

        return $forms;
    }

    /**
     * @return array<int, FormView>
     */
    public function buildDeleteFormViews(Board $board): array
    {
        $forms = [];
        foreach ($this->activeBoardTasks($board) as $task) {
            $taskId = $task->getId();
            if ($taskId === null) {
                continue;
            }

            $forms[$taskId] = $this->buildDeleteForm($board, $task)->createView();
        }

        return $forms;
    }

    /**
     * @return \Generator<int, Task>
     */
    private function activeBoardTasks(Board $board): \Generator
    {
        foreach ($board->getBoardRows() as $boardRow) {
            foreach ($boardRow->getActiveTasks() as $task) {
                yield $task;
            }
        }
    }
}
