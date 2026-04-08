<?php

declare(strict_types=1);

namespace App\Tests\Integration;

use App\Board\UI\Http\BoardController;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

#[CoversClass(BoardController::class)]
final class BoardControllerTest extends AbstractDatabaseWebTestCase
{
    #[Test]
    public function itShowsBoardById(): void
    {
        $board = $this->createBoard('Show Board', true);

        $this->client->request('GET', sprintf('/boards/%d', $board->getId()));

        self::assertResponseIsSuccessful();
        self::assertSelectorTextContains('h1', 'Hello BoardController! Board ID: ' . $board->getId());
    }

    #[Test]
    public function itReturnsNotFoundForMissingBoard(): void
    {
        $this->client->catchExceptions(false);
        $this->expectException(NotFoundHttpException::class);

        $this->client->request('GET', '/boards/99999');
    }
}
