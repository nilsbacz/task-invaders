<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260531190000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Add task spawn cursors and task instance tracking.';
    }

    public function up(Schema $schema): void // phpcs:ignore Generic.CodeAnalysis.UnusedFunctionParameter
    {
        $this->addSql(<<<'SQL'
ALTER TABLE task
    ADD created_at DATETIME DEFAULT NULL,
    ADD next_spawn_at DATETIME DEFAULT NULL
SQL);
        $this->addSql(<<<'SQL'
UPDATE task
    SET created_at = spawn_date,
        next_spawn_at = spawn_date
SQL);
        $this->addSql(<<<'SQL'
ALTER TABLE task
    CHANGE created_at created_at DATETIME NOT NULL
SQL);
        $this->addSql(<<<'SQL'
CREATE INDEX IDX_TASK_NEXT_SPAWN_AT ON task (next_spawn_at)
SQL);
        $this->addSql(<<<'SQL'
CREATE TABLE task_instance (
    id INT AUTO_INCREMENT NOT NULL,
    task_id INT NOT NULL,
    spawned_at DATETIME NOT NULL,
    reaches_base_at DATETIME NOT NULL,
    completed_at DATETIME DEFAULT NULL,
    resolved_at DATETIME DEFAULT NULL,
    resolution VARCHAR(32) DEFAULT NULL,
    created_at DATETIME NOT NULL,
    INDEX IDX_TASK_INSTANCE_ACTIVE (resolved_at, completed_at, reaches_base_at),
    INDEX IDX_TASK_INSTANCE_TASK_ACTIVE (task_id, resolved_at),
    UNIQUE INDEX UNIQ_TASK_INSTANCE_TASK_SPAWNED_AT (task_id, spawned_at),
    PRIMARY KEY (id)
) DEFAULT CHARACTER SET utf8mb4
SQL);
        $this->addSql(<<<'SQL'
ALTER TABLE task_instance
    ADD CONSTRAINT FK_TASK_INSTANCE_TASK FOREIGN KEY (task_id) REFERENCES task (id) ON DELETE CASCADE
SQL);
    }

    public function down(Schema $schema): void // phpcs:ignore Generic.CodeAnalysis.UnusedFunctionParameter
    {
        $this->addSql(<<<'SQL'
ALTER TABLE task_instance DROP FOREIGN KEY FK_TASK_INSTANCE_TASK
SQL);
        $this->addSql(<<<'SQL'
DROP TABLE task_instance
SQL);
        $this->addSql(<<<'SQL'
DROP INDEX IDX_TASK_NEXT_SPAWN_AT ON task
SQL);
        $this->addSql(<<<'SQL'
ALTER TABLE task
    DROP created_at,
    DROP next_spawn_at
SQL);
    }
}
