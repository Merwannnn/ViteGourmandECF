/* ce fichier sql contient les données de création de base de données et de toute les tables */
/* il s'agit de commandes sql permettant de montrer la bonne compréhension du langage SQL mais il NE DOIT PAS être utilisé pour la création de la base de données et des tables */
/* pour un déploiement en local il faut créer une base de données manuellement et utilisé les fichier de migrations du projet pour la création des tables */
/* les commandes d'intégration de données ne sont que des exemple et NE DOIVENT PAS être utilisé pour insérer des donnés dans la base */
/* pour la plupart des table il faut directement passée par le site web(via les formulaires) pour pouvoir insérer des donnés car certain champs dépendent d'un bundle */

CREATE DATABASE vite_gourmand_ecf
    CHARSET utf8mb4
    COLLATE utf8mb4_unicode_ci;

/* permet de créer la table qui contient les avis des commande client */
CREATE TABLE avis (
  id INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
  commande_id INT NOT NULL,
  `user_id` INT NOT NULL,
  note INT NOT NULL,
  commentaire LONGTEXT NOT NULL,
  statut VARCHAR(255) NOT NULL,
  FOREIGN KEY (commande_id) REFERENCES commande (id),
  FOREIGN KEY (`user_id`) REFERENCES `user` (id)
);

/* permet de créer la table qui contient les commande client */
CREATE TABLE commande (
  id INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
  date_commande DATE NOT NULL,
  date_prestation DATE NOT NULL,
  heure_livraison TIME NOT NULL,
  prix_menu DECIMAL(10,2) NOT NULL,
  nombre_personne INT NOT NULL,
  prix_livraison DECIMAL(10,2) NOT NULL,
  statut VARCHAR(255) NOT NULL,
  pret_materiel TINYINT(1) NOT NULL,
  restitution_materiel TINYINT(1) NOT NULL,
  numero_commande VARCHAR(8) NOT NULL UNIQUE,
  menu_id INT NOT NULL,
  `user_id` INT NOT NULL,
  adresse_livraison VARCHAR(255) NOT NULL,
  FOREIGN KEY (`user_id`) REFERENCES `user` (id),
  FOREIGN KEY (menu_id) REFERENCES menu (id)
);

/* permet de créer la table qui contient l'historique des statut commande client */
CREATE TABLE commande_statut_historique (
  id INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
  commande_id INT NOT NULL,
  statut_suivant VARCHAR(255) NOT NULL,
  updated_at DATETIME NOT NULL,
  FOREIGN KEY (commande_id) REFERENCES commande (id)
);

/* permet de créer la table qui contient les horaires */
CREATE TABLE horaire (
  id INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
  jour VARCHAR(255) DEFAULT NULL,
  heure_ouverture TIME DEFAULT NULL,
  heure_fermeture TIME DEFAULT NULL,
  exception VARCHAR(255) DEFAULT NULL
);

/* permet de créer la table qui contient les menus */
CREATE TABLE menu (
  id INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
  title VARCHAR(255) NOT NULL,
  nb_personne_minimum INT NOT NULL,
  prix_personne DECIMAL(10,2) NOT NULL,
  regime VARCHAR(255) NOT NULL,
  quantite_restante INT NOT NULL,
  description LONGTEXT NOT NULL,
  theme_id INT DEFAULT NULL,
  conditions VARCHAR(255) DEFAULT NULL,
  FOREIGN KEY (theme_id) REFERENCES theme (id)
);

/* permet de créer une table intermédiaire pour la relation ManyToMany entre les table menu et plat */
CREATE TABLE menu_plat (
  menu_id INT NOT NULL PRIMARY KEY,
  plat_id INT NOT NULL PRIMARY KEY,
  FOREIGN KEY (menu_id) REFERENCES menu (id) ON DELETE CASCADE,
  FOREIGN KEY (plat_id) REFERENCES plat (id) ON DELETE CASCADE
);

/* permet de créer la table qui contient les plat que l'on met dans les menus */
CREATE TABLE plat (
  id INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
  plat_title VARCHAR(255) NOT NULL,
  allergene VARCHAR(255) NOT NULL,
  thumbnail VARCHAR(255) DEFAULT NULL
);

/* permet de créer la table qui contient les theme que l'on met dans les menu */
CREATE TABLE theme (
  id INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
  theme_title VARCHAR(255) NOT NULL
);

/* permet de créer la table qui contient les information utilisateur */
CREATE TABLE `user` (
  id INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
  email VARCHAR(180) NOT NULL UNIQUE,
  roles JSON NOT NULL,
  password VARCHAR(255) NOT NULL,
  name VARCHAR(255) NOT NULL,
  phone VARCHAR(255) NOT NULL,
  address VARCHAR(255) NOT NULL,
  is_verified TINYINT(1) NOT NULL
);


/* ces commandes d'intégration de données ne sont que des exemple pour montrer la compréhension du langage SQL */
/* et NE DOIVENT PAS être utilisé pour insérer des donnés dans la base car certain champs dépendent d'un bundle et nécessite de passer par le site web directement pour ajouter des données  */

INSERT INTO horaire (
  jour,
  heure_ouverture,
  heure_fermeture,
  exception
) VALUES 
(
  'Lundi',
  '11:30:00',
  '17:00:00',
  NULL
),
(
  'Mardi',
  '11:30:00',
  '17:00:00',
  NULL
),
(
  'Mercredi',
  '11:30:00',
  '16:30:00',
  NULL
),
(
  'Jeudi',
  '11:30:00',
  '17:00:00',
  NULL
),
(
  'Vendredi',
  '11:30:00',
  '17:00:00',
  NULL
),
(
  'Samedi',
  '11:30:00',
  '16:30:00',
  NULL
),
(
  'Dimanche',
  NULL,
  NULL,
  'Fermé'
);

INSERT INTO menu (
  title,
  nb_personne_minimum,
  prix_personne,
  regime,
  quantite_restante,
  description,
  theme_id,
  conditions
) VALUES
(
  'Menu Classique',
  2,
  19.99,
  'Classique',
  22,
  'Notre Menu Classique propose une sélection de recettes gourmandes et accessibles, idéale pour vos repas en famille ou vos déjeuners professionnels. Découvrez en entrée une salade César au poulet fraîche et généreuse, poursuivez avec un cheeseburger accompagné de frites pour un plat convivial et savoureux, puis terminez votre repas avec une crème au chocolat onctueuse et gourmande. Un menu pensé pour satisfaire tous les palais grâce à des grands classiques appréciés de tous.',
  1,
  'À consommer sous 48h après livraison.'
);