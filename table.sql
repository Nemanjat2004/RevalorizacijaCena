-- phpMyAdmin SQL Dump
-- version 5.2.0
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Jun 12, 2025 at 12:43 PM
-- Server version: 10.4.27-MariaDB
-- PHP Version: 7.4.33

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `stambenagithub`
--

-- --------------------------------------------------------

--
-- Table structure for table `izvodstavke`
--

CREATE TABLE `izvodstavke` (
  `id` int(11) NOT NULL,
  `id_ugovora` int(11) NOT NULL,
  `Dug` decimal(18,2) NOT NULL,
  `datum` date DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_bin;

--
-- Dumping data for table `izvodstavke`
--

INSERT INTO `izvodstavke` (`id`, `id_ugovora`, `Dug`, `datum`) VALUES
(1, 1, '500.00', '2025-06-15');

-- --------------------------------------------------------

--
-- Table structure for table `rate`
--

CREATE TABLE `rate` (
  `id` int(11) NOT NULL,
  `id_ugovora` int(11) NOT NULL,
  `redni_broj` int(11) NOT NULL,
  `cena` decimal(18,2) NOT NULL,
  `rev_cena` decimal(18,2) DEFAULT NULL,
  `datum` date NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_bin;

-- --------------------------------------------------------

--
-- Table structure for table `revalorizacija`
--

CREATE TABLE `revalorizacija` (
  `id` int(11) NOT NULL,
  `datum_rev` date NOT NULL,
  `napomena` varchar(255) DEFAULT NULL,
  `koef` decimal(10,6) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_bin;

-- --------------------------------------------------------

--
-- Table structure for table `rev_ugovor`
--

CREATE TABLE `rev_ugovor` (
  `id` int(11) NOT NULL,
  `id_revalorizacije` int(11) NOT NULL,
  `id_ugovora` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_bin;

-- --------------------------------------------------------

--
-- Table structure for table `ugovori`
--

CREATE TABLE `ugovori` (
  `id` int(11) NOT NULL,
  `datumDok` date DEFAULT NULL,
  `Cena` decimal(18,2) DEFAULT NULL,
  `Ucesce` decimal(18,2) DEFAULT NULL,
  `BrojRata` int(11) DEFAULT NULL,
  `Aktivan` tinyint(1) DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_bin;

--
-- Dumping data for table `ugovori`
--

INSERT INTO `ugovori` (`id`, `datumDok`, `Cena`, `Ucesce`, `BrojRata`, `Aktivan`) VALUES
(1, '2025-06-01', '1000.00', '0.00', 2, 1);

--
-- Indexes for dumped tables
--

--
-- Indexes for table `izvodstavke`
--
ALTER TABLE `izvodstavke`
  ADD PRIMARY KEY (`id`),
  ADD KEY `id_ugovora` (`id_ugovora`);

--
-- Indexes for table `rate`
--
ALTER TABLE `rate`
  ADD PRIMARY KEY (`id`),
  ADD KEY `id_ugovora` (`id_ugovora`);

--
-- Indexes for table `revalorizacija`
--
ALTER TABLE `revalorizacija`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `rev_ugovor`
--
ALTER TABLE `rev_ugovor`
  ADD PRIMARY KEY (`id`),
  ADD KEY `id_revalorizacije` (`id_revalorizacije`),
  ADD KEY `id_ugovora` (`id_ugovora`);

--
-- Indexes for table `ugovori`
--
ALTER TABLE `ugovori`
  ADD PRIMARY KEY (`id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `izvodstavke`
--
ALTER TABLE `izvodstavke`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `rate`
--
ALTER TABLE `rate`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=14;

--
-- AUTO_INCREMENT for table `revalorizacija`
--
ALTER TABLE `revalorizacija`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `rev_ugovor`
--
ALTER TABLE `rev_ugovor`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=21;

--
-- AUTO_INCREMENT for table `ugovori`
--
ALTER TABLE `ugovori`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `izvodstavke`
--
ALTER TABLE `izvodstavke`
  ADD CONSTRAINT `izvodstavke_ibfk_1` FOREIGN KEY (`id_ugovora`) REFERENCES `ugovori` (`id`);

--
-- Constraints for table `rate`
--
ALTER TABLE `rate`
  ADD CONSTRAINT `rate_ibfk_1` FOREIGN KEY (`id_ugovora`) REFERENCES `ugovori` (`id`);

--
-- Constraints for table `rev_ugovor`
--
ALTER TABLE `rev_ugovor`
  ADD CONSTRAINT `rev_ugovor_ibfk_1` FOREIGN KEY (`id_revalorizacije`) REFERENCES `revalorizacija` (`id`),
  ADD CONSTRAINT `rev_ugovor_ibfk_2` FOREIGN KEY (`id_ugovora`) REFERENCES `ugovori` (`id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
