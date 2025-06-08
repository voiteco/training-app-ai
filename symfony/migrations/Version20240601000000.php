<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20240601000000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Create initial database schema with trainings, bookings, user_sessions, users, and training_reviews tables';
    }

    public function up(Schema $schema): void
    {
        // Create user table
        $this->addSql('CREATE TABLE `user` (
            id INT AUTO_INCREMENT NOT NULL,
            email VARCHAR(180) NOT NULL,
            roles JSON NOT NULL,
            password VARCHAR(255) NOT NULL,
            created_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\',
            UNIQUE INDEX UNIQ_8D93D649E7927C74 (email),
            PRIMARY KEY(id)
        ) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');

        // Create training table
        $this->addSql('CREATE TABLE training (
            id INT AUTO_INCREMENT NOT NULL,
            google_sheet_id VARCHAR(255) NOT NULL,
            date DATE NOT NULL,
            time TIME NOT NULL,
            title VARCHAR(255) NOT NULL,
            slots INT NOT NULL,
            slots_available INT NOT NULL,
            price DOUBLE PRECISION NOT NULL,
            created_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\',
            updated_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\',
            PRIMARY KEY(id)
        ) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');

        // Create booking table
        $this->addSql('CREATE TABLE booking (
            id INT AUTO_INCREMENT NOT NULL,
            training_id INT NOT NULL,
            full_name VARCHAR(255) NOT NULL,
            email VARCHAR(255) NOT NULL,
            phone VARCHAR(255) NOT NULL,
            confirmation_token VARCHAR(255) NOT NULL,
            status VARCHAR(255) NOT NULL,
            device_token VARCHAR(255) DEFAULT NULL,
            created_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\',
            updated_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\',
            INDEX IDX_E00CEDDEBEBD06BB (training_id),
            PRIMARY KEY(id)
        ) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');

        // Create user_session table
        $this->addSql('CREATE TABLE user_session (
            id INT AUTO_INCREMENT NOT NULL,
            device_token VARCHAR(255) NOT NULL,
            full_name VARCHAR(255) NOT NULL,
            email VARCHAR(255) NOT NULL,
            phone VARCHAR(255) NOT NULL,
            last_visit DATETIME NOT NULL,
            created_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\',
            updated_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\',
            PRIMARY KEY(id)
        ) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');

        // Create training_review table
        $this->addSql('CREATE TABLE training_review (
            id INT AUTO_INCREMENT NOT NULL,
            training_id INT NOT NULL,
            user_id INT DEFAULT NULL,
            rating INT NOT NULL,
            comment LONGTEXT DEFAULT NULL,
            created_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\',
            INDEX IDX_9A11B1FBBEBD06BB (training_id),
            INDEX IDX_9A11B1FBA76ED395 (user_id),
            PRIMARY KEY(id)
        ) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');

        // Add foreign key constraints
        $this->addSql('ALTER TABLE booking ADD CONSTRAINT FK_E00CEDDEBEBD06BB FOREIGN KEY (training_id) REFERENCES training (id)');
        $this->addSql('ALTER TABLE training_review ADD CONSTRAINT FK_9A11B1FBBEBD06BB FOREIGN KEY (training_id) REFERENCES training (id)');
        $this->addSql('ALTER TABLE training_review ADD CONSTRAINT FK_9A11B1FBA76ED395 FOREIGN KEY (user_id) REFERENCES `user` (id)');
    }

    public function down(Schema $schema): void
    {
        // Drop foreign key constraints first
        $this->addSql('ALTER TABLE booking DROP FOREIGN KEY FK_E00CEDDEBEBD06BB');
        $this->addSql('ALTER TABLE training_review DROP FOREIGN KEY FK_9A11B1FBBEBD06BB');
        $this->addSql('ALTER TABLE training_review DROP FOREIGN KEY FK_9A11B1FBA76ED395');

        // Drop tables
        $this->addSql('DROP TABLE booking');
        $this->addSql('DROP TABLE training');
        $this->addSql('DROP TABLE training_review');
        $this->addSql('DROP TABLE user_session');
        $this->addSql('DROP TABLE `user`');
    }
}
