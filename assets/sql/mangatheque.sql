-- Active: 1770213586191@@127.0.0.1@8889@mangatheque_db
CREATE DATABASE IF NOT EXISTS `mangatheque_db` DEFAULT CHARACTER SET utf8 collate utf8_general_ci;
USE `mangatheque_db`;
CREATE TABLE `manga` (
    `id` int(11) NOT NULL AUTO_INCREMENT PRIMARY KEY,
    `titre` VARCHAR(255) NOT NULL,
    `categorie` VARCHAR(255) NOT NULL,
    `genre_1` VARCHAR(255) NOT NULL,
    `genre_2` VARCHAR(255) NOT NULL,
    `genre_3` VARCHAR(255),
    `theme` VARCHAR(255) NOT NULL,
    `created_at`   TIMESTAMP     NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE = InnoDB DEFAULT CHARSET = utf8;

-- Rajouter une colonne
ALTER TABLE `manga`ADD `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP;

CREATE TABLE `status` (
    `id` int(11) NOT NULL AUTO_INCREMENT PRIMARY KEY,
    `status`VARCHAR(255)
);
INSERT INTO `status` (`status`)
VALUES ('En cours'), ('Terminer'), ('En pause'), ('À lire'), ('Abandonner');

CREATE TABLE `langage`