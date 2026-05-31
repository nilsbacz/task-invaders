<?php

declare(strict_types=1);

namespace App\Service;

use App\Board\Domain\Board;
use App\Entity\Task;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormView;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

final readonly class TaskShootFormFactory
{
    public function __construct(
        private FormFactoryInterface $formFactory,
        private UrlGeneratorInterface $urlGenerator,
    ) {
    }

    public function buildShootForm(Board $board, Task $task): FormInterface
    {
        $options = [
                    'action' => $this->urlGenerator->generate(
                        'board_task_shoot',
                        [
                         'id'     => $board->getId(),
                         'taskId' => $task->getId(),
                        ]
                    ),
                    'method' => 'POST',
                   ];

        $name = 'task_shoot_' . $task->getId();

        $builder = $this->formFactory->createNamedBuilder($name, options: $options);
        $builder->add('confirm', HiddenType::class, ['data' => '1']);

        return $builder->getForm();
    }

    /**
     * @return array<int, FormView>
     */
    public function buildShootFormViews(
        Board $board,
        ?int $errorTaskId = null,
        ?FormInterface $errorForm = null
    ): array {
        $forms = [];

        foreach ($board->getBoardRows() as $boardRow) {
            foreach ($boardRow->getActiveTasks() as $task) {
                $taskId = $task->getId();
                if ($taskId === null) {
                    continue;
                }

                if ($errorTaskId !== null && $taskId === $errorTaskId && $errorForm !== null) {
                    $forms[$taskId] = $errorForm->createView();
                    continue;
                }

                $forms[$taskId] = $this->buildShootForm($board, $task)->createView();
            }
        }

        return $forms;
    }
}
