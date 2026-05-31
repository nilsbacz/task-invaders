<?php

declare(strict_types=1);

namespace App\Tests\Service;

use App\Service\BoardDetailFormErrors;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Form\FormInterface;

#[CoversClass(BoardDetailFormErrors::class)]
final class BoardDetailFormErrorsTest extends TestCase
{
    #[Test]
    public function itCreatesEmptyErrors(): void
    {
        // Arrange & Act
        $errors = BoardDetailFormErrors::none();

        // Assert
        self::assertNull($errors->rowCreateForm);
        self::assertNull($errors->rowUpdateForm);
        self::assertNull($errors->rowUpdateId);
        self::assertNull($errors->taskCreateForm);
        self::assertNull($errors->taskCreateRowId);
        self::assertNull($errors->taskUpdateForm);
        self::assertNull($errors->taskUpdateId);
        self::assertNull($errors->taskShootForm);
        self::assertNull($errors->taskShootId);
    }

    #[Test]
    public function itCreatesSpecificErrorStates(): void
    {
        // Arrange
        $form = $this->createStub(FormInterface::class);

        // Act
        $rowCreate = BoardDetailFormErrors::rowCreate($form);
        $rowUpdate = BoardDetailFormErrors::rowUpdate(7, $form);
        $taskCreate = BoardDetailFormErrors::taskCreate(8, $form);
        $taskUpdate = BoardDetailFormErrors::taskUpdate(9, $form);
        $taskShoot = BoardDetailFormErrors::taskShoot(10, $form);

        // Assert
        self::assertSame($form, $rowCreate->rowCreateForm);
        self::assertSame($form, $rowUpdate->rowUpdateForm);
        self::assertSame(7, $rowUpdate->rowUpdateId);
        self::assertSame($form, $taskCreate->taskCreateForm);
        self::assertSame(8, $taskCreate->taskCreateRowId);
        self::assertSame($form, $taskUpdate->taskUpdateForm);
        self::assertSame(9, $taskUpdate->taskUpdateId);
        self::assertSame($form, $taskShoot->taskShootForm);
        self::assertSame(10, $taskShoot->taskShootId);
    }
}
