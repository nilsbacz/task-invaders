<?php

declare(strict_types=1);

namespace App\Tests\Integration;

use App\Board\Application\BoardCreator;
use App\Board\Domain\BoardRow;
use App\Controller\BoardCreationController;
use App\Entity\Task;
use App\Service\BoardDeleter;
use App\Service\BoardPresetApplier;
use App\Service\BoardPresetLoader;
use App\Service\BoardUpdater;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\Attributes\UsesClass;
use Symfony\Component\DomCrawler\Crawler;
use Symfony\Component\DomCrawler\Field\ChoiceFormField;
use Symfony\Component\DomCrawler\Form;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

#[CoversClass(BoardCreationController::class)]
#[UsesClass(BoardCreator::class)]
#[UsesClass(BoardDeleter::class)]
#[UsesClass(BoardPresetApplier::class)]
#[UsesClass(BoardPresetLoader::class)]
#[UsesClass(BoardUpdater::class)]
final class BoardCreationControllerTest extends AbstractDatabaseWebTestCase
{
    #[Test]
    public function itCreatesBoardFromForm(): void
    {

        $crawler = $this->client->request('GET', '/boards');
        self::assertResponseIsSuccessful();
        $form = $this->createBoardFormFixture($crawler, 'Alpha Board', true);


        $this->client->submit($form);
        $this->client->followRedirect();


        self::assertResponseIsSuccessful();
        $board = $this->boards->findOneBy(['title' => 'Alpha Board']);
        $tasks = $this->entityManager->getRepository(Task::class)->findAll();

        self::assertNotNull($board);
        self::assertTrue($board->isTurretMode());
        self::assertSame(
            [
             'sports',
             'household',
             'projects',
            ],
            array_map(
                static fn (BoardRow $boardRow): string => $boardRow->getTitle(),
                $board->getBoardRows()->toArray()
            )
        );
        self::assertCount(4, $tasks);
    }

    #[Test]
    public function itUpdatesBoardAndCanUnsetTurretMode(): void
    {

        $board = $this->createBoard('Beta Board', true);
        $boardId = $board->getId();
        self::assertNotNull($boardId);
        $crawler = $this->client->request('GET', '/boards');
        self::assertResponseIsSuccessful();
        $form = $this->createUpdateFormFixture($crawler, $boardId, 'Beta Board Updated', false);


        $this->client->submit($form);
        $this->client->followRedirect();


        self::assertResponseIsSuccessful();
        $updated = $this->boards->find($boardId);

        self::assertNotNull($updated);
        self::assertSame('Beta Board Updated', $updated->getTitle());
        self::assertFalse($updated->isTurretMode());
    }

    #[Test]
    #[DataProvider('invalidTitleScenarioProvider')]
    public function itRejectsInvalidTitleType(string $scenario): void
    {

        $boardId = $scenario === 'update' ? $this->createBoard('Gamma Board', false)->getId() : null;
        $crawler = $this->client->request('GET', '/boards');
        self::assertResponseIsSuccessful();
        $request = $this->createInvalidTitleRequestFixture($crawler, $scenario, $boardId);


        $this->client->request($request['method'], $request['path'], $request['payload']);


        self::assertResponseStatusCodeSame(Response::HTTP_BAD_REQUEST);

        if ($scenario === 'create') {
            self::assertCount(0, $this->boards->findAll());

            return;
        }

        self::assertNotNull($boardId);
        $updated = $this->boards->find($boardId);

        self::assertNotNull($updated);
        self::assertSame('Gamma Board', $updated->getTitle());
    }

    #[Test]
    public function itDeletesBoardFromForm(): void
    {

        $board = $this->createBoard('Delete Board', false);
        $crawler = $this->client->request('GET', '/boards');
        self::assertResponseIsSuccessful();
        $formNode = $crawler->filter(sprintf('form[name="board_delete_%d"]', $board->getId()));
        self::assertCount(1, $formNode);
        $form = $formNode->form();


        $this->client->submit($form);
        $this->client->followRedirect();


        self::assertResponseIsSuccessful();
        self::assertNull($this->boards->find($board->getId()));
    }

    #[Test]
    public function itRejectsDeleteWithoutFormSubmission(): void
    {

        $board = $this->createBoard('Reject Delete Board', false);
        $action = sprintf('/boards/%d', $board->getId());


        $this->client->request('DELETE', $action);


        self::assertResponseStatusCodeSame(Response::HTTP_BAD_REQUEST);
        self::assertNotNull($this->boards->find($board->getId()));
    }

    #[Test]
    public function itReturnsNotFoundWhenUpdatingMissingBoard(): void
    {

        $this->client->catchExceptions(false);
        $this->expectException(NotFoundHttpException::class);


        $this->client->request('PATCH', '/boards/99999');
    }

    #[Test]
    public function itReturnsNotFoundWhenDeletingMissingBoard(): void
    {

        $this->client->catchExceptions(false);
        $this->expectException(NotFoundHttpException::class);


        $this->client->request('DELETE', '/boards/99999');
    }

    /**
     * @return array<string, array{string}>
     */
    public static function invalidTitleScenarioProvider(): array
    {
        return [
                'create' => ['create'],
                'update' => ['update'],
               ];
    }

    private function createBoardFormFixture(Crawler $crawler, string $title, bool $isTurretMode): Form
    {
        $form = $crawler->selectButton('Create')->form();
        $formName = $form->getName();
        $form[sprintf('%s[title]', $formName)] = $title;

        $turretInput = $form[sprintf('%s[isTurretMode]', $formName)];
        self::assertInstanceOf(ChoiceFormField::class, $turretInput);
        $isTurretMode ? $turretInput->tick() : $turretInput->untick();

        return $form;
    }

    private function createUpdateFormFixture(
        Crawler $crawler,
        int $boardId,
        string $title,
        bool $isTurretMode,
    ): Form {
        $formNode = $crawler->filter(sprintf('form[name="board_%d"]', $boardId));
        self::assertCount(1, $formNode);

        $form = $formNode->form();
        $formName = $form->getName();
        $form[sprintf('%s[title]', $formName)] = $title;

        $turretInput = $form[sprintf('%s[isTurretMode]', $formName)];
        self::assertInstanceOf(ChoiceFormField::class, $turretInput);
        $isTurretMode ? $turretInput->tick() : $turretInput->untick();

        return $form;
    }

    /**
     * @return array{method: string, path: string, payload: array<string, array<string, mixed>>}
     */
    private function createInvalidTitleRequestFixture(Crawler $crawler, string $scenario, ?int $boardId): array
    {
        if ($scenario === 'create') {
            $form = $crawler->selectButton('Create')->form();
            $formName = $form->getName();
            /** @var array<string, array<string, mixed>> $payload */
            $payload = $form->getPhpValues();
            $payload[$formName]['title'] = ['invalid'];

            return [
                    'method'  => 'POST',
                    'path'    => '/boards',
                    'payload' => $payload,
                   ];
        }

        self::assertNotNull($boardId);
        $form = $this->createUpdateFormFixture($crawler, $boardId, 'Gamma Board', false);
        $formName = $form->getName();
        /** @var array<string, array<string, mixed>> $payload */
        $payload = $form->getPhpValues();
        $payload[$formName]['title'] = ['invalid'];

        return [
                'method'  => 'PATCH',
                'path'    => sprintf('/boards/%d', $boardId),
                'payload' => $payload,
               ];
    }
}
