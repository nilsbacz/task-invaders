<?php

declare(strict_types=1);

namespace App\Controller;

use App\Entity\Board;
use App\Repository\BoardRepository;
use App\Service\BoardCreator;
use App\Service\BoardDeleter;
use App\Service\BoardFormFactory;
use App\Service\BoardPageDataBuilder;
use App\Service\BoardUpdater;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class BoardController extends AbstractController
{
    public function __construct(
        private readonly BoardRepository $boards,
        private readonly BoardCreator $boardCreator,
        private readonly BoardDeleter $boardDeleter,
        private readonly BoardUpdater $boardUpdater,
        private readonly BoardFormFactory $boardFormFactory,
        private readonly BoardPageDataBuilder $pageDataBuilder,
    ) {
    }

    #[Route('/boards', name: 'board_index', methods: ['GET'])]
    public function index(): Response
    {
        return $this->renderIndex();
    }

    #[Route('/boards', name: 'board_create', methods: ['POST'])]
    public function create(Request $request): Response
    {
        $board = new Board();
        $form = $this->boardFormFactory->buildCreateForm($board);
        $form->handleRequest($request);

        $formSubmitted = $form->isSubmitted();
        $formValid = $formSubmitted && $form->isValid();

        if ($formValid) {
            $this->boardCreator->create($board);
            $this->addFlash('success', 'Board created.');

            return $this->redirectToRoute('board_index');
        }

        return $this->renderIndex($form, null, null, Response::HTTP_BAD_REQUEST);
    }

    #[Route('/boards/{id}', name: 'board_update', requirements: ['id' => '\\d+'], methods: ['PATCH'])]
    public function update(
        int $id,
        Request $request,
    ): Response {
        $board = $this->boards->find($id);
        if ($board === null) {
            throw $this->createNotFoundException('Board not found.');
        }

        $form = $this->boardFormFactory->buildUpdateForm($board);
        $submittedData = $this->extractFormData($request, $form);
        $form->submit($submittedData, true);

        $formValid = $form->isSubmitted() && $form->isValid();
        if ($formValid) {
            $this->boardUpdater->update($board);
        }

        if ($formValid) {
            $this->addFlash('success', 'Board updated.');

            return $this->redirectToRoute('board_index');
        }

        return $this->renderIndex(null, $form, $board->getId(), Response::HTTP_BAD_REQUEST);
    }

    #[Route('/boards/{id}', name: 'board_delete', requirements: ['id' => '\\d+'], methods: ['DELETE'])]
    public function delete(int $id, Request $request): Response
    {
        $board = $this->boards->find($id);
        if ($board === null) {
            throw $this->createNotFoundException('Board not found.');
        }

        $form = $this->boardFormFactory->buildDeleteForm($board);
        $form->handleRequest($request);

        $formValid = $form->isSubmitted() && $form->isValid();
        if ($formValid) {
            $this->boardDeleter->delete($board);
            $this->addFlash('success', 'Board deleted.');

            return $this->redirectToRoute('board_index');
        }

        return $this->renderIndex(null, null, null, Response::HTTP_BAD_REQUEST);
    }

    /**
     * @param Request $request
     * @param FormInterface $form
     * @return array <mixed>
     */
    private function extractFormData(Request $request, FormInterface $form): array
    {
        $formName = $form->getName();
        return $request->request->all($formName);
    }

    private function renderIndex(
        ?FormInterface $createForm = null,
        ?FormInterface $errorUpdateForm = null,
        ?int $errorBoardId = null,
        int $statusCode = Response::HTTP_OK
    ): Response {
        $parameters = $this->pageDataBuilder->buildIndexData($createForm, $errorUpdateForm, $errorBoardId);

        return $this->render('board/index.html.twig', $parameters, new Response('', $statusCode));
    }
}
