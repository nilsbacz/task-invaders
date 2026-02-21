<?php

declare(strict_types=1);

namespace App\Tests\Form;

use App\Entity\Board;
use App\Form\BoardType;
use Override;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use Symfony\Component\EventDispatcher\EventDispatcher;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Test\TypeTestCase;

#[CoversClass(BoardType::class)]
final class BoardTypeTest extends TypeTestCase
{
    #[Test]
    public function itBuildsExpectedFieldsAndOptions(): void
    {
        $form = $this->factory->create(BoardType::class);

        self::assertSame(Board::class, $form->getConfig()->getOption('data_class'));

        self::assertTrue($form->has('title'));
        $titleField = $form->get('title');
        self::assertInstanceOf(TextType::class, $titleField->getConfig()->getType()->getInnerType());
        self::assertSame('Title', $titleField->getConfig()->getOption('label'));
        self::assertTrue($titleField->getConfig()->getOption('required'));
        self::assertSame('', $titleField->getConfig()->getOption('empty_data'));
        $titleAttrs = $titleField->getConfig()->getOption('attr');
        self::assertIsArray($titleAttrs);
        self::assertSame(255, $titleAttrs['maxlength'] ?? null);

        self::assertTrue($form->has('isTurretMode'));
        $turretField = $form->get('isTurretMode');
        self::assertInstanceOf(CheckboxType::class, $turretField->getConfig()->getType()->getInnerType());
        self::assertSame('Turret mode', $turretField->getConfig()->getOption('label'));
        self::assertFalse($turretField->getConfig()->getOption('required'));
    }

    #[Test]
    public function itSubmitsDataIntoBoard(): void
    {
        $form = $this->factory->create(BoardType::class);

        $form->submit([
                       'title'        => 'Form Board',
                       'isTurretMode' => true,
                      ]);

        self::assertTrue($form->isSynchronized());

        $data = $form->getData();
        self::assertInstanceOf(Board::class, $data);
        self::assertSame('Form Board', $data->getTitle());
        self::assertTrue($data->isTurretMode());
    }

    #[Test]
    public function itAllowsCustomTitleMaxLength(): void
    {
        $form = $this->factory->create(BoardType::class, options: ['title_max_length' => 120]);
        $titleField = $form->get('title');

        $titleAttrs = $titleField->getConfig()->getOption('attr');
        self::assertIsArray($titleAttrs);
        self::assertSame(120, $titleAttrs['maxlength'] ?? null);
    }

    #[Override]
    protected function setUp(): void
    {
        $this->dispatcher = new EventDispatcher();

        parent::setUp();
    }
}
