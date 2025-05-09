<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20230601000001 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Create FAQ table for Help Center';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('CREATE TABLE faq (
            id INT AUTO_INCREMENT NOT NULL,
            question VARCHAR(255) NOT NULL,
            answer LONGTEXT NOT NULL,
            category VARCHAR(50) NOT NULL,
            display_order INT NOT NULL,
            is_published TINYINT(1) NOT NULL,
            created_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\',
            updated_at DATETIME DEFAULT NULL COMMENT \'(DC2Type:datetime_immutable)\',
            PRIMARY KEY(id)
        ) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        
        // Insert some initial FAQs
        $this->addSql("INSERT INTO faq (question, answer, category, display_order, is_published, created_at)
            VALUES 
            ('What is SkillSwap?', 'SkillSwap is a platform that connects people who want to exchange skills for free. You can teach skills you know, and learn skills from others in your community without any monetary exchange.', 'General', 1, 1, NOW()),
            ('How do I create an account?', 'To create an account, click on the \"Sign Up\" button in the top right corner of the homepage. You\'ll need to provide an email address and create a password. After verifying your email, you can set up your profile.', 'Account', 1, 1, NOW()),
            ('How do skill exchanges work?', 'Skill exchanges happen when two users agree to teach each other different skills. You can request a session with another user through their profile page. You\'ll agree on a time, location, and the skills you want to exchange.', 'Sessions', 1, 1, NOW()),
            ('Is SkillSwap completely free?', 'Yes, the core functionality of SkillSwap is completely free. We believe in building a community where knowledge is freely shared. We may introduce premium features in the future, but the basic skill exchange will always be free.', 'General', 2, 1, NOW()),
            ('How do I add skills to my profile?', 'To add skills to your profile, go to your profile page and click on the \"Edit Profile\" button. Scroll to the Skills section where you can add skills you offer and skills you want to learn.', 'Skills', 1, 1, NOW())");
    }

    public function down(Schema $schema): void
    {
        $this->addSql('DROP TABLE faq');
    }
} 