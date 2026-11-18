-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Nov 18, 2026 at 05:19 AM
-- Server version: 10.4.32-MariaDB
-- PHP Version: 8.0.30

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `paper_trail_books`
--

-- --------------------------------------------------------

--
-- Table structure for table `books`
--

CREATE TABLE `books` (
  `book_id` int(10) UNSIGNED NOT NULL,
  `title` varchar(200) NOT NULL,
  `author` varchar(150) NOT NULL,
  `category` varchar(80) NOT NULL,
  `price` decimal(10,2) NOT NULL,
  `stock_quantity` int(10) UNSIGNED NOT NULL DEFAULT 0,
  `description` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `books`
--

INSERT INTO `books` (`book_id`, `title`, `author`, `category`, `price`, `stock_quantity`, `description`) VALUES
(1, 'The Lantern Bookshop', 'Maya Bennett', 'Fiction', 24.95, 12, 'A bookseller discovers a collection of mysterious letters.'),
(2, 'Beyond the Paper Trail', 'Oliver Reed', 'Mystery', 29.50, 8, 'A missing journal leads a librarian through a coastal mystery.'),
(3, 'Cloud Computing Essentials', 'Nina Clarke', 'Technology', 39.95, 10, 'An introductory guide to cloud concepts and application design.'),
(4, 'Gardens of Tomorrow', 'Maya Bennett', 'Lifestyle', 22.00, 6, 'Practical ideas for creating a small home garden.'),
(5, 'The Little Star Explorer', 'Leo Santos', 'Children', 18.50, 15, 'A young explorer follows a trail of stars across imaginary worlds.'),
(6, 'Learning PHP Step by Step', 'Nina Clarke', 'Technology', 34.95, 0, 'A beginner-friendly introduction to building PHP applications.');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `books`
--
ALTER TABLE `books`
  ADD PRIMARY KEY (`book_id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `books`
--
ALTER TABLE `books`
  MODIFY `book_id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
