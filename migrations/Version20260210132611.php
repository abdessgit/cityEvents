<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260210132611 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE categorie (id INT AUTO_INCREMENT NOT NULL, nom_categorie VARCHAR(40) NOT NULL, PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE evenements (id INT AUTO_INCREMENT NOT NULL, nom_evenement VARCHAR(255) NOT NULL, description_event LONGTEXT NOT NULL, date_creation DATETIME NOT NULL, date_debut DATETIME NOT NULL, date_fin DATETIME NOT NULL, adresse VARCHAR(255) NOT NULL, nbre_place VARCHAR(7) NOT NULL, price_place NUMERIC(5, 2) NOT NULL, categorie_id INT NOT NULL, ville_id INT NOT NULL, utilisateur_id INT NOT NULL, INDEX IDX_E10AD400BCF5E72D (categorie_id), INDEX IDX_E10AD400A73F0036 (ville_id), INDEX IDX_E10AD400FB88E14F (utilisateur_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE images (id INT AUTO_INCREMENT NOT NULL, nom_images VARCHAR(255) NOT NULL, evenements_id INT NOT NULL, INDEX IDX_E01FBE6A63C02CD4 (evenements_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE roles (id INT AUTO_INCREMENT NOT NULL, nom_role VARCHAR(50) NOT NULL, PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE ticket (id INT AUTO_INCREMENT NOT NULL, object_ticket VARCHAR(150) NOT NULL, message LONGTEXT NOT NULL, utilisateur_id INT NOT NULL, INDEX IDX_97A0ADA3FB88E14F (utilisateur_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE utilisateur (id INT AUTO_INCREMENT NOT NULL, nom_utilisateur VARCHAR(120) NOT NULL, prenom_utilisateur VARCHAR(40) NOT NULL, date_inscription DATETIME NOT NULL, email_utilisateur VARCHAR(120) NOT NULL, tel_utilisateur VARCHAR(20) NOT NULL, ip_utilisateur VARCHAR(50) NOT NULL, mdp_utilisateur VARCHAR(255) NOT NULL, roles_id INT NOT NULL, INDEX IDX_1D1C63B338C751C4 (roles_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE ville (id INT AUTO_INCREMENT NOT NULL, nom_ville VARCHAR(50) NOT NULL, code_postal VARCHAR(6) NOT NULL, code_insee VARCHAR(6) NOT NULL, PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE messenger_messages (id BIGINT AUTO_INCREMENT NOT NULL, body LONGTEXT NOT NULL, headers LONGTEXT NOT NULL, queue_name VARCHAR(190) NOT NULL, created_at DATETIME NOT NULL, available_at DATETIME NOT NULL, delivered_at DATETIME DEFAULT NULL, INDEX IDX_75EA56E0FB7336F0E3BD61CE16BA31DBBF396750 (queue_name, available_at, delivered_at, id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('ALTER TABLE evenements ADD CONSTRAINT FK_E10AD400BCF5E72D FOREIGN KEY (categorie_id) REFERENCES categorie (id)');
        $this->addSql('ALTER TABLE evenements ADD CONSTRAINT FK_E10AD400A73F0036 FOREIGN KEY (ville_id) REFERENCES ville (id)');
        $this->addSql('ALTER TABLE evenements ADD CONSTRAINT FK_E10AD400FB88E14F FOREIGN KEY (utilisateur_id) REFERENCES utilisateur (id)');
        $this->addSql('ALTER TABLE images ADD CONSTRAINT FK_E01FBE6A63C02CD4 FOREIGN KEY (evenements_id) REFERENCES evenements (id)');
        $this->addSql('ALTER TABLE ticket ADD CONSTRAINT FK_97A0ADA3FB88E14F FOREIGN KEY (utilisateur_id) REFERENCES utilisateur (id)');
        $this->addSql('ALTER TABLE utilisateur ADD CONSTRAINT FK_1D1C63B338C751C4 FOREIGN KEY (roles_id) REFERENCES roles (id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE evenements DROP FOREIGN KEY FK_E10AD400BCF5E72D');
        $this->addSql('ALTER TABLE evenements DROP FOREIGN KEY FK_E10AD400A73F0036');
        $this->addSql('ALTER TABLE evenements DROP FOREIGN KEY FK_E10AD400FB88E14F');
        $this->addSql('ALTER TABLE images DROP FOREIGN KEY FK_E01FBE6A63C02CD4');
        $this->addSql('ALTER TABLE ticket DROP FOREIGN KEY FK_97A0ADA3FB88E14F');
        $this->addSql('ALTER TABLE utilisateur DROP FOREIGN KEY FK_1D1C63B338C751C4');
        $this->addSql('DROP TABLE categorie');
        $this->addSql('DROP TABLE evenements');
        $this->addSql('DROP TABLE images');
        $this->addSql('DROP TABLE roles');
        $this->addSql('DROP TABLE ticket');
        $this->addSql('DROP TABLE utilisateur');
        $this->addSql('DROP TABLE ville');
        $this->addSql('DROP TABLE messenger_messages');
    }
}
