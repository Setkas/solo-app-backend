-- phpMyAdmin SQL Dump
-- version 4.5.1
-- http://www.phpmyadmin.net
--
-- Host: 127.0.0.1
-- Generation Time: Mar 07, 2017 at 02:55 PM
-- Server version: 10.1.13-MariaDB
-- PHP Version: 5.6.20

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `soloapp`
--

-- --------------------------------------------------------

--
-- Table structure for table `client`
--

CREATE TABLE `client` (
  `id` int(9) NOT NULL,
  `practice_id` int(9) NOT NULL,
  `keywords` varchar(50) NOT NULL,
  `username` varchar(10) DEFAULT NULL,
  `password` varchar(32) DEFAULT NULL,
  `e_name` blob NOT NULL,
  `e_surname` blob NOT NULL,
  `e_street` blob NOT NULL,
  `e_number` blob NOT NULL,
  `e_zip` blob NOT NULL,
  `e_state` blob NOT NULL,
  `e_phone` blob NOT NULL,
  `e_email` blob NOT NULL,
  `gender` int(1) NOT NULL,
  `birth_date` varchar(25) NOT NULL,
  `changes_reminder` varchar(25) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `language`
--

CREATE TABLE `language` (
  `id` int(9) NOT NULL,
  `rating` int(3) NOT NULL,
  `code` varchar(2) NOT NULL,
  `string` varchar(50) NOT NULL,
  `locales` text COMMENT 'JSON array with list of locale identifiers'
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Dumping data for table `language`
--

INSERT INTO `language` (`id`, `rating`, `code`, `string`, `locales`) VALUES
(1, 30, 'en', 'ENGLISH', '["en","en-au","en-bz","en-ca","en-ie","en-jm","en-nz","en-ph","en-za","en-tt","en-gb","en-us","en-zw"]'),
(2, 10, 'cs', 'CZECH', '["cs"]'),
(3, 20, 'de', 'GERMAN', '["de","de-at","de-de","de-li","de-lu","de-ch"]'),
(4, 40, 'sk', 'SLOVAKIAN', '["sk"]');

-- --------------------------------------------------------

--
-- Table structure for table `mail`
--

CREATE TABLE `mail` (
  `id` int(9) NOT NULL,
  `language_id` int(9) NOT NULL,
  `code` varchar(50) NOT NULL,
  `subject` text NOT NULL,
  `sender` varchar(100) NOT NULL,
  `template` text NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `position`
--

CREATE TABLE `position` (
  `id` int(9) NOT NULL,
  `position` varchar(50) NOT NULL,
  `language_ids` text NOT NULL COMMENT 'JSON array of language ids'
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Dumping data for table `position`
--

INSERT INTO `position` (`id`, `position`, `language_ids`) VALUES
(1, 'DENTIST', '[1,2,3,4]'),
(2, 'HYGIENIST', '[1,2,3,4]'),
(3, 'ZMF', '[3]'),
(4, 'ZMP', '[3]'),
(5, 'ZFA', '[3]'),
(6, 'OTHER', '[1,2,3,4]');

-- --------------------------------------------------------

--
-- Table structure for table `practice`
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

-- --------------------------------------------------------

--
-- Table structure for table `setup`
--

CREATE TABLE `setup` (
  `id` int(9) NOT NULL,
  `practice_id` int(9) NOT NULL,
  `name` varchar(50) NOT NULL,
  `value` int(50) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `term`
--

CREATE TABLE `term` (
  `id` int(9) NOT NULL,
  `client_id` int(9) NOT NULL,
  `user_id` int(9) NOT NULL,
  `date` varchar(25) NOT NULL,
  `teeth` varchar(50) NOT NULL,
  `bleed_inner` varchar(50) NOT NULL,
  `bleed_outer` varchar(50) NOT NULL,
  `bleed_middle` varchar(50) NOT NULL,
  `stix` varchar(50) NOT NULL,
  `pass` varchar(50) NOT NULL,
  `tartar` varchar(10) NOT NULL,
  `next_date` varchar(25) NOT NULL,
  `note` text NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `user`
--

CREATE TABLE `user` (
  `id` int(9) NOT NULL,
  `practice_id` int(9) NOT NULL,
  `position_id` int(9) NOT NULL,
  `code` varchar(5) NOT NULL COMMENT 'Login code for user',
  `password` varchar(32) NOT NULL COMMENT 'Password in md5 hash',
  `e_title` blob NOT NULL,
  `e_name` blob NOT NULL,
  `e_surname` blob NOT NULL,
  `gender` int(1) NOT NULL,
  `authorization` varchar(10) NOT NULL,
  `reset_password` int(1) NOT NULL DEFAULT '0',
  `deleted` varchar(25) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Indexes for dumped tables
--

--
-- Indexes for table `client`
--
ALTER TABLE `client`
  ADD PRIMARY KEY (`id`),
  ADD KEY `practice_id` (`practice_id`);

--
-- Indexes for table `language`
--
ALTER TABLE `language`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `mail`
--
ALTER TABLE `mail`
  ADD PRIMARY KEY (`id`),
  ADD KEY `language_id` (`language_id`);

--
-- Indexes for table `position`
--
ALTER TABLE `position`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `practice`
--
ALTER TABLE `practice`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `code` (`code`),
  ADD UNIQUE KEY `name` (`company`),
  ADD KEY `language_id` (`language_id`);

--
-- Indexes for table `setup`
--
ALTER TABLE `setup`
  ADD PRIMARY KEY (`id`),
  ADD KEY `practice_id` (`practice_id`);

--
-- Indexes for table `term`
--
ALTER TABLE `term`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `client_id` (`client_id`);

--
-- Indexes for table `user`
--
ALTER TABLE `user`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `code` (`code`),
  ADD KEY `practice_id` (`practice_id`),
  ADD KEY `position_id` (`position_id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `client`
--
ALTER TABLE `client`
  MODIFY `id` int(9) NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT for table `language`
--
ALTER TABLE `language`
  MODIFY `id` int(9) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;
--
-- AUTO_INCREMENT for table `mail`
--
ALTER TABLE `mail`
  MODIFY `id` int(9) NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT for table `position`
--
ALTER TABLE `position`
  MODIFY `id` int(9) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;
--
-- AUTO_INCREMENT for table `practice`
--
ALTER TABLE `practice`
  MODIFY `id` int(9) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;
--
-- AUTO_INCREMENT for table `setup`
--
ALTER TABLE `setup`
  MODIFY `id` int(9) NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT for table `term`
--
ALTER TABLE `term`
  MODIFY `id` int(9) NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT for table `user`
--
ALTER TABLE `user`
  MODIFY `id` int(9) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;
--
-- Constraints for dumped tables
--

--
-- Constraints for table `client`
--
ALTER TABLE `client`
  ADD CONSTRAINT `client_ibfk_1` FOREIGN KEY (`practice_id`) REFERENCES `practice` (`id`);

--
-- Constraints for table `mail`
--
ALTER TABLE `mail`
  ADD CONSTRAINT `mail_ibfk_1` FOREIGN KEY (`language_id`) REFERENCES `language` (`id`);

--
-- Constraints for table `practice`
--
ALTER TABLE `practice`
  ADD CONSTRAINT `practice_ibfk_1` FOREIGN KEY (`language_id`) REFERENCES `language` (`id`);

--
-- Constraints for table `setup`
--
ALTER TABLE `setup`
  ADD CONSTRAINT `setup_ibfk_1` FOREIGN KEY (`practice_id`) REFERENCES `practice` (`id`);

--
-- Constraints for table `term`
--
ALTER TABLE `term`
  ADD CONSTRAINT `term_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `user` (`id`),
  ADD CONSTRAINT `term_ibfk_2` FOREIGN KEY (`client_id`) REFERENCES `client` (`id`);

--
-- Constraints for table `user`
--
ALTER TABLE `user`
  ADD CONSTRAINT `user_ibfk_1` FOREIGN KEY (`practice_id`) REFERENCES `practice` (`id`),
  ADD CONSTRAINT `user_ibfk_2` FOREIGN KEY (`position_id`) REFERENCES `position` (`id`);

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
