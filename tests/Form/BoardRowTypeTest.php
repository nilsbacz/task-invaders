<?php

declare(strict_types=1);

namespace App\Tests\Form;

use App\Board\Application\CreateBoardRow;
use App\Board\Domain\BoardRow;
use App\Form\BoardRowType;
use Override;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use Symfony\Component\EventDispatcher\EventDispatcher;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Test\TypeTestCase;

#[CoversClass(BoardRowType::class)]
final class BoardRowTypeTest extends TypeTestCase
{
    #[Test]
    public function itBuildsExpectedFieldsAndOptions(): void
    {
        // Arrange & Act
        $form = $this->factory->create(BoardRowType::class);

        // Assert
        self::assertSame(BoardRow::class, $form->getConfig()->getOption('data_class'));

        self::assertTrue($form->has('title'));
        $titleField = $form->get('title');
        self::assertInstanceOf(TextType::class, $titleField->getConfig()->getType()->getInnerType());
        self::assertSame('Row title', $titleField->getConfig()->getOption('label'));
        self::assertTrue($titleField->getConfig()->getOption('required'));
        self::assertSame('', $titleField->getConfig()->getOption('empty_data'));
        $titleAttrs = $titleField->getConfig()->getOption('attr');
        self::assertIsArray($titleAttrs);
        self::assertSame(32, $titleAttrs['maxlength'] ?? null);
    }

    #[Test]
    public function itSubmitsDataIntoBoardRow(): void
    {
        // Arrange
        $form = $this->factory->create(BoardRowType::class);

        // Act
        $form->submit(['title' => 'Household']);

        // Assert
        self::assertTrue($form->isSynchronized());

        $data = $form->getData();
        self::assertInstanceOf(BoardRow::class, $data);
        self::assertSame('Household', $data->getTitle());
    }

    #[Test]
    public function itCanBindToCreateBoardRowCommand(): void
    {
        // Arrange
        $form = $this->factory->create(
            BoardRowType::class,
            new CreateBoardRow(),
            ['data_class' => CreateBoardRow::class]
        );

        // Act
        $form->submit(['title' => 'Projects']);

        // Assert
        self::assertTrue($form->isSynchronized());

        $data = $form->getData();
        self::assertInstanceOf(CreateBoardRow::class, $data);
        self::assertSame('Projects', $data->getTitle());
    }

    #[Test]
    public function itAllowsCustomTitleMaxLength(): void
    {
        // Arrange & Act
        $form = $this->factory->create(BoardRowType::class, options: ['title_max_length' => 24]);
        $titleField = $form->get('title');

        // Assert
        $titleAttrs = $titleField->getConfig()->getOption('attr');
        self::assertIsArray($titleAttrs);
        self::assertSame(24, $titleAttrs['maxlength'] ?? null);
    }

    #[Override]
    protected function setUp(): void
    {
        $this->dispatcher = new EventDispatcher();

        parent::setUp();
    }
}
