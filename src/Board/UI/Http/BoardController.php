<?php

declare(strict_types=1);

namespace App\Board\UI\Http;

use App\Board\Domain\Board;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class BoardController extends AbstractController
{
    #[Route('/boards/{id}', name: 'board_show', requirements: ['id' => '\d+'], methods: ['GET'])]
    public function index(Board $board): Response
    {
        return $this->render('boards/show.html.twig', ['board' => $board]);
    }
}
