<?php

declare(strict_types=1);

namespace App\Tests\Service;

use App\Entity\Board;
use App\Repository\BoardRepository;
use App\Service\BoardFormFactory;
use App\Service\BoardPageDataBuilder;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Form\Forms;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Routing\RequestContext;

final class BoardPageDataBuilderTest extends TestCase
{
    #[Test]
    public function itBuildsIndexDataWithDefaultForms(): void
    {
        $boards = [
                   $this->createBoardWithId(1),
                   $this->createBoardWithId(2),
                  ];
        $repository = $this->createMock(BoardRepository::class);
        $repository->expects(self::once())->method('findAll')->willReturn($boards);

        $builder = new BoardPageDataBuilder($repository, $this->createBoardFormFactory());

        $data = $builder->buildIndexData();

        self::assertSame($boards, $data['boards']);
        self::assertSame('board_create', $data['createForm']->vars['name']);
        self::assertSame('board_1', $data['updateForms'][1]->vars['name']);
        self::assertSame('board_2', $data['updateForms'][2]->vars['name']);
        self::assertSame('board_delete_1', $data['deleteForms'][1]->vars['name']);
        self::assertSame('board_delete_2', $data['deleteForms'][2]->vars['name']);
    }

    #[Test]
    public function itUsesProvidedFormsWhenBuildingIndexData(): void
    {
        $boards = [
                   $this->createBoardWithId(1),
                   $this->createBoardWithId(2),
                  ];
        $repository = $this->createMock(BoardRepository::class);
        $repository->expects(self::once())->method('findAll')->willReturn($boards);

        $formFactory = Forms::createFormFactoryBuilder()->getFormFactory();
        $customCreateForm = $formFactory->createNamed('custom_create');
        $errorUpdateForm = $formFactory->createNamed('board_error_2');

        $builder = new BoardPageDataBuilder($repository, $this->createBoardFormFactory());

        $data = $builder->buildIndexData($customCreateForm, $errorUpdateForm, 2);

        self::assertSame('custom_create', $data['createForm']->vars['name']);
        self::assertSame('board_1', $data['updateForms'][1]->vars['name']);
        self::assertSame('board_error_2', $data['updateForms'][2]->vars['name']);
    }

    private function createBoardFormFactory(): BoardFormFactory
    {
        $formFactory = Forms::createFormFactoryBuilder()->getFormFactory();
        $urlGenerator = new class () implements UrlGeneratorInterface {
            private RequestContext $context;

            public function __construct()
            {
                $this->context = new RequestContext();
            }

            /**
             * @param array<string, mixed> $parameters
             */
            #[\Override]
            public function generate(
                string $name,
                array $parameters = [],
                int $referenceType = UrlGeneratorInterface::ABSOLUTE_PATH
            ): string {
                if ($referenceType !== UrlGeneratorInterface::ABSOLUTE_PATH) {
                    throw new \InvalidArgumentException('Only ABSOLUTE_PATH is supported.');
                }

                if ($name === 'board_create') {
                    return '/boards';
                }

                if (!isset($parameters['id']) || !is_int($parameters['id'])) {
                    throw new \InvalidArgumentException('Missing board id.');
                }

                $id = $parameters['id'];

                return match ($name) {
                    'board_update' => sprintf('/boards/%d', $id),
                    'board_delete' => sprintf('/boards/%d', $id),
                    default => throw new \InvalidArgumentException('Unknown route.'),
                };
            }

            #[\Override]
            public function setContext(RequestContext $context): void
            {
                $this->context = $context;
            }

            #[\Override]
            public function getContext(): RequestContext
            {
                return $this->context;
            }
        };

        return new BoardFormFactory($formFactory, $urlGenerator);
    }

    private function createBoardWithId(int $id): Board
    {
        $board = new Board();
        $reflection = new \ReflectionProperty(Board::class, 'id');
        $reflection->setAccessible(true);
        $reflection->setValue($board, $id);

        return $board;
    }
}
