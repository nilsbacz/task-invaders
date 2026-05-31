<?php

declare(strict_types=1);

namespace App\Service;

use App\Board\Domain\Board;
use Symfony\Component\HttpFoundation\Response;
use Twig\Environment;

final readonly class BoardDetailPageRenderer
{
    public function __construct(
        private Environment $twig,
        private BoardRowFormFactory $boardRowFormFactory,
        private TaskFormFactory $taskFormFactory,
        private TaskShootFormFactory $taskShootFormFactory,
    ) {
    }

    public function render(
        Board $board,
        ?BoardDetailFormErrors $errors = null,
        int $statusCode = Response::HTTP_OK
    ): Response {
        $errors ??= BoardDetailFormErrors::none();

        return new Response(
            $this->twig->render(
                'boards/show.html.twig',
                [
                 'board'           => $board,
                 'rowCreateForm'   => ($errors->rowCreateForm ?? $this->boardRowFormFactory->buildCreateForm($board))
                     ->createView(),
                 'rowDeleteForms'  => $this->boardRowFormFactory->buildDeleteFormViews($board),
                 'rowUpdateForms'  => $this->boardRowFormFactory->buildUpdateFormViews(
                     $board,
                     $errors->rowUpdateId,
                     $errors->rowUpdateForm
                 ),
                 'shootForms'      => $this->taskShootFormFactory->buildShootFormViews(
                     $board,
                     $errors->taskShootId,
                     $errors->taskShootForm
                 ),
                 'taskCreateForms' => $this->taskFormFactory->buildCreateFormViews(
                     $board,
                     $errors->taskCreateRowId,
                     $errors->taskCreateForm
                 ),
                 'taskDeleteForms' => $this->taskFormFactory->buildDeleteFormViews($board),
                 'taskUpdateForms' => $this->taskFormFactory->buildUpdateFormViews(
                     $board,
                     $errors->taskUpdateId,
                     $errors->taskUpdateForm
                 ),
                ]
            ),
            $statusCode
        );
    }
}
