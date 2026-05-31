<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260531173000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Track task completion and allow completed task records to detach from board rows.';
    }

    public function up(Schema $schema): void // phpcs:ignore Generic.CodeAnalysis.UnusedFunctionParameter
    {
        $this->addSql(<<<'SQL'
ALTER TABLE task
    ADD completed_at DATETIME DEFAULT NULL,
    CHANGE board_row_id board_row_id INT DEFAULT NULL
SQL);
    }

    public function down(Schema $schema): void // phpcs:ignore Generic.CodeAnalysis.UnusedFunctionParameter
    {
        $this->addSql(<<<'SQL'
ALTER TABLE task
    DROP completed_at,
    CHANGE board_row_id board_row_id INT NOT NULL
SQL);
    }
}
