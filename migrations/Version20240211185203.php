<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20240211185203 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE post ADD thread_id INT NOT NULL, ADD user_id INT NOT NULL');
        $this->addSql('ALTER TABLE post ADD CONSTRAINT FK_5A8A6C8DE2904019 FOREIGN KEY (thread_id) REFERENCES thread (thread_id)');
        $this->addSql('ALTER TABLE post ADD CONSTRAINT FK_5A8A6C8DA76ED395 FOREIGN KEY (user_id) REFERENCES user (user_id)');
        $this->addSql('CREATE INDEX IDX_5A8A6C8DE2904019 ON post (thread_id)');
        $this->addSql('CREATE INDEX IDX_5A8A6C8DA76ED395 ON post (user_id)');
        $this->addSql('ALTER TABLE thread ADD forum_id INT NOT NULL, ADD user_id INT NOT NULL');
        $this->addSql('ALTER TABLE thread ADD CONSTRAINT FK_31204C8329CCBAD0 FOREIGN KEY (forum_id) REFERENCES forum (forum_id)');
        $this->addSql('ALTER TABLE thread ADD CONSTRAINT FK_31204C83A76ED395 FOREIGN KEY (user_id) REFERENCES user (user_id)');
        $this->addSql('CREATE INDEX IDX_31204C8329CCBAD0 ON thread (forum_id)');
        $this->addSql('CREATE INDEX IDX_31204C83A76ED395 ON thread (user_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE post DROP FOREIGN KEY FK_5A8A6C8DE2904019');
        $this->addSql('ALTER TABLE post DROP FOREIGN KEY FK_5A8A6C8DA76ED395');
        $this->addSql('DROP INDEX IDX_5A8A6C8DE2904019 ON post');
        $this->addSql('DROP INDEX IDX_5A8A6C8DA76ED395 ON post');
        $this->addSql('ALTER TABLE post DROP thread_id, DROP user_id');
        $this->addSql('ALTER TABLE thread DROP FOREIGN KEY FK_31204C8329CCBAD0');
        $this->addSql('ALTER TABLE thread DROP FOREIGN KEY FK_31204C83A76ED395');
        $this->addSql('DROP INDEX IDX_31204C8329CCBAD0 ON thread');
        $this->addSql('DROP INDEX IDX_31204C83A76ED395 ON thread');
        $this->addSql('ALTER TABLE thread DROP forum_id, DROP user_id');
    }
}
