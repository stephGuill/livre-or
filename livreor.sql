-- Base de données: livreor
-- Structure des tables pour le livre d'or

-- ==================================================
-- Fichier SQL annoté : explications ligne-par-ligne
-- Ce fichier contient la structure minimale pour l'application
-- "Livre d'Or" : base, tables et contraintes.
-- Les commentaires ci-dessous expliquent chaque élément.
-- ==================================================

-- --------------------------------------------------
-- Création de la base de données
-- --------------------------------------------------
-- CREATE DATABASE IF NOT EXISTS `livreor` ...
--  - CREATE DATABASE : instruction SQL qui crée une base si elle n'existe pas.
--  - IF NOT EXISTS : évite une erreur si la base existe déjà.
--  - DEFAULT CHARACTER SET utf8mb4 : définit l'encodage par défaut (utf8mb4 gère les emoji).
--  - COLLATE utf8mb4_unicode_ci : définit la collation (comparaison/tri insensible à la casse).
CREATE DATABASE IF NOT EXISTS `livreor` DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

-- --------------------------------------------------
-- Basculer sur la base `livreor` pour exécuter les instructions suivantes
-- --------------------------------------------------
-- USE `livreor` : indique au serveur SQL d'utiliser la base nouvellement créée.
USE `livreor`;

-- --------------------------------------------------
-- Table : utilisateurs
-- --------------------------------------------------
-- Cette table stocke les comptes utilisateurs.
-- Colonnes :
--  - `id`       : identifiant numérique auto-incrémenté (clé primaire).
--  - `login`    : nom d'utilisateur (chaîne, unique pour empêcher doublons).
--  - `password` : mot de passe hashé (stockez des hashes, pas du texte clair).
-- Contraintes :
--  - PRIMARY KEY (`id`) : identifiant unique pour chaque ligne.
--  - UNIQUE KEY `login` (`login`) : assure que deux utilisateurs ne partagent pas le même login.
CREATE TABLE `utilisateurs` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `login` varchar(255) NOT NULL,
  `password` varchar(255) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `login` (`login`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------
-- Table : commentaires
-- --------------------------------------------------
-- Cette table contient les commentaires du livre d'or.
-- Colonnes :
--  - `id`           : identifiant du commentaire (clé primaire, auto-increment).
--  - `commentaire`  : texte du commentaire (type TEXT pour longueur variable).
--  - `id_utilisateur`: référence vers `utilisateurs.id` (clé étrangère).
--  - `date`         : date/heure d'insertion, valeur par défaut = CURRENT_TIMESTAMP.
-- Indexes et contraintes :
--  - KEY `id_utilisateur` (`id_utilisateur`) : index pour accélérer les jointures/filtrages par utilisateur.
--  - CONSTRAINT ... FOREIGN KEY (`id_utilisateur`) REFERENCES `utilisateurs` (`id`)
--    : garantit l'intégrité référentielle (un commentaire doit référencer un utilisateur existant).
CREATE TABLE `commentaires` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `commentaire` text NOT NULL,
  `id_utilisateur` int(11) NOT NULL,
  `date` datetime DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `id_utilisateur` (`id_utilisateur`),
  CONSTRAINT `commentaires_ibfk_1` FOREIGN KEY (`id_utilisateur`) REFERENCES `utilisateurs` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Notes pratiques / sécurité :
--  - Le champ `password` doit contenir des hashes générés par password_hash() (PHP) ou équivalent.
--  - Évitez d'insérer des données brutes sans les nettoyer côté application.
--  - Pensez aux actions ON DELETE/ON UPDATE selon votre logique métier (ex: cascade ou set null).
--    Ici, aucune action n'est configurée, donc la suppression d'un utilisateur provoquera
--    une erreur si des commentaires qui le réfèrent existent (contrainte FK). Vous pouvez
--    ajuster la contrainte en ajoutant ON DELETE CASCADE si vous voulez supprimer les commentaires
--    automatiquement lors de la suppression d'un utilisateur.

-- Exemple d'insertion (optionnel) :
-- INSERT INTO `utilisateurs` (`login`, `password`) VALUES ('admin', '<hash>');
-- Remplacer '<hash>' par un hash généré (ne pas stocker le mot de passe en clair).

-- Fin du fichier.