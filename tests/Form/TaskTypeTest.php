<?php

declare(strict_types=1);

namespace App\Tests\Form;

use App\Board\Application\CreateTask;
use App\Entity\Task;
use App\Enum\TaskRiskLevel;
use App\Form\TaskType;
use Override;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\Attributes\UsesClass;
use Symfony\Component\EventDispatcher\EventDispatcher;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\EnumType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Test\TypeTestCase;

#[CoversClass(TaskType::class)]
#[UsesClass(CreateTask::class)]
#[UsesClass(Task::class)]
#[UsesClass(TaskRiskLevel::class)]
final class TaskTypeTest extends TypeTestCase
{
    #[Test]
    public function itBuildsExpectedFieldsAndOptions(): void
    {
        // Arrange & Act
        $form = $this->factory->create(TaskType::class);

        // Assert
        self::assertSame(Task::class, $form->getConfig()->getOption('data_class'));

        self::assertTrue($form->has('title'));
        $titleField = $form->get('title');
        self::assertInstanceOf(TextType::class, $titleField->getConfig()->getType()->getInnerType());
        self::assertSame('Task title', $titleField->getConfig()->getOption('label'));
        self::assertTrue($titleField->getConfig()->getOption('required'));
        self::assertSame('', $titleField->getConfig()->getOption('empty_data'));
        $titleAttrs = $titleField->getConfig()->getOption('attr');
        self::assertIsArray($titleAttrs);
        self::assertSame(32, $titleAttrs['maxlength'] ?? null);

        self::assertInstanceOf(EnumType::class, $form->get('riskLevel')->getConfig()->getType()->getInnerType());
        self::assertInstanceOf(IntegerType::class, $form->get('respawnsIn')->getConfig()->getType()->getInnerType());
        self::assertInstanceOf(IntegerType::class, $form->get('spawnsEvery')->getConfig()->getType()->getInnerType());
        self::assertInstanceOf(IntegerType::class, $form->get('reachesBaseIn')->getConfig()->getType()->getInnerType());
        self::assertInstanceOf(IntegerType::class, $form->get('speedFactor')->getConfig()->getType()->getInnerType());
        self::assertInstanceOf(CheckboxType::class, $form->get('hasShield')->getConfig()->getType()->getInnerType());
        self::assertInstanceOf(
            CheckboxType::class,
            $form->get('respawnImmediatelyAfterDeath')->getConfig()->getType()->getInnerType()
        );
    }

    #[Test]
    public function itSubmitsDataIntoTask(): void
    {
        // Arrange
        $form = $this->factory->create(TaskType::class);

        // Act
        $form->submit([
                       'title'                        => 'Workout',
                       'riskLevel'                    => 'YELLOW',
                       'respawnsIn'                   => '15',
                       'spawnsEvery'                  => '45',
                       'reachesBaseIn'                => '90',
                       'hasShield'                    => '1',
                       'respawnImmediatelyAfterDeath' => '1',
                       'speedFactor'                  => '2',
                      ]);

        // Assert
        self::assertTrue($form->isSynchronized());

        $data = $form->getData();
        self::assertInstanceOf(Task::class, $data);
        self::assertSame('Workout', $data->getTitle());
        self::assertSame(TaskRiskLevel::YELLOW, $data->getRiskLevel());
        self::assertSame(15, $data->getRespawnsIn());
        self::assertSame(45, $data->getSpawnsEvery());
        self::assertSame(90, $data->getReachesBaseIn());
        self::assertTrue($data->hasShield());
        self::assertTrue($data->isRespawnImmediatelyAfterDeath());
        self::assertSame(2, $data->getSpeedFactor());
    }

    #[Test]
    public function itCanBindToCreateTaskCommand(): void
    {
        // Arrange
        $form = $this->factory->create(TaskType::class, new CreateTask(), ['data_class' => CreateTask::class]);

        // Act
        $form->submit([
                       'title'         => 'Admin',
                       'riskLevel'     => 'RED',
                       'respawnsIn'    => '5',
                       'spawnsEvery'   => '10',
                       'reachesBaseIn' => '20',
                       'hasShield'     => '1',
                       'speedFactor'   => '3',
                      ]);

        // Assert
        self::assertTrue($form->isSynchronized());

        $data = $form->getData();
        self::assertInstanceOf(CreateTask::class, $data);
        self::assertSame('Admin', $data->getTitle());
        self::assertSame(TaskRiskLevel::RED, $data->getRiskLevel());
        self::assertSame(5, $data->getRespawnsIn());
        self::assertSame(10, $data->getSpawnsEvery());
        self::assertSame(20, $data->getReachesBaseIn());
        self::assertTrue($data->hasShield());
        self::assertFalse($data->isRespawnImmediatelyAfterDeath());
        self::assertSame(3, $data->getSpeedFactor());
    }

    #[Test]
    public function itAllowsCustomTitleMaxLength(): void
    {
        // Arrange & Act
        $form = $this->factory->create(TaskType::class, options: ['title_max_length' => 24]);
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
