<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20230106175717 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE answer_group (id INT AUTO_INCREMENT NOT NULL, survey_id INT NOT NULL, INDEX IDX_F6FAAD96B3FE509D (survey_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE answer_group ADD CONSTRAINT FK_F6FAAD96B3FE509D FOREIGN KEY (survey_id) REFERENCES survey (id)');
        $this->addSql('ALTER TABLE answer DROP FOREIGN KEY FK_DADD4A25B3FE509D');
        $this->addSql('ALTER TABLE answer DROP FOREIGN KEY FK_DADD4A251E27F6BF');
        $this->addSql('DROP INDEX IDX_DADD4A25B3FE509D ON answer');
        $this->addSql('DROP INDEX IDX_DADD4A251E27F6BF ON answer');
        $this->addSql('ALTER TABLE answer ADD answer_group_id INT NOT NULL, DROP survey_id, DROP question_id');
        $this->addSql('ALTER TABLE answer ADD CONSTRAINT FK_DADD4A25D91ED822 FOREIGN KEY (answer_group_id) REFERENCES answer_group (id)');
        $this->addSql('CREATE INDEX IDX_DADD4A25D91ED822 ON answer (answer_group_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE answer DROP FOREIGN KEY FK_DADD4A25D91ED822');
        $this->addSql('ALTER TABLE answer_group DROP FOREIGN KEY FK_F6FAAD96B3FE509D');
        $this->addSql('DROP TABLE answer_group');
        $this->addSql('DROP INDEX IDX_DADD4A25D91ED822 ON answer');
        $this->addSql('ALTER TABLE answer ADD question_id INT NOT NULL, CHANGE answer_group_id survey_id INT NOT NULL');
        $this->addSql('ALTER TABLE answer ADD CONSTRAINT FK_DADD4A25B3FE509D FOREIGN KEY (survey_id) REFERENCES survey (id)');
        $this->addSql('ALTER TABLE answer ADD CONSTRAINT FK_DADD4A251E27F6BF FOREIGN KEY (question_id) REFERENCES question (id)');
        $this->addSql('CREATE INDEX IDX_DADD4A25B3FE509D ON answer (survey_id)');
        $this->addSql('CREATE INDEX IDX_DADD4A251E27F6BF ON answer (question_id)');
    }
}
