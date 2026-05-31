<?php

declare(strict_types=1);

namespace App\Service;

use App\Board\Application\CreateBoardRow;
use App\Board\Domain\Board;
use App\Board\Domain\BoardRow;
use App\Form\BoardRowType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormView;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

final readonly class BoardRowFormFactory
{
    public function __construct(
        private FormFactoryInterface $formFactory,
        private UrlGeneratorInterface $urlGenerator,
    ) {
    }

    public function buildCreateForm(Board $board, ?CreateBoardRow $command = null): FormInterface
    {
        $command ??= new CreateBoardRow();
        $options = [
                    'action'     => $this->urlGenerator->generate('board_row_create', ['id' => $board->getId()]),
                    'method'     => 'POST',
                    'data_class' => CreateBoardRow::class,
                   ];

        return $this->formFactory->createNamed('board_row_create', BoardRowType::class, $command, $options);
    }

    public function buildUpdateForm(Board $board, BoardRow $boardRow): FormInterface
    {
        $options = [
                    'action' => $this->urlGenerator->generate(
                        'board_row_update',
                        [
                         'id'    => $board->getId(),
                         'rowId' => $boardRow->getId(),
                        ]
                    ),
                    'method' => 'PATCH',
                   ];

        return $this->formFactory->createNamed(
            'board_row_' . $boardRow->getId(),
            BoardRowType::class,
            $boardRow,
            $options
        );
    }

    public function buildDeleteForm(Board $board, BoardRow $boardRow): FormInterface
    {
        $options = [
                    'action' => $this->urlGenerator->generate(
                        'board_row_delete',
                        [
                         'id'    => $board->getId(),
                         'rowId' => $boardRow->getId(),
                        ]
                    ),
                    'method' => 'DELETE',
                   ];

        $name = 'board_row_delete_' . $boardRow->getId();

        $builder = $this->formFactory->createNamedBuilder($name, options: $options);
        $builder->add('confirm', HiddenType::class, ['data' => '1']);

        return $builder->getForm();
    }

    /**
     * @return array<int, FormView>
     */
    public function buildUpdateFormViews(
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

            $forms[$boardRowId] = $this->buildUpdateForm($board, $boardRow)->createView();
        }

        return $forms;
    }

    /**
     * @return array<int, FormView>
     */
    public function buildDeleteFormViews(Board $board): array
    {
        $forms = [];
        foreach ($board->getBoardRows() as $boardRow) {
            $boardRowId = $boardRow->getId();
            if ($boardRowId === null) {
                continue;
            }

            $forms[$boardRowId] = $this->buildDeleteForm($board, $boardRow)->createView();
        }

        return $forms;
    }
}
