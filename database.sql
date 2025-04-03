-- Script de création de la base de données stock_management
-- Ce script crée toutes les tables nécessaires pour l'application de gestion de stock

-- Création de la base de données
CREATE DATABASE IF NOT EXISTS `stock_management` DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE `stock_management`;

-- Table des utilisateurs
CREATE TABLE IF NOT EXISTS `utilisateurs` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `nom` varchar(100) NOT NULL,
  `prenom` varchar(100) NOT NULL,
  `email` varchar(100) NOT NULL,
  `mot_de_passe` varchar(255) NOT NULL,
  `role` enum('admin','magasinier','secretaire') NOT NULL DEFAULT 'secretaire',
  `statut` enum('actif','inactif') NOT NULL DEFAULT 'actif',
  `date_creation` datetime NOT NULL,
  `date_modification` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `email` (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Table des catégories
CREATE TABLE IF NOT EXISTS `categories` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `nom` varchar(100) NOT NULL,
  `description` text DEFAULT NULL,
  `statut` enum('actif','inactif') NOT NULL DEFAULT 'actif',
  `date_creation` datetime NOT NULL,
  `date_modification` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `nom` (`nom`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Table des fournisseurs
CREATE TABLE IF NOT EXISTS `fournisseurs` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `nom` varchar(100) NOT NULL,
  `adresse` text DEFAULT NULL,
  `telephone` varchar(20) DEFAULT NULL,
  `email` varchar(100) DEFAULT NULL,
  `statut` enum('actif','inactif') NOT NULL DEFAULT 'actif',
  `date_creation` datetime NOT NULL,
  `date_modification` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `nom` (`nom`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Table des clients
CREATE TABLE IF NOT EXISTS `clients` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `nom` varchar(100) NOT NULL,
  `prenom` varchar(100) NOT NULL,
  `email` varchar(100) DEFAULT NULL,
  `telephone` varchar(20) DEFAULT NULL,
  `adresse` text DEFAULT NULL,
  `ville` varchar(100) DEFAULT NULL,
  `code_postal` varchar(20) DEFAULT NULL,
  `pays` varchar(100) DEFAULT NULL,
  `statut` enum('actif','inactif') NOT NULL DEFAULT 'actif',
  `date_creation` datetime NOT NULL,
  `date_modification` datetime DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Table des produits
CREATE TABLE IF NOT EXISTS `produits` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `reference` varchar(50) NOT NULL,
  `designation` varchar(255) NOT NULL,
  `description` text DEFAULT NULL,
  `id_categorie` int(11) DEFAULT NULL,
  `id_fournisseur` int(11) DEFAULT NULL,
  `prix_achat` decimal(10,2) DEFAULT NULL,
  `prix_vente` decimal(10,2) NOT NULL,
  `quantite_stock` int(11) NOT NULL DEFAULT 0,
  `quantite_alerte` int(11) DEFAULT 5,
  `unite` varchar(20) DEFAULT NULL,
  `image` varchar(255) DEFAULT NULL,
  `statut` enum('actif','inactif') NOT NULL DEFAULT 'actif',
  `date_creation` datetime NOT NULL,
  `date_modification` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `reference` (`reference`),
  KEY `id_categorie` (`id_categorie`),
  KEY `id_fournisseur` (`id_fournisseur`),
  CONSTRAINT `produits_ibfk_1` FOREIGN KEY (`id_categorie`) REFERENCES `categories` (`id`) ON DELETE SET NULL,
  CONSTRAINT `produits_ibfk_2` FOREIGN KEY (`id_fournisseur`) REFERENCES `fournisseurs` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Table des commandes
CREATE TABLE IF NOT EXISTS `commandes` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `reference` varchar(50) NOT NULL,
  `id_client` int(11) NOT NULL,
  `id_utilisateur` int(11) NOT NULL,
  `montant_total` decimal(10,2) NOT NULL,
  `statut` enum('pending','approved','rejected','delivered','cancelled') NOT NULL DEFAULT 'pending',
  `statut_paiement` enum('pending','partial','paid') NOT NULL DEFAULT 'pending',
  `date_livraison_prevue` date DEFAULT NULL,
  `notes` text DEFAULT NULL,
  `date_creation` datetime NOT NULL,
  `date_modification` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `reference` (`reference`),
  KEY `id_client` (`id_client`),
  KEY `id_utilisateur` (`id_utilisateur`),
  CONSTRAINT `commandes_ibfk_1` FOREIGN KEY (`id_client`) REFERENCES `clients` (`id`),
  CONSTRAINT `commandes_ibfk_2` FOREIGN KEY (`id_utilisateur`) REFERENCES `utilisateurs` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Table des détails de commande
CREATE TABLE IF NOT EXISTS `details_commande` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `id_commande` int(11) NOT NULL,
  `id_produit` int(11) NOT NULL,
  `quantite` int(11) NOT NULL,
  `prix_unitaire` decimal(10,2) NOT NULL,
  `montant_total` decimal(10,2) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `id_commande` (`id_commande`),
  KEY `id_produit` (`id_produit`),
  CONSTRAINT `details_commande_ibfk_1` FOREIGN KEY (`id_commande`) REFERENCES `commandes` (`id`) ON DELETE CASCADE,
  CONSTRAINT `details_commande_ibfk_2` FOREIGN KEY (`id_produit`) REFERENCES `produits` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Table des paiements
CREATE TABLE IF NOT EXISTS `paiements` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `id_commande` int(11) NOT NULL,
  `montant` decimal(10,2) NOT NULL,
  `mode_paiement` enum('especes','cheque','virement','carte') NOT NULL,
  `reference_transaction` varchar(100) DEFAULT NULL,
  `date_paiement` datetime NOT NULL,
  `notes` text DEFAULT NULL,
  `id_utilisateur` int(11) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `id_commande` (`id_commande`),
  KEY `id_utilisateur` (`id_utilisateur`),
  CONSTRAINT `paiements_ibfk_1` FOREIGN KEY (`id_commande`) REFERENCES `commandes` (`id`) ON DELETE CASCADE,
  CONSTRAINT `paiements_ibfk_2` FOREIGN KEY (`id_utilisateur`) REFERENCES `utilisateurs` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Table des opérations de stock
CREATE TABLE IF NOT EXISTS `operations_stock` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `id_produit` int(11) NOT NULL,
  `type_operation` enum('entry','exit') NOT NULL,
  `quantite` int(11) NOT NULL,
  `motif` varchar(255) DEFAULT NULL,
  `id_commande` int(11) DEFAULT NULL,
  `id_utilisateur` int(11) NOT NULL,
  `date_operation` datetime NOT NULL,
  PRIMARY KEY (`id`),
  KEY `id_produit` (`id_produit`),
  KEY `id_commande` (`id_commande`),
  KEY `id_utilisateur` (`id_utilisateur`),
  CONSTRAINT `operations_stock_ibfk_1` FOREIGN KEY (`id_produit`) REFERENCES `produits` (`id`),
  CONSTRAINT `operations_stock_ibfk_2` FOREIGN KEY (`id_commande`) REFERENCES `commandes` (`id`) ON DELETE SET NULL,
  CONSTRAINT `operations_stock_ibfk_3` FOREIGN KEY (`id_utilisateur`) REFERENCES `utilisateurs` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Table des logs d'activité
CREATE TABLE IF NOT EXISTS `logs_activite` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `id_utilisateur` int(11) DEFAULT NULL,
  `action` varchar(255) NOT NULL,
  `entite` varchar(50) NOT NULL,
  `id_entite` int(11) DEFAULT NULL,
  `details` text DEFAULT NULL,
  `adresse_ip` varchar(45) DEFAULT NULL,
  `date_creation` datetime NOT NULL,
  PRIMARY KEY (`id`),
  KEY `id_utilisateur` (`id_utilisateur`),
  CONSTRAINT `logs_activite_ibfk_1` FOREIGN KEY (`id_utilisateur`) REFERENCES `utilisateurs` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;


-- Table des entrées de stock
CREATE TABLE IF NOT EXISTS `entrees_stock` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `reference` varchar(50) NOT NULL,
  `date_entree` datetime NOT NULL,
  `id_fournisseur` int(11) DEFAULT NULL,
  `id_utilisateur` int(11) NOT NULL,
  `montant_total` decimal(10,2) NOT NULL DEFAULT 0.00,
  `notes` text DEFAULT NULL,
  `statut` enum('en_attente','validee','annulee') NOT NULL DEFAULT 'en_attente',
  `date_creation` datetime NOT NULL,
  `date_modification` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `reference` (`reference`),
  KEY `id_fournisseur` (`id_fournisseur`),
  KEY `id_utilisateur` (`id_utilisateur`),
  CONSTRAINT `entrees_stock_ibfk_1` FOREIGN KEY (`id_fournisseur`) REFERENCES `fournisseurs` (`id`) ON DELETE SET NULL,
  CONSTRAINT `entrees_stock_ibfk_2` FOREIGN KEY (`id_utilisateur`) REFERENCES `utilisateurs` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Table des détails d'entrées de stock
CREATE TABLE IF NOT EXISTS `details_entree_stock` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `id_entree` int(11) NOT NULL,
  `id_produit` int(11) NOT NULL,
  `quantite` int(11) NOT NULL,
  `prix_unitaire` decimal(10,2) NOT NULL,
  `montant_total` decimal(10,2) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `id_entree` (`id_entree`),
  KEY `id_produit` (`id_produit`),
  CONSTRAINT `details_entree_stock_ibfk_1` FOREIGN KEY (`id_entree`) REFERENCES `entrees_stock` (`id`) ON DELETE CASCADE,
  CONSTRAINT `details_entree_stock_ibfk_2` FOREIGN KEY (`id_produit`) REFERENCES `produits` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Table des sorties de stock
CREATE TABLE IF NOT EXISTS `sorties_stock` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `reference` varchar(50) NOT NULL,
  `date_sortie` datetime NOT NULL,
  `id_utilisateur` int(11) NOT NULL,
  `type_sortie` enum('vente','perte','transfert','autre') NOT NULL DEFAULT 'vente',
  `id_commande` int(11) DEFAULT NULL,
  `destination` varchar(255) DEFAULT NULL,
  `montant_total` decimal(10,2) NOT NULL DEFAULT 0.00,
  `notes` text DEFAULT NULL,
  `statut` enum('en_attente','validee','annulee') NOT NULL DEFAULT 'en_attente',
  `date_creation` datetime NOT NULL,
  `date_modification` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `reference` (`reference`),
  KEY `id_utilisateur` (`id_utilisateur`),
  KEY `id_commande` (`id_commande`),
  CONSTRAINT `sorties_stock_ibfk_1` FOREIGN KEY (`id_utilisateur`) REFERENCES `utilisateurs` (`id`),
  CONSTRAINT `sorties_stock_ibfk_2` FOREIGN KEY (`id_commande`) REFERENCES `commandes` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Table des détails de sorties de stock
CREATE TABLE IF NOT EXISTS `details_sortie_stock` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `id_sortie` int(11) NOT NULL,
  `id_produit` int(11) NOT NULL,
  `quantite` int(11) NOT NULL,
  `prix_unitaire` decimal(10,2) NOT NULL,
  `montant_total` decimal(10,2) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `id_sortie` (`id_sortie`),
  KEY `id_produit` (`id_produit`),
  CONSTRAINT `details_sortie_stock_ibfk_1` FOREIGN KEY (`id_sortie`) REFERENCES `sorties_stock` (`id`) ON DELETE CASCADE,
  CONSTRAINT `details_sortie_stock_ibfk_2` FOREIGN KEY (`id_produit`) REFERENCES `produits` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS `livraisons` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `reference` varchar(50) NOT NULL,
  `date_livraison` datetime NOT NULL,
  `id_utilisateur` int(11) NOT NULL,
  `destination` varchar(255) DEFAULT NULL,
  `notes` text DEFAULT NULL,
  `statut` enum('en_attente','validee','annulee') NOT NULL DEFAULT 'en_attente',
  `date_creation` datetime NOT NULL,
  `date_modification` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `reference` (`reference`),
  KEY `id_utilisateur` (`id_utilisateur`),
  CONSTRAINT `livraisons_ibfk_1` FOREIGN KEY (`id_utilisateur`) REFERENCES `utilisateurs` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

ALTER TABLE `sorties_stock`
ADD COLUMN `id_livraison` int(11) DEFAULT NULL,
ADD KEY `id_livraison` (`id_livraison`),
ADD CONSTRAINT `sorties_stock_ibfk_3` FOREIGN KEY (`id_livraison`) REFERENCES `livraisons` (`id`) ON DELETE SET NULL;


ALTER TABLE `entrees_stock`
ADD COLUMN `id_livraison` int(11) DEFAULT NULL,
ADD COLUMN `id_client` int(11) DEFAULT NULL,
ADD KEY `id_livraison` (`id_livraison`),
ADD KEY `id_client` (`id_client`),
ADD CONSTRAINT `entrees_stock_ibfk_3` FOREIGN KEY (`id_livraison`) REFERENCES `livraisons` (`id`) ON DELETE SET NULL,
ADD CONSTRAINT `sorties_stock_ibfk_4` FOREIGN KEY (`id_client`) REFERENCES `clients` (`id`) ON DELETE SET NULL;

ALTER TABLE users ADD COLUMN derniere_connexion DATETIME NULL;
ALTER TABLE utilisateurs
ADD COLUMN remember_token VARCHAR(255) NULL,
ADD COLUMN remember_token_expiry DATETIME NULL;

ALTER TABLE `sorties_stock`
MODIFY COLUMN `id_livraison` int(11) DEFAULT NULL;
ALTER TABLE `sorties_stock`
MODIFY COLUMN `id_commande` int(11) DEFAULT NULL,
ALTER TABLE `utilisateurs`
ADD COLUMN `username` VARCHAR(255) DEFAULT NULL;

-- Insertion d'un utilisateur administrateur par défaut
-- Mot de passe: admin123 (à changer après la première connexion)
-- INSERT INTO `utilisateurs` (`nom`, `prenom`, `email`, `mot_de_passe`, `role`, `statut`, `date_creation`) 
-- VALUES ('Admin', 'System', 'admin@example.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'admin', 'actif', NOW());
-- VALUES ('Admin', 'System', 'admin@example.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'admin', 'actif', NOW());

INSERT INTO `utilisateurs` (`nom`, `prenom`, `email`, `mot_de_passe`, `role`, `statut`, `date_creation`) 
VALUES 
('Admin', 'System', 'admin@gmail.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'admin', 'actif', NOW()),
('John', 'Doe', 'storekeeper.@gmail.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'magasinier', 'actif', NOW()),
('Jane', 'Smith', 'secretary@gmail.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'secretaire', 'actif', NOW());
-- Insertion de quelques catégories de base
INSERT INTO `categories` (`nom`, `description`, `statut`, `date_creation`) VALUES
('Électronique', 'Produits électroniques et accessoires', 'actif', NOW()),
('Informatique', 'Matériel informatique et périphériques', 'actif', NOW()),
('Outillage', 'Outils et équipements', 'actif', NOW()),
('Fournitures', 'Fournitures diverses', 'actif', NOW());

-- Insertion de quelques fournisseurs de base
INSERT INTO `fournisseurs` (`nom`, `adresse`, `telephone`, `email`, `statut`, `date_creation`) VALUES
('Fournisseur A', '123 Rue Principale, Ville A', '+212600000001', 'contact@fournisseura.com', 'actif', NOW()),
('Fournisseur B', '456 Avenue Centrale, Ville B', '+212600000002', 'contact@fournisseurb.com', 'actif', NOW()),
('Fournisseur C', '789 Boulevard Principal, Ville C', '+212600000003', 'contact@fournisseurc.com', 'actif', NOW());
