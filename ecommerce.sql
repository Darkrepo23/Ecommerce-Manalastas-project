-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Jan 08, 2026 at 11:43 AM
-- Server version: 10.4.32-MariaDB
-- PHP Version: 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `ecommerce`
--

-- --------------------------------------------------------

--
-- Table structure for table `carts`
--

CREATE TABLE `carts` (
  `id` int(11) NOT NULL,
  `user_id` int(200) NOT NULL,
  `Cart` varchar(200) NOT NULL,
  `Quantity` int(200) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `carts`
--

INSERT INTO `carts` (`id`, `user_id`, `Cart`, `Quantity`) VALUES
(7, 7, 'Zx', 1),
(8, 22, 'Zx', 2);

-- --------------------------------------------------------

--
-- Table structure for table `products`
--

CREATE TABLE `products` (
  `Product Name` varchar(200) NOT NULL,
  `Description` varchar(200) NOT NULL,
  `Ratings` varchar(50) NOT NULL,
  `Stock` int(250) NOT NULL,
  `Sold` int(250) NOT NULL,
  `Product Img` varchar(200) NOT NULL,
  `Adding Date` date NOT NULL,
  `Price` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `products`
--

INSERT INTO `products` (`Product Name`, `Description`, `Ratings`, `Stock`, `Sold`, `Product Img`, `Adding Date`, `Price`) VALUES
('Zx', 'aadsadasd', '2', 232, 9, 'uploads/prod_695d325d865806.99443201.png', '2026-01-06', 34344),
('asdsd', '2323', '5', 2323, 0, 'uploads/prod_695d71bb441063.31194275.png', '2026-01-06', 23);

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `ID` int(11) NOT NULL,
  `Username` varchar(100) NOT NULL,
  `Email` varchar(255) NOT NULL,
  `Password` varchar(255) NOT NULL,
  `ProfileImg` varchar(500) DEFAULT NULL,
  `DateCreated` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`ID`, `Username`, `Email`, `Password`, `ProfileImg`, `DateCreated`) VALUES
(3, 'asdad', 'cosmarkgarcia@gmail.com', '$2y$10$Nxd3nH.pUWdEmTNaHprR5e855ZqhHJlSmrB3rXyVdt4LV97ELB/hy', '../uploads/users/user_695d2473d1c113.87862721.png', '2026-01-06 23:04:19'),
(7, 'vincent', 'vincentmanalastas827@gmail.com', '$2y$10$nDFbpd9xBOXuc1fPWTseIeIfTD1LoYzxNTFsZrbt0Wr4.k.JEjfN2', NULL, '2026-01-07 03:10:03'),
(8, 'elms123', 'manalastasjg@gmail.com', '$2y$10$YlDjDS8awKW2pEULw5vPeuTIOo4QpFzRm61GePtqTckPM0fzYGJ.y', '../uploads/users/user_695f76340e3713.96235396.png', '2026-01-08 17:17:40');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `carts`
--
ALTER TABLE `carts`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`ID`),
  ADD UNIQUE KEY `Username` (`Username`),
  ADD UNIQUE KEY `Email` (`Email`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `carts`
--
ALTER TABLE `carts`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `ID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
