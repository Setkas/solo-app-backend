-- phpMyAdmin SQL Dump
-- version 4.6.5.2
-- https://www.phpmyadmin.net/
--
-- Počítač: 127.0.0.1
-- Vytvořeno: Ned 19. bře 2017, 01:49
-- Verze serveru: 10.1.21-MariaDB
-- Verze PHP: 5.6.30

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET AUTOCOMMIT = 0;
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Databáze: `soloapp`
--

-- --------------------------------------------------------

--
-- Struktura tabulky `client`
--

DROP TABLE IF EXISTS `client`;
CREATE TABLE `client` (
  `id` int(9) NOT NULL,
  `practice_id` int(9) NOT NULL,
  `keywords` varchar(50) NOT NULL,
  `e_name` blob NOT NULL,
  `e_surname` blob NOT NULL,
  `e_address` blob NOT NULL,
  `e_phone` blob NOT NULL,
  `birth_date` varchar(25) NOT NULL,
  `email` varchar(150) DEFAULT NULL,
  `gender` int(1) NOT NULL,
  `password` varchar(32) DEFAULT NULL,
  `changes_reminder` varchar(25) DEFAULT NULL,
  `deleted` tinyint(1) NOT NULL DEFAULT '0'
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- RELACE PRO TABULKU `client`:
--   `practice_id`
--       `practice` -> `id`
--

-- --------------------------------------------------------

--
-- Struktura tabulky `language`
--

DROP TABLE IF EXISTS `language`;
CREATE TABLE `language` (
  `id` int(9) NOT NULL,
  `rating` int(3) NOT NULL DEFAULT '0',
  `code` varchar(2) NOT NULL,
  `string` varchar(50) NOT NULL,
  `locales` text COMMENT 'JSON array with list of locale identifiers'
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- RELACE PRO TABULKU `language`:
--

--
-- Vypisuji data pro tabulku `language`
--

INSERT INTO `language` (`id`, `rating`, `code`, `string`, `locales`) VALUES
(1, 30, 'en', 'ENGLISH', '[\"en\",\"en-au\",\"en-bz\",\"en-ca\",\"en-ie\",\"en-jm\",\"en-nz\",\"en-ph\",\"en-za\",\"en-tt\",\"en-gb\",\"en-us\",\"en-zw\"]'),
(2, 10, 'cs', 'CZECH', '[\"cs\"]'),
(3, 20, 'de', 'GERMAN', '[\"de\",\"de-at\",\"de-de\",\"de-li\",\"de-lu\",\"de-ch\"]'),
(4, 40, 'sk', 'SLOVAKIAN', '[\"sk\"]');

-- --------------------------------------------------------

--
-- Struktura tabulky `position`
--

DROP TABLE IF EXISTS `position`;
CREATE TABLE `position` (
  `id` int(9) NOT NULL,
  `rating` int(3) NOT NULL DEFAULT '0',
  `position` varchar(50) NOT NULL,
  `language_ids` text NOT NULL COMMENT 'JSON array of language ids'
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- RELACE PRO TABULKU `position`:
--

--
-- Vypisuji data pro tabulku `position`
--

INSERT INTO `position` (`id`, `rating`, `position`, `language_ids`) VALUES
(1, 10, 'DENTIST', '[1,2,3,4]'),
(2, 20, 'HYGIENIST', '[1,2,3,4]'),
(3, 30, 'ZMF', '[3]'),
(4, 40, 'ZMP', '[3]'),
(5, 50, 'ZFA', '[3]'),
(6, 90, 'OTHER', '[1,2,3,4]');

-- --------------------------------------------------------

--
-- Struktura tabulky `practice`
--

DROP TABLE IF EXISTS `practice`;
CREATE TABLE `practice` (
  `id` int(9) NOT NULL,
  `language_id` int(9) NOT NULL,
  `code` varchar(7) NOT NULL COMMENT 'Login code for practice',
  `company` varchar(50) NOT NULL,
  `e_address` blob NOT NULL,
  `e_phone` blob NOT NULL,
  `e_contact_email` blob NOT NULL,
  `e_webpages` blob NOT NULL,
  `e_system_email` blob,
  `valid` varchar(25) NOT NULL,
  `valid_reminder` tinyint(1) NOT NULL DEFAULT '0',
  `monthly_reminder` varchar(25) DEFAULT NULL,
  `changes_reminder` varchar(25) DEFAULT NULL,
  `deleted` tinyint(1) NOT NULL DEFAULT '0'
) ENGINE=InnoDB DEFAULT CHARSET=utf8 ROW_FORMAT=COMPACT;

--
-- RELACE PRO TABULKU `practice`:
--   `language_id`
--       `language` -> `id`
--

-- --------------------------------------------------------

--
-- Struktura tabulky `setup`
--

DROP TABLE IF EXISTS `setup`;
CREATE TABLE `setup` (
  `id` int(9) NOT NULL,
  `user_id` int(9) NOT NULL,
  `name` varchar(50) NOT NULL,
  `value` text NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- RELACE PRO TABULKU `setup`:
--   `user_id`
--       `user` -> `id`
--

-- --------------------------------------------------------

--
-- Struktura tabulky `term`
--

DROP TABLE IF EXISTS `term`;
CREATE TABLE `term` (
  `id` int(9) NOT NULL,
  `client_id` int(9) NOT NULL,
  `user_id` int(9) NOT NULL,
  `date` varchar(25) NOT NULL,
  `next_date` varchar(25) NOT NULL,
  `note` text NOT NULL,
  `teeth_upper` varchar(16) NOT NULL,
  `teeth_lower` varchar(16) NOT NULL,
  `bleed_upper_inner` varchar(16) NOT NULL,
  `bleed_upper_outer` varchar(16) NOT NULL,
  `bleed_upper_middle` varchar(15) NOT NULL,
  `bleed_lower_inner` varchar(16) NOT NULL,
  `bleed_lower_outer` varchar(16) NOT NULL,
  `bleed_lower_middle` varchar(15) NOT NULL,
  `stix_upper` varchar(15) NOT NULL,
  `stix_lower` varchar(15) NOT NULL,
  `pass_upper` varchar(15) NOT NULL,
  `pass_lower` varchar(15) NOT NULL,
  `tartar` varchar(2) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 ROW_FORMAT=COMPACT;

--
-- RELACE PRO TABULKU `term`:
--   `user_id`
--       `user` -> `id`
--   `client_id`
--       `client` -> `id`
--

-- --------------------------------------------------------

--
-- Struktura tabulky `user`
--

DROP TABLE IF EXISTS `user`;
CREATE TABLE `user` (
  `id` int(9) NOT NULL,
  `practice_id` int(9) NOT NULL,
  `position_id` int(9) NOT NULL,
  `code` int(5) NOT NULL,
  `password` varchar(32) NOT NULL COMMENT 'Password in md5 hash',
  `e_title` blob NOT NULL,
  `e_name` blob NOT NULL,
  `e_surname` blob NOT NULL,
  `gender` int(1) NOT NULL,
  `authorization` varchar(10) NOT NULL,
  `reset_password` tinyint(1) NOT NULL DEFAULT '0',
  `deleted` tinyint(1) NOT NULL DEFAULT '0'
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- RELACE PRO TABULKU `user`:
--   `practice_id`
--       `practice` -> `id`
--   `position_id`
--       `position` -> `id`
--

--
-- Klíče pro exportované tabulky
--

--
-- Klíče pro tabulku `client`
--
ALTER TABLE `client`
  ADD PRIMARY KEY (`id`),
  ADD KEY `practice_id` (`practice_id`),
  ADD KEY `keywords` (`keywords`);

--
-- Klíče pro tabulku `language`
--
ALTER TABLE `language`
  ADD PRIMARY KEY (`id`);

--
-- Klíče pro tabulku `position`
--
ALTER TABLE `position`
  ADD PRIMARY KEY (`id`);

--
-- Klíče pro tabulku `practice`
--
ALTER TABLE `practice`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `code` (`code`),
  ADD UNIQUE KEY `name` (`company`),
  ADD KEY `language_id` (`language_id`);

--
-- Klíče pro tabulku `setup`
--
ALTER TABLE `setup`
  ADD PRIMARY KEY (`id`),
  ADD KEY `practice_id` (`user_id`);

--
-- Klíče pro tabulku `term`
--
ALTER TABLE `term`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `client_id` (`client_id`);

--
-- Klíče pro tabulku `user`
--
ALTER TABLE `user`
  ADD PRIMARY KEY (`id`),
  ADD KEY `practice_id` (`practice_id`),
  ADD KEY `position_id` (`position_id`);

--
-- AUTO_INCREMENT pro tabulky
--

--
-- AUTO_INCREMENT pro tabulku `client`
--
ALTER TABLE `client`
  MODIFY `id` int(9) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;
--
-- AUTO_INCREMENT pro tabulku `language`
--
ALTER TABLE `language`
  MODIFY `id` int(9) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;
--
-- AUTO_INCREMENT pro tabulku `position`
--
ALTER TABLE `position`
  MODIFY `id` int(9) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;
--
-- AUTO_INCREMENT pro tabulku `practice`
--
ALTER TABLE `practice`
  MODIFY `id` int(9) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=21;
--
-- AUTO_INCREMENT pro tabulku `setup`
--
ALTER TABLE `setup`
  MODIFY `id` int(9) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;
--
-- AUTO_INCREMENT pro tabulku `term`
--
ALTER TABLE `term`
  MODIFY `id` int(9) NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT pro tabulku `user`
--
ALTER TABLE `user`
  MODIFY `id` int(9) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=21;
--
-- Omezení pro exportované tabulky
--

--
-- Omezení pro tabulku `client`
--
ALTER TABLE `client`
  ADD CONSTRAINT `client_ibfk_1` FOREIGN KEY (`practice_id`) REFERENCES `practice` (`id`);

--
-- Omezení pro tabulku `practice`
--
ALTER TABLE `practice`
  ADD CONSTRAINT `practice_ibfk_1` FOREIGN KEY (`language_id`) REFERENCES `language` (`id`);

--
-- Omezení pro tabulku `setup`
--
ALTER TABLE `setup`
  ADD CONSTRAINT `setup_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `user` (`id`);

--
-- Omezení pro tabulku `term`
--
ALTER TABLE `term`
  ADD CONSTRAINT `term_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `user` (`id`),
  ADD CONSTRAINT `term_ibfk_2` FOREIGN KEY (`client_id`) REFERENCES `client` (`id`);

--
-- Omezení pro tabulku `user`
--
ALTER TABLE `user`
  ADD CONSTRAINT `user_ibfk_1` FOREIGN KEY (`practice_id`) REFERENCES `practice` (`id`),
  ADD CONSTRAINT `user_ibfk_2` FOREIGN KEY (`position_id`) REFERENCES `position` (`id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
