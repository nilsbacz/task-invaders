<?php

declare(strict_types=1);

namespace App\Tests\Service;

use App\Entity\Board;
use App\Service\BoardFormFactory;
use InvalidArgumentException;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Forms;
use Symfony\Component\Routing\RequestContext;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

final class BoardFormFactoryTest extends TestCase
{
    private BoardFormFactory $factory;
    private \Symfony\Component\Form\FormFactoryInterface $formFactory;

    #[\Override]
    protected function setUp(): void
    {
        $this->formFactory = Forms::createFormFactoryBuilder()->getFormFactory();
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
                    throw new InvalidArgumentException('Only ABSOLUTE_PATH is supported.');
                }

                if ($name === 'board_create') {
                    return '/boards';
                }

                if (!isset($parameters['id']) || !is_int($parameters['id'])) {
                    throw new InvalidArgumentException('Missing board id.');
                }

                $id = $parameters['id'];

                return match ($name) {
                    'board_update', 'board_delete' => sprintf('/boards/%d', $id),
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

        $this->factory = new BoardFormFactory($this->formFactory, $urlGenerator);
    }

    #[Test]
    public function itBuildsCreateForm(): void
    {
        $form = $this->factory->buildCreateForm();

        self::assertSame('board_create', $form->getName());
        self::assertSame('/boards', $form->getConfig()->getOption('action'));
        self::assertSame('POST', $form->getConfig()->getOption('method'));
        self::assertTrue($form->has('title'));
        self::assertTrue($form->has('isTurretMode'));
    }

    #[Test]
    #[DataProvider('updateDeleteFormProvider')]
    public function itBuildsUpdateAndDeleteForms(string $type, string $expectedName, string $expectedMethod): void
    {
        $board = $this->createBoardWithId(7);

        $form = $type === 'update' ? $this->factory->buildUpdateForm($board) : $this->factory->buildDeleteForm($board);

        self::assertSame($expectedName, $form->getName());
        self::assertSame('/boards/7', $form->getConfig()->getOption('action'));
        self::assertSame($expectedMethod, $form->getConfig()->getOption('method'));

        if ($type === 'update') {
            self::assertTrue($form->has('title'));
            self::assertTrue($form->has('isTurretMode'));
        }

        if ($type === 'delete') {
            self::assertTrue($form->has('confirm'));
            self::assertInstanceOf(HiddenType::class, $form->get('confirm')->getConfig()->getType()->getInnerType());
        }
    }

    #[Test]
    public function itBuildsFormViewsForBoards(): void
    {
        $boardOne = $this->createBoardWithId(1);
        $boardTwo = $this->createBoardWithId(2);
        $boardWithoutId = new Board();

        $errorForm = $this->formFactory->createNamed('board_error_2');

        $updateViews = $this->factory->buildUpdateFormViews(
            [
             $boardOne,
             $boardTwo,
             $boardWithoutId,
            ],
            2,
            $errorForm
        );
        $deleteViews = $this->factory->buildDeleteFormViews([$boardOne, $boardTwo, $boardWithoutId]);

        self::assertCount(2, $updateViews);
        self::assertSame('board_1', $updateViews[1]->vars['name']);
        self::assertSame('board_error_2', $updateViews[2]->vars['name']);

        self::assertCount(2, $deleteViews);
        self::assertSame('board_delete_1', $deleteViews[1]->vars['name']);
        self::assertSame('board_delete_2', $deleteViews[2]->vars['name']);
    }

    /**
     * @return array<string, array{string, string, string}>
     */
    public static function updateDeleteFormProvider(): array
    {
        return [
                'update' => [
                             'update',
                             'board_7',
                             'PATCH',
                            ],
                'delete' => [
                             'delete',
                             'board_delete_7',
                             'DELETE',
                            ],
               ];
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
