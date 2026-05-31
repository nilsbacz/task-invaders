<?php

declare(strict_types=1);

namespace App\Board\UI\Http;

use App\Board\Application\CreateBoardRow;
use App\Board\Domain\Board;
use App\Board\Domain\BoardRow;
use App\Board\Infrastructure\Persistence\DoctrineBoardRowRepository;
use App\Entity\Task;
use App\Repository\TaskRepository;
use App\Service\BoardRowCreator;
use App\Service\BoardRowDeleter;
use App\Service\BoardRowFormFactory;
use App\Service\BoardRowUpdater;
use App\Service\TaskShootFormFactory;
use App\Service\TaskShooter;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class BoardController extends AbstractController
{
    public function __construct(
        private readonly DoctrineBoardRowRepository $boardRows,
        private readonly TaskRepository $tasks,
        private readonly BoardRowCreator $boardRowCreator,
        private readonly BoardRowDeleter $boardRowDeleter,
        private readonly BoardRowFormFactory $boardRowFormFactory,
        private readonly BoardRowUpdater $boardRowUpdater,
        private readonly TaskShooter $taskShooter,
        private readonly TaskShootFormFactory $taskShootFormFactory,
    ) {
    }

    #[Route('/boards/{id}', name: 'board_show', requirements: ['id' => '\d+'], methods: ['GET'])]
    public function index(Board $board): Response
    {
        return $this->renderBoard($board);
    }

    #[Route('/boards/{id}/rows', name: 'board_row_create', requirements: ['id' => '\d+'], methods: ['POST'])]
    public function createRow(Board $board, Request $request): Response
    {
        $command = new CreateBoardRow();
        $form = $this->boardRowFormFactory->buildCreateForm($board, $command);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $submittedCommand = $form->getData();
            \assert($submittedCommand instanceof CreateBoardRow);
            $this->boardRowCreator->create($board, $submittedCommand);
            $this->addFlash('success', 'Row added.');

            return $this->redirectToRoute('board_show', ['id' => $board->getId()]);
        }

        return $this->renderBoard($board, errorRowCreateForm: $form, statusCode: Response::HTTP_BAD_REQUEST);
    }

    #[Route(
        '/boards/{id}/rows/{rowId}',
        name: 'board_row_update',
        requirements: [
                       'id'    => '\d+',
                       'rowId' => '\d+',
                      ],
        methods: ['PATCH']
    )]
    public function updateRow(Board $board, int $rowId, Request $request): Response
    {
        $boardRow = $this->findBoardRow($board, $rowId);
        $form = $this->boardRowFormFactory->buildUpdateForm($board, $boardRow);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->boardRowUpdater->update($boardRow);
            $this->addFlash('success', 'Row updated.');

            return $this->redirectToRoute('board_show', ['id' => $board->getId()]);
        }

        return $this->renderBoard(
            $board,
            errorRowUpdateForm: $form,
            errorRowId: $rowId,
            statusCode: Response::HTTP_BAD_REQUEST
        );
    }

    #[Route(
        '/boards/{id}/rows/{rowId}',
        name: 'board_row_delete',
        requirements: [
                       'id'    => '\d+',
                       'rowId' => '\d+',
                      ],
        methods: ['DELETE']
    )]
    public function deleteRow(Board $board, int $rowId, Request $request): Response
    {
        $boardRow = $this->findBoardRow($board, $rowId);
        $form = $this->boardRowFormFactory->buildDeleteForm($board, $boardRow);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->boardRowDeleter->delete($board, $boardRow);
            $this->addFlash('success', 'Row removed.');

            return $this->redirectToRoute('board_show', ['id' => $board->getId()]);
        }

        return $this->renderBoard($board, statusCode: Response::HTTP_BAD_REQUEST);
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
        $task = $this->tasks->find($taskId);
        if ($task === null || !$this->taskCanBeShot($task, $board)) {
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

        return $this->renderBoard(
            $board,
            errorShootForm: $form,
            errorTaskId: $taskId,
            statusCode: Response::HTTP_BAD_REQUEST
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

    private function taskCanBeShot(Task $task, Board $board): bool
    {
        return $task->getBoardRow()?->getBoard()?->getId() === $board->getId()
            && $task->shouldAppearOnBoard();
    }

    private function renderBoard(
        Board $board,
        ?FormInterface $errorShootForm = null,
        ?int $errorTaskId = null,
        ?FormInterface $errorRowCreateForm = null,
        ?FormInterface $errorRowUpdateForm = null,
        ?int $errorRowId = null,
        int $statusCode = Response::HTTP_OK
    ): Response {
        return $this->render(
            'boards/show.html.twig',
            [
             'board'          => $board,
             'rowCreateForm'  => ($errorRowCreateForm ?? $this->boardRowFormFactory->buildCreateForm($board))
                 ->createView(),
             'rowDeleteForms' => $this->boardRowFormFactory->buildDeleteFormViews($board),
             'rowUpdateForms' => $this->boardRowFormFactory->buildUpdateFormViews(
                 $board,
                 $errorRowId,
                 $errorRowUpdateForm
             ),
             'shootForms'     => $this->taskShootFormFactory->buildShootFormViews(
                 $board,
                 $errorTaskId,
                 $errorShootForm
             ),
            ],
            new Response('', $statusCode)
        );
    }
}
