<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260216214814 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Initial schema for board, row, sprite, task, and task_description.';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql(<<<'SQL'
CREATE TABLE board (
    id INT AUTO_INCREMENT NOT NULL,
    title VARCHAR(255) NOT NULL,
    is_turret_mode TINYINT NOT NULL,
    PRIMARY KEY (id)
) DEFAULT CHARACTER SET utf8mb4
SQL);
        $this->addSql(<<<'SQL'
CREATE TABLE `row` (
    id INT AUTO_INCREMENT NOT NULL,
    title VARCHAR(32) NOT NULL,
    `row_number` INT NOT NULL,
    PRIMARY KEY (id)
) DEFAULT CHARACTER SET utf8mb4
SQL);
        $this->addSql(<<<'SQL'
CREATE TABLE sprite (
    id INT AUTO_INCREMENT NOT NULL,
    title VARCHAR(32) NOT NULL,
    sprite_data LONGBLOB NOT NULL,
    color VARCHAR(9) NOT NULL,
    PRIMARY KEY (id)
) DEFAULT CHARACTER SET utf8mb4
SQL);
        $this->addSql(<<<'SQL'
CREATE TABLE task (
    id INT AUTO_INCREMENT NOT NULL,
    title VARCHAR(32) NOT NULL,
    row_id INT NOT NULL,
    risk_level SMALLINT NOT NULL,
    spawn_date DATETIME NOT NULL,
    respawns_in INT DEFAULT 0 NOT NULL,
    spawns_every INT DEFAULT 0 NOT NULL,
    reaches_base_in INT NOT NULL,
    has_shield TINYINT DEFAULT 0 NOT NULL,
    respawn_immediately_after_death TINYINT DEFAULT 0 NOT NULL,
    speed_factor INT DEFAULT 0 NOT NULL,
    task_description_id INT DEFAULT NULL,
    sprite_id INT DEFAULT NULL,
    INDEX IDX_527EDB2511B6E0C4 (task_description_id),
    INDEX IDX_527EDB254ED1B8A2 (sprite_id),
    PRIMARY KEY (id)
) DEFAULT CHARACTER SET utf8mb4
SQL);
        $this->addSql(<<<'SQL'
CREATE TABLE task_description (
    id INT AUTO_INCREMENT NOT NULL,
    description LONGTEXT NOT NULL,
    PRIMARY KEY (id)
) DEFAULT CHARACTER SET utf8mb4
SQL);
        $this->addSql(<<<'SQL'
ALTER TABLE task
    ADD CONSTRAINT FK_527EDB2511B6E0C4 FOREIGN KEY (task_description_id) REFERENCES task_description (id)
SQL);
        $this->addSql(<<<'SQL'
ALTER TABLE task
    ADD CONSTRAINT FK_527EDB254ED1B8A2 FOREIGN KEY (sprite_id) REFERENCES sprite (id)
SQL);
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql(<<<'SQL'
ALTER TABLE task DROP FOREIGN KEY FK_527EDB2511B6E0C4
SQL);
        $this->addSql(<<<'SQL'
ALTER TABLE task DROP FOREIGN KEY FK_527EDB254ED1B8A2
SQL);
        $this->addSql(<<<'SQL'
DROP TABLE board
SQL);
        $this->addSql(<<<'SQL'
DROP TABLE `row`
SQL);
        $this->addSql(<<<'SQL'
DROP TABLE sprite
SQL);
        $this->addSql(<<<'SQL'
DROP TABLE task
SQL);
        $this->addSql(<<<'SQL'
DROP TABLE task_description
SQL);
    }
}
