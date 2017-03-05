-- phpMyAdmin SQL Dump
-- version 4.6.5.2
-- https://www.phpmyadmin.net/
--
-- Počítač: 127.0.0.1
-- Vytvořeno: Ned 05. bře 2017, 18:39
-- Verze serveru: 10.1.21-MariaDB
-- Verze PHP: 5.6.30

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
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
-- Struktura tabulky `practice`
--

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
  `valid_reminder` int(1) NOT NULL DEFAULT '0',
  `monthly_reminder` varchar(25) DEFAULT NULL,
  `changes_reminder` varchar(25) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 ROW_FORMAT=COMPACT;

--
-- Klíče pro exportované tabulky
--

--
-- Klíče pro tabulku `practice`
--
ALTER TABLE `practice`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `code` (`code`),
  ADD UNIQUE KEY `name` (`company`);

--
-- AUTO_INCREMENT pro tabulky
--

--
-- AUTO_INCREMENT pro tabulku `practice`
--
ALTER TABLE `practice`
  MODIFY `id` int(9) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
