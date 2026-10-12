-- Active: 1770213586191@@127.0.0.1@8889@mangatheque_db
CREATE DATABASE IF NOT EXISTS `mangatheque_db` DEFAULT CHARACTER SET utf8 collate utf8_general_ci;

USE `mangatheque_db`;

CREATE TABLE `manga` (
    `id_manga` int(11) NOT NULL AUTO_INCREMENT PRIMARY KEY,
    `titre` VARCHAR(255) NOT NULL,
    `categorie` VARCHAR(255) NOT NULL,
    `theme` VARCHAR(255) NOT NULL,
    `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE = InnoDB DEFAULT CHARSET = utf8;

CREATE TABLE `genre` (
    `id_genre` INT(11) NOT NULL AUTO_INCREMENT PRIMARY KEY,
    `genres` VARCHAR(255) NOT NULL
) ENGINE = InnoDB;

INSERT INTO
    genre (genres)
VALUES ('Action'),
    ('Adventure'),
    ('Boys love'),
    ('Comedy'),
    ('Crime'),
    ('Drama'),
    ('Ecchi'),
    ('Fantasy'),
    ('Girls love'),
    ('Gore'),
    ('Gyaru'),
    ('Harem'),
    ('Historical'),
    ('Horror'),
    ('Martial arts'),
    ('Mystery'),
    ('Isekai'),
    ('Phylosophical'),
    ('Psychological'),
    ('Reincarnation'),
    ('Romance'),
    ('School life'),
    ('Sci-fi'),
    ('Slice of life'),
    ('Space'),
    ('Supernatural'),
    ('Survival'),
    ('Suspense'),
    ('Thriller'),
    ('Time travel'),
    ('Vampire'),
    ('Video game'),
    ('Workplace');

CREATE TABLE `association_manga_genre` (
    `id_association` int(11) NOT NULL auto_increment PRIMARY KEY,
    `id_manga` int(11) DEFAULT NULL,
    `id_genre` int(11) DEFAULT NULL,
    KEY `id_manga` (`id_manga`),
    KEY `id_genre` (`id_genre`)
) ENGINE = InnoDB;

SELECT *
FROM
    manga m
    INNER JOIN association_manga_genre amg 
    ON m.id_manga = amg.id_manga
    INNER JOIN genre g 
    ON g.id_genre = amg.id_genre;