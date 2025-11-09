<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20251108051316 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE commande (id INT AUTO_INCREMENT NOT NULL, date_commande DATE NOT NULL COMMENT \'(DC2Type:date_immutable)\', date_prestation DATE NOT NULL COMMENT \'(DC2Type:date_immutable)\', heure_livraison TIME NOT NULL COMMENT \'(DC2Type:time_immutable)\', prix_menu NUMERIC(10, 2) NOT NULL, nombre_personne INT NOT NULL, prix_livraison NUMERIC(10, 2) NOT NULL, statut VARCHAR(255) NOT NULL, pret_materiel TINYINT(1) NOT NULL, restitution_materiel TINYINT(1) NOT NULL, numero_commande VARCHAR(255) NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('DROP TABLE commande');
    }
}
