<?php

declare(strict_types=1);

namespace App\Service;

use App\Entity\Board;
use App\Repository\BoardRepository;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormView;

final readonly class BoardPageDataBuilder
{
    public function __construct(
        private BoardRepository $boards,
        private BoardFormFactory $boardFormFactory,
    ) {
    }

    /**
     * @return array{
     *     boards: array<int, Board>,
     *     createForm: FormView,
     *     updateForms: array<int, FormView>,
     *     deleteForms: array<int, FormView>
     * }
     */
    public function buildIndexData(
        ?FormInterface $createForm = null,
        ?FormInterface $errorUpdateForm = null,
        ?int $errorBoardId = null
    ): array {
        /** @var array<int, Board> $boards */
        $boards = $this->boards->findAll();
        $createForm ??= $this->boardFormFactory->buildCreateForm();

        $updateForms = $this->boardFormFactory->buildUpdateFormViews(
            $boards,
            $errorBoardId,
            $errorUpdateForm
        );
        $deleteForms = $this->boardFormFactory->buildDeleteFormViews($boards);

        return [
                'boards'      => $boards,
                'createForm'  => $createForm->createView(),
                'updateForms' => $updateForms,
                'deleteForms' => $deleteForms,
               ];
    }
}
