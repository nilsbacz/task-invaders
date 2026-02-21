<?php

declare(strict_types=1);

namespace App\Service;

use App\Entity\Board;
use App\Form\BoardType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormView;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

final readonly class BoardFormFactory
{
    public function __construct(
        private FormFactoryInterface $formFactory,
        private UrlGeneratorInterface $urlGenerator,
    ) {
    }

    public function buildCreateForm(?Board $board = null): FormInterface
    {
        $board ??= new Board();
        $options = [
                    'action' => $this->urlGenerator->generate('board_create'),
                    'method' => 'POST',
                   ];

        return $this->formFactory->createNamed('board_create', BoardType::class, $board, $options);
    }

    public function buildUpdateForm(Board $board): FormInterface
    {
        $options = [
                    'action' => $this->urlGenerator->generate('board_update', ['id' => $board->getId()]),
                    'method' => 'PATCH',
                   ];

        return $this->formFactory->createNamed('board_' . $board->getId(), BoardType::class, $board, $options);
    }

    public function buildDeleteForm(Board $board): FormInterface
    {
        $options = [
                    'action' => $this->urlGenerator->generate('board_delete', ['id' => $board->getId()]),
                    'method' => 'DELETE',
                   ];

        $name = 'board_delete_' . $board->getId();

        $builder = $this->formFactory->createNamedBuilder($name, options: $options);
        $builder->add('confirm', HiddenType::class, ['data' => '1']);

        return $builder->getForm();
    }

    /**
     * @param iterable<Board> $boards
     *
     * @return array<int, FormView>
     */
    public function buildUpdateFormViews(
        iterable $boards,
        ?int $errorBoardId = null,
        ?FormInterface $errorForm = null
    ): array {
        $forms = [];
        foreach ($boards as $board) {
            $boardId = $board->getId();
            if ($boardId === null) {
                continue;
            }

            if ($errorBoardId !== null && $boardId === $errorBoardId && $errorForm !== null) {
                $forms[$boardId] = $errorForm->createView();
                continue;
            }

            $forms[$boardId] = $this->buildUpdateForm($board)->createView();
        }

        return $forms;
    }

    /**
     * @param iterable<Board> $boards
     *
     * @return array<int, FormView>
     */
    public function buildDeleteFormViews(iterable $boards): array
    {
        $forms = [];
        foreach ($boards as $board) {
            $boardId = $board->getId();
            if ($boardId === null) {
                continue;
            }

            $forms[$boardId] = $this->buildDeleteForm($board)->createView();
        }

        return $forms;
    }
}
