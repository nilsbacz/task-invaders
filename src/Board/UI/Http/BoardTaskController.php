<?php

declare(strict_types=1);

namespace App\Board\UI\Http;

use App\Board\Application\CreateTask;
use App\Board\Domain\Board;
use App\Board\Domain\BoardRow;
use App\Board\Infrastructure\Persistence\DoctrineBoardRowRepository;
use App\Entity\Task;
use App\Repository\TaskRepository;
use App\Service\BoardDetailFormErrors;
use App\Service\BoardDetailPageRenderer;
use App\Service\TaskCreator;
use App\Service\TaskFormFactory;
use App\Service\TaskRemover;
use App\Service\TaskShootFormFactory;
use App\Service\TaskShooter;
use App\Service\TaskUpdater;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class BoardTaskController extends AbstractController
{
    public function __construct(
        private readonly DoctrineBoardRowRepository $boardRows,
        private readonly TaskRepository $tasks,
        private readonly BoardDetailPageRenderer $boardDetailPageRenderer,
        private readonly TaskCreator $taskCreator,
        private readonly TaskFormFactory $taskFormFactory,
        private readonly TaskRemover $taskRemover,
        private readonly TaskShooter $taskShooter,
        private readonly TaskShootFormFactory $taskShootFormFactory,
        private readonly TaskUpdater $taskUpdater,
    ) {
    }

    #[Route(
        '/boards/{id}/rows/{rowId}/tasks',
        name: 'board_task_create',
        requirements: [
                       'id'    => '\d+',
                       'rowId' => '\d+',
                      ],
        methods: ['POST']
    )]
    public function create(Board $board, int $rowId, Request $request): Response
    {
        $boardRow = $this->findBoardRow($board, $rowId);
        $command = new CreateTask();
        $form = $this->taskFormFactory->buildCreateForm($board, $boardRow, $command);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $submittedCommand = $form->getData();
            \assert($submittedCommand instanceof CreateTask);
            $this->taskCreator->create($boardRow, $submittedCommand);
            $this->addFlash('success', 'Task added.');

            return $this->redirectToRoute('board_show', ['id' => $board->getId()]);
        }

        return $this->boardDetailPageRenderer->render(
            $board,
            BoardDetailFormErrors::taskCreate($rowId, $form),
            Response::HTTP_BAD_REQUEST
        );
    }

    #[Route(
        '/boards/{id}/tasks/{taskId}',
        name: 'board_task_update',
        requirements: [
                       'id'     => '\d+',
                       'taskId' => '\d+',
                      ],
        methods: ['PATCH']
    )]
    public function update(Board $board, int $taskId, Request $request): Response
    {
        $task = $this->findBoardTask($board, $taskId);
        $form = $this->taskFormFactory->buildUpdateForm($board, $task);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->taskUpdater->update($task);
            $this->addFlash('success', 'Task updated.');

            return $this->redirectToRoute('board_show', ['id' => $board->getId()]);
        }

        return $this->boardDetailPageRenderer->render(
            $board,
            BoardDetailFormErrors::taskUpdate($taskId, $form),
            Response::HTTP_BAD_REQUEST
        );
    }

    #[Route(
        '/boards/{id}/tasks/{taskId}',
        name: 'board_task_delete',
        requirements: [
                       'id'     => '\d+',
                       'taskId' => '\d+',
                      ],
        methods: ['DELETE']
    )]
    public function delete(Board $board, int $taskId, Request $request): Response
    {
        $task = $this->findBoardTask($board, $taskId);
        $form = $this->taskFormFactory->buildDeleteForm($board, $task);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->taskRemover->remove($task);
            $this->addFlash('success', 'Task removed.');

            return $this->redirectToRoute('board_show', ['id' => $board->getId()]);
        }

        return $this->boardDetailPageRenderer->render($board, statusCode: Response::HTTP_BAD_REQUEST);
    }

    #[Route(
        '/boards/{id}/tasks/{taskId}/shoot',
        name: 'board_task_shoot',
        requirements: [
                       'id'     => '\d+',
                       'taskId' => '\d+',
                      ],
        methods: ['POST']
    )]
    public function shoot(Board $board, int $taskId, Request $request): Response
    {
        $task = $this->findBoardTask($board, $taskId);
        if (!$task->shouldAppearOnBoard()) {
            throw $this->createNotFoundException('Task not found.');
        }

        $form = $this->taskShootFormFactory->buildShootForm($board, $task);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $taskRespawns = $task->isRespawnImmediatelyAfterDeath();
            $this->taskShooter->shoot($task, new \DateTimeImmutable());
            $this->addFlash('success', $taskRespawns ? 'Task respawned.' : 'Task shot.');

            return $this->redirectToRoute('board_show', ['id' => $board->getId()]);
        }

        return $this->boardDetailPageRenderer->render(
            $board,
            BoardDetailFormErrors::taskShoot($taskId, $form),
            Response::HTTP_BAD_REQUEST
        );
    }

    private function findBoardRow(Board $board, int $rowId): BoardRow
    {
        $boardRow = $this->boardRows->find($rowId);
        if ($boardRow === null || $boardRow->getBoard()?->getId() !== $board->getId()) {
            throw $this->createNotFoundException('Row not found.');
        }

        return $boardRow;
    }

    private function findBoardTask(Board $board, int $taskId): Task
    {
        $task = $this->tasks->find($taskId);
        if ($task === null || $task->getBoardRow()?->getBoard()?->getId() !== $board->getId()) {
            throw $this->createNotFoundException('Task not found.');
        }

        return $task;
    }
}
