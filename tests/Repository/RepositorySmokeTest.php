<?php

declare(strict_types=1);

namespace App\Tests\Repository;

use App\Repository\BoardRepository;
use App\Repository\RowRepository;
use App\Repository\SpriteRepository;
use App\Repository\TaskDescriptionRepository;
use App\Repository\TaskRepository;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

#[CoversClass(BoardRepository::class)]
#[CoversClass(RowRepository::class)]
#[CoversClass(SpriteRepository::class)]
#[CoversClass(TaskDescriptionRepository::class)]
#[CoversClass(TaskRepository::class)]
final class RepositorySmokeTest extends TestCase
{
    /**
     * @param class-string $repositoryClass
     */
    #[Test]
    #[DataProvider('repositoryProvider')]
    public function itConstructsRepositories(string $repositoryClass): void
    {
        $registry = $this->createStub(ManagerRegistry::class);

        $repository = new $repositoryClass($registry);

        self::assertInstanceOf($repositoryClass, $repository);
        self::assertInstanceOf(ServiceEntityRepository::class, $repository);
    }

    /**
     * @return array<string, array{0: class-string}>
     */
    public static function repositoryProvider(): array
    {
        return [
                'board'            => [BoardRepository::class],
                'row'              => [RowRepository::class],
                'sprite'           => [SpriteRepository::class],
                'task_description' => [TaskDescriptionRepository::class],
                'task'             => [TaskRepository::class],
               ];
    }
}
