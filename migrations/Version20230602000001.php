<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20230602000001 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Add geolocation and calendar integration fields';
    }

    public function up(Schema $schema): void
    {
        // Add geolocation fields to User entity
        $this->addSql('ALTER TABLE `user` ADD latitude DOUBLE PRECISION DEFAULT NULL, ADD longitude DOUBLE PRECISION DEFAULT NULL');
        
        // Add calendar and location fields to Session entity
        $this->addSql('ALTER TABLE session ADD duration INT DEFAULT 60, ADD location VARCHAR(255) DEFAULT NULL, ADD latitude DOUBLE PRECISION DEFAULT NULL, ADD longitude DOUBLE PRECISION DEFAULT NULL, ADD reminder_sent TINYINT(1) DEFAULT 0, ADD reminder_minutes_before INT DEFAULT 30, ADD calendar_event_id VARCHAR(255) DEFAULT NULL');
    }

    public function down(Schema $schema): void
    {
        // Remove geolocation fields from User entity
        $this->addSql('ALTER TABLE `user` DROP latitude, DROP longitude');
        
        // Remove calendar and location fields from Session entity
        $this->addSql('ALTER TABLE session DROP duration, DROP location, DROP latitude, DROP longitude, DROP reminder_sent, DROP reminder_minutes_before, DROP calendar_event_id');
    }
} 