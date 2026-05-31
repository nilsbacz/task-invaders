<?php

declare(strict_types=1);

namespace App\Tests\Service;

use App\Board\Application\CreateBoardRow;
use App\Board\Domain\Board;
use App\Board\Domain\BoardRow;
use App\Service\BoardRowFormFactory;
use InvalidArgumentException;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\Attributes\UsesClass;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\Forms;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Routing\RequestContext;

#[CoversClass(BoardRowFormFactory::class)]
#[UsesClass(Board::class)]
#[UsesClass(BoardRow::class)]
#[UsesClass(CreateBoardRow::class)]
final class BoardRowFormFactoryTest extends TestCase
{
    private BoardRowFormFactory $factory;
    private FormFactoryInterface $formFactory;

    #[\Override]
    protected function setUp(): void
    {
        $this->formFactory = Forms::createFormFactoryBuilder()->getFormFactory();
        $this->factory = new BoardRowFormFactory($this->formFactory, $this->createUrlGeneratorFixture());
    }

    #[Test]
    public function itBuildsCreateForm(): void
    {
        // Arrange
        $board = $this->createBoardFixtureWithId(7);

        // Act
        $form = $this->factory->buildCreateForm($board);

        // Assert
        self::assertSame('board_row_create', $form->getName());
        self::assertSame('/boards/7/rows', $form->getConfig()->getOption('action'));
        self::assertSame('POST', $form->getConfig()->getOption('method'));
        self::assertSame(CreateBoardRow::class, $form->getConfig()->getOption('data_class'));
        self::assertTrue($form->has('title'));
    }

    #[Test]
    public function itBuildsUpdateAndDeleteForms(): void
    {
        // Arrange
        $board = $this->createBoardFixtureWithId(7);
        $boardRow = $this->createBoardRowFixtureWithId(13);

        // Act
        $updateForm = $this->factory->buildUpdateForm($board, $boardRow);
        $deleteForm = $this->factory->buildDeleteForm($board, $boardRow);

        // Assert
        self::assertSame('board_row_13', $updateForm->getName());
        self::assertSame('/boards/7/rows/13', $updateForm->getConfig()->getOption('action'));
        self::assertSame('PATCH', $updateForm->getConfig()->getOption('method'));
        self::assertTrue($updateForm->has('title'));

        self::assertSame('board_row_delete_13', $deleteForm->getName());
        self::assertSame('/boards/7/rows/13', $deleteForm->getConfig()->getOption('action'));
        self::assertSame('DELETE', $deleteForm->getConfig()->getOption('method'));
        self::assertTrue($deleteForm->has('confirm'));
        self::assertInstanceOf(HiddenType::class, $deleteForm->get('confirm')->getConfig()->getType()->getInnerType());
    }

    #[Test]
    public function itBuildsFormViewsForPersistedBoardRows(): void
    {
        // Arrange
        $board = $this->createBoardFixtureWithId(7);
        $rowOne = $this->createBoardRowFixtureWithId(13);
        $rowTwo = $this->createBoardRowFixtureWithId(14);
        $rowWithoutId = new BoardRow();
        $rowWithoutId->setTitle('Draft');
        $rowWithoutId->setRowNumber(3);
        $board->addBoardRow($rowOne);
        $board->addBoardRow($rowTwo);
        $board->addBoardRow($rowWithoutId);
        $errorForm = $this->createErrorFormFixture();

        // Act
        $updateViews = $this->factory->buildUpdateFormViews($board, 14, $errorForm);
        $deleteViews = $this->factory->buildDeleteFormViews($board);

        // Assert
        self::assertCount(2, $updateViews);
        self::assertSame('board_row_13', $updateViews[13]->vars['name']);
        self::assertSame('board_row_error_14', $updateViews[14]->vars['name']);

        self::assertCount(2, $deleteViews);
        self::assertSame('board_row_delete_13', $deleteViews[13]->vars['name']);
        self::assertSame('board_row_delete_14', $deleteViews[14]->vars['name']);
    }

    private function createUrlGeneratorFixture(): UrlGeneratorInterface
    {
        return new class () implements UrlGeneratorInterface {
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
                int $referenceType = UrlGeneratorInterface::ABSOLUTE_PATH,
            ): string {
                if ($referenceType !== UrlGeneratorInterface::ABSOLUTE_PATH) {
                    throw new InvalidArgumentException('Only ABSOLUTE_PATH is supported.');
                }

                if (!isset($parameters['id']) || !is_int($parameters['id'])) {
                    throw new InvalidArgumentException('Missing board id.');
                }

                if ($name === 'board_row_create') {
                    return sprintf('/boards/%d/rows', $parameters['id']);
                }

                if (!isset($parameters['rowId']) || !is_int($parameters['rowId'])) {
                    throw new InvalidArgumentException('Missing board row id.');
                }

                return match ($name) {
                    'board_row_update',
                    'board_row_delete' => sprintf('/boards/%d/rows/%d', $parameters['id'], $parameters['rowId']),
                    default => throw new InvalidArgumentException('Unknown route.'),
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
    }

    private function createBoardFixtureWithId(int $id): Board
    {
        $board = new Board();
        $reflection = new \ReflectionProperty(Board::class, 'id');
        $reflection->setAccessible(true);
        $reflection->setValue($board, $id);

        return $board;
    }

    private function createBoardRowFixtureWithId(int $id): BoardRow
    {
        $boardRow = new BoardRow();
        $boardRow->setTitle('Row ' . $id);
        $boardRow->setRowNumber($id);
        $reflection = new \ReflectionProperty(BoardRow::class, 'id');
        $reflection->setAccessible(true);
        $reflection->setValue($boardRow, $id);

        return $boardRow;
    }

    private function createErrorFormFixture(): FormInterface
    {
        return $this->formFactory->createNamed('board_row_error_14');
    }
}
