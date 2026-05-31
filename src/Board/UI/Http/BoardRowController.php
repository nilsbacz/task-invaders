<?php

declare(strict_types=1);

namespace App\Board\UI\Http;

use App\Board\Application\CreateBoardRow;
use App\Board\Domain\Board;
use App\Board\Domain\BoardRow;
use App\Board\Infrastructure\Persistence\DoctrineBoardRowRepository;
use App\Service\BoardDetailFormErrors;
use App\Service\BoardDetailPageRenderer;
use App\Service\BoardRowCreator;
use App\Service\BoardRowDeleter;
use App\Service\BoardRowFormFactory;
use App\Service\BoardRowUpdater;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class BoardRowController extends AbstractController
{
    public function __construct(
        private readonly DoctrineBoardRowRepository $boardRows,
        private readonly BoardDetailPageRenderer $boardDetailPageRenderer,
        private readonly BoardRowCreator $boardRowCreator,
        private readonly BoardRowDeleter $boardRowDeleter,
        private readonly BoardRowFormFactory $boardRowFormFactory,
        private readonly BoardRowUpdater $boardRowUpdater,
    ) {
    }

    #[Route('/boards/{id}/rows', name: 'board_row_create', requirements: ['id' => '\d+'], methods: ['POST'])]
    public function create(Board $board, Request $request): Response
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

        return $this->boardDetailPageRenderer->render(
            $board,
            BoardDetailFormErrors::rowCreate($form),
            Response::HTTP_BAD_REQUEST
        );
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
    public function update(Board $board, int $rowId, Request $request): Response
    {
        $boardRow = $this->findBoardRow($board, $rowId);
        $form = $this->boardRowFormFactory->buildUpdateForm($board, $boardRow);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->boardRowUpdater->update($boardRow);
            $this->addFlash('success', 'Row updated.');

            return $this->redirectToRoute('board_show', ['id' => $board->getId()]);
        }

        return $this->boardDetailPageRenderer->render(
            $board,
            BoardDetailFormErrors::rowUpdate($rowId, $form),
            Response::HTTP_BAD_REQUEST
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
    public function delete(Board $board, int $rowId, Request $request): Response
    {
        $boardRow = $this->findBoardRow($board, $rowId);
        $form = $this->boardRowFormFactory->buildDeleteForm($board, $boardRow);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->boardRowDeleter->delete($board, $boardRow);
            $this->addFlash('success', 'Row removed.');

            return $this->redirectToRoute('board_show', ['id' => $board->getId()]);
        }

        return $this->boardDetailPageRenderer->render($board, statusCode: Response::HTTP_BAD_REQUEST);
    }

    private function findBoardRow(Board $board, int $rowId): BoardRow
    {
        $boardRow = $this->boardRows->find($rowId);
        if ($boardRow === null || $boardRow->getBoard()?->getId() !== $board->getId()) {
            throw $this->createNotFoundException('Row not found.');
        }

        return $boardRow;
    }
}
