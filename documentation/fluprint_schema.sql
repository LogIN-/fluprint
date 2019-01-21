-- phpMyAdmin SQL Dump
-- version 4.8.4
-- https://www.phpmyadmin.net/
--
-- Host: localhost
-- Generation Time: Jan 21, 2019 at 08:10 PM
-- Server version: 10.1.37-MariaDB
-- PHP Version: 7.3.0

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET AUTOCOMMIT = 0;
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `fluprint`
--

-- --------------------------------------------------------

--
-- Table structure for table `donors`
--

CREATE TABLE `donors` (
  `id` int(11) NOT NULL,
  `study_donor_id` varchar(45) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `study_id` int(11) DEFAULT NULL,
  `study_internal_id` varchar(45) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `gender` varchar(45) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `race` varchar(45) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `birth_age_weeks` tinyint(4) DEFAULT NULL,
  `breastfed` tinyint(1) DEFAULT NULL,
  `breastfed_period` varchar(45) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `daycare` tinyint(1) DEFAULT NULL,
  `daycare_age` float DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `donor_visits`
--

CREATE TABLE `donor_visits` (
  `id` int(11) NOT NULL,
  `donor_id` int(11) NOT NULL,
  `visit_id` tinyint(4) DEFAULT NULL,
  `visit_internal_id` tinyint(4) DEFAULT NULL,
  `visit_year` int(11) DEFAULT NULL,
  `visit_day` varchar(45) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `visit_type_hai` varchar(10) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `age` float DEFAULT NULL,
  `age_round` tinyint(4) DEFAULT NULL,
  `cmv_status` int(11) DEFAULT NULL,
  `ebv_status` int(11) DEFAULT NULL,
  `bmi` float DEFAULT NULL,
  `vaccine` tinyint(6) DEFAULT NULL,
  `geo_mean` float DEFAULT NULL,
  `d_geo_mean` int(11) DEFAULT NULL,
  `delta_single` int(11) DEFAULT NULL,
  `vaccine_resp` tinyint(4) DEFAULT NULL,
  `total_data` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Main Donor Details Table';

-- --------------------------------------------------------

--
-- Table structure for table `experimental_data`
--

CREATE TABLE `experimental_data` (
  `id` int(11) NOT NULL,
  `donor_id` int(11) NOT NULL,
  `donor_visits_id` int(11) NOT NULL,
  `assay` tinyint(4) DEFAULT NULL,
  `name` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `name_formatted` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `subset` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `units` varchar(45) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `data` float DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Indexes for dumped tables
--

--
-- Indexes for table `donors`
--
ALTER TABLE `donors`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `donor_id_uq` (`study_donor_id`) USING BTREE,
  ADD KEY `study_id` (`study_id`);

--
-- Indexes for table `donor_visits`
--
ALTER TABLE `donor_visits`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_donor_visits_donors_idx` (`donor_id`),
  ADD KEY `unique_donor_idx` (`visit_internal_id`,`donor_id`) USING BTREE,
  ADD KEY `age_idx` (`age`) USING BTREE,
  ADD KEY `vaccine_idx` (`vaccine`) USING BTREE,
  ADD KEY `visit_year_idx` (`visit_year`) USING BTREE,
  ADD KEY `vaccine_resp` (`vaccine_resp`),
  ADD KEY `age_round` (`age_round`);

--
-- Indexes for table `experimental_data`
--
ALTER TABLE `experimental_data`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_experimental_data_donor_visits_idx` (`donor_visits_id`),
  ADD KEY `assay_idx` (`assay`) USING BTREE,
  ADD KEY `name_formatted` (`name_formatted`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `donors`
--
ALTER TABLE `donors`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `donor_visits`
--
ALTER TABLE `donor_visits`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `experimental_data`
--
ALTER TABLE `experimental_data`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
