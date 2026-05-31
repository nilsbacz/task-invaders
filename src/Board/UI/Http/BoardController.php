<?php

declare(strict_types=1);

namespace App\Board\UI\Http;

use App\Board\Domain\Board;
use App\Entity\Task;
use App\Repository\TaskRepository;
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
        private readonly TaskRepository $tasks,
        private readonly TaskShooter $taskShooter,
        private readonly TaskShootFormFactory $taskShootFormFactory,
    ) {
    }

    #[Route('/boards/{id}', name: 'board_show', requirements: ['id' => '\d+'], methods: ['GET'])]
    public function index(Board $board): Response
    {
        return $this->renderBoard($board);
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

        return $this->renderBoard($board, $form, $taskId, Response::HTTP_BAD_REQUEST);
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
        int $statusCode = Response::HTTP_OK
    ): Response {
        return $this->render(
            'boards/show.html.twig',
            [
             'board'      => $board,
             'shootForms' => $this->taskShootFormFactory->buildShootFormViews(
                 $board,
                 $errorTaskId,
                 $errorShootForm
             ),
            ],
            new Response('', $statusCode)
        );
    }
}
