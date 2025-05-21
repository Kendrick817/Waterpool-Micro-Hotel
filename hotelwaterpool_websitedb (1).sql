-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: May 18, 2025 at 04:12 PM
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
-- Database: `hotelwaterpool_websitedb`
--

-- --------------------------------------------------------

--
-- Table structure for table `admin`
--

CREATE TABLE `admin` (
  `admin_ID` int(11) NOT NULL,
  `username` varchar(50) NOT NULL,
  `password` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `admin`
--

INSERT INTO `admin` (`admin_ID`, `username`, `password`) VALUES
(1, 'admin', '0192023a7bbd73250516f069df18b500');

-- --------------------------------------------------------

--
-- Table structure for table `bookings`
--

CREATE TABLE `bookings` (
  `booking_ID` int(11) NOT NULL,
  `room_ID` int(11) NOT NULL,
  `user_email` varchar(100) NOT NULL,
  `check_in` date NOT NULL,
  `check_out` date NOT NULL,
  `adults` int(11) NOT NULL,
  `children` int(11) NOT NULL,
  `status` varchar(20) DEFAULT 'Pending',
  `nights` int(11) DEFAULT 0,
  `total_price` decimal(10,2) DEFAULT 0.00,
  `price_calculation` varchar(100) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `bookings`
--

INSERT INTO `bookings` (`booking_ID`, `room_ID`, `user_email`, `check_in`, `check_out`, `adults`, `children`, `status`, `nights`, `total_price`, `price_calculation`, `created_at`) VALUES
(58, 6, 'kendra@gmail.com', '2025-05-16', '2025-05-17', 3, 5, 'Cancelled', 1, 4000.00, '₱4,000.00 × 1 night', '2025-05-15 14:38:21'),
(59, 7, 'kendrick.amparado817@gmail.com', '2025-05-27', '2025-05-28', 2, 0, 'Confirmed', 1, 7000.00, '₱7,000.00 × 1 night', '2025-05-16 07:48:53'),
(60, 5, 'kendra@gmail.com', '2025-05-28', '2025-05-29', 1, 0, 'Confirmed', 1, 1500.00, '₱1,500.00 × 1 night', '2025-05-16 11:06:40'),
(61, 5, 'amparado.kendrick123@gmail.com', '2025-05-19', '2025-05-20', 4, 2, 'Confirmed', 1, 1500.00, '₱1,500.00 × 1 night', '2025-05-18 02:02:31'),
(64, 6, 'kendra@gmail.com', '2025-06-05', '2025-06-06', 4, 0, 'Confirmed', 1, 4000.00, '₱4,000.00 × 1 night', '2025-05-18 10:57:23');

-- --------------------------------------------------------

--
-- Table structure for table `contact_messages`
--

CREATE TABLE `contact_messages` (
  `id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `email` varchar(100) NOT NULL,
  `subject` varchar(200) NOT NULL,
  `message` text NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `contact_messages`
--

INSERT INTO `contact_messages` (`id`, `name`, `email`, `subject`, `message`, `created_at`) VALUES
(2, 'Kendrick Obre Amparado', 'kendrick.amparado817@gmail.com', 'asda', 'Lorem ipsum dolor sit amet, consectetur adipiscing elit. Sed do eiusmod tempor incididunt ut labore et dolore magna aliqua. Ut enim ad minim veniam, quis nostrud exercitation ullamco laboris nisi ut aliquip ex ea commodo consequat. Duis aute irure dolor in reprehenderit in voluptate velit esse cillum dolore eu fugiat nulla pariatur. Excepteur sint occaecat cupidatat non proident, sunt in culpa qui officia deserunt mollit anim id est laborum.', '2025-05-11 12:43:56'),
(4, 'asda', 'elbert@gmail.com', 'asd', 'asdawdawds', '2025-05-14 12:23:38'),
(5, 'kends', 'kendra@gmail.com', 'ngutana lang', 'asdaw a raw asdawe', '2025-05-15 11:48:29'),
(6, 'asdawda', 'awdasd@gmail.com', 'wdasdawd', 'Beneath the overcast sky, a gentle breeze swept through the empty park, rustling the leaves and stirring memories of forgotten afternoons. The old bench near the fountain creaked quietly, its wood weathered by years of sun and rain. Somewhere in the distance, a dog barked once, then silence returned, wrapping the scene in a calm stillness. It was the kind of day that invited reflection, where time seemed to stretch and fold over itself, blurring the line between what was and what could be.Beneath the overcast sky, a gentle breeze swept through the empty park, rustling the leaves and stirring memories of forgotten afternoons. The old bench near the fountain creaked quietly, its wood weathered by years of sun and rain. Somewhere in the distance, a dog barked once, then silence returned, wrapping the scene in a calm stillness. It was the kind of day that invited reflection, where time seemed to stretch and fold over itself, blurring the line between what was and what could be.Beneath the overcast sky, a gentle breeze swept through the empty park, rustling the leaves and stirring memories of forgotten afternoons. The old bench near the fountain creaked quietly, its wood weathered by years of sun and rain. Somewhere in the distance, a dog barked once, then silence returned, wrapping the scene in a calm stillness. It was the kind of day that invited reflection, where time seemed to stretch and fold over itself, blurring the line between what was and what could be.Beneath the overcast sky, a gentle breeze swept through the empty park, rustling the leaves and stirring memories of forgotten afternoons. The old bench near the fountain creaked quietly, its wood weathered by years of sun and rain. Somewhere in the distance, a dog barked once, then silence returned, wrapping the scene in a calm stillness. It was the kind of day that invited reflection, where time seemed to stretch and fold over itself, blurring the line between what was and what could be.Beneath the overcast sky, a gentle breeze swept through the empty park, rustling the leaves and stirring memories of forgotten afternoons. The old bench near the fountain creaked quietly, its wood weathered by years of sun and rain. Somewhere in the distance, a dog barked once, then silence returned, wrapping the scene in a calm stillness. It was the kind of day that invited reflection, where time seemed to stretch and fold over itself, blurring the line between what was and what could be.', '2025-05-15 13:27:02'),
(7, 'Kendrick Obre Amparado', 'kendrick.amparado817@gmail.com', 'how to book', 'ga error pag mag booked', '2025-05-18 02:10:17'),
(9, 'Elbert Cahanap', 'elbert.cahanap@gmail.com', 'ngutana lang', 'ngano diman ko maka booked', '2025-05-18 14:00:17');

-- --------------------------------------------------------

--
-- Table structure for table `facilities`
--

CREATE TABLE `facilities` (
  `facility_ID` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `description` text DEFAULT NULL,
  `icon` varchar(50) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `facilities`
--

INSERT INTO `facilities` (`facility_ID`, `name`, `description`, `icon`) VALUES
(4, 'Wireless Fidelity', 'Complimentary high-speed Wi-Fi available in all rooms and areas.', '1747321243_wifi.png'),
(5, 'Swimming Pool', 'Relax and unwind in our well-maintained outdoor swimming pool', '1747321737_swimming-pool.png'),
(6, 'Swimming Pool (Kids)', 'Shallow kids’ pool with safety features, clean, fun, and supervised.', '1747322675_1747321737_swimming-pool.png'),
(7, 'Parking Area', 'Spacious, secure parking area available for all guests.', '1747322832_parking-area.png'),
(8, 'Smoking Area', 'Designated smoking area provided, safe, ventilated, and away from rooms.', '1747322918_smoke.png'),
(9, 'Air Condition', 'Air-conditioned rooms with adjustable temperature for comfort in all seasons.', '1747323329_air.png'),
(10, 'Karaoke', 'Karaoke area with modern system, fun vibes, and song variety.', '1747326997_microphone.png');

-- --------------------------------------------------------

--
-- Table structure for table `payments`
--

CREATE TABLE `payments` (
  `payment_ID` int(11) NOT NULL,
  `booking_ID` int(11) NOT NULL,
  `user_ID` int(11) NOT NULL,
  `amount` decimal(10,2) NOT NULL,
  `payment_method` varchar(50) NOT NULL,
  `transaction_id` varchar(100) NOT NULL,
  `card_number` varchar(30) DEFAULT NULL,
  `cardholder_name` varchar(100) DEFAULT NULL,
  `expiry_date` varchar(10) DEFAULT NULL,
  `payment_date` datetime NOT NULL,
  `status` varchar(20) NOT NULL DEFAULT 'Pending'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `payments`
--

INSERT INTO `payments` (`payment_ID`, `booking_ID`, `user_ID`, `amount`, `payment_method`, `transaction_id`, `card_number`, `cardholder_name`, `expiry_date`, `payment_date`, `status`) VALUES
(40, 58, 3, 4000.00, 'Credit/Debit Card', 'TXN17473199185203', '************3131', 'asdawdawd', '2025-12', '2025-05-15 16:38:38', 'Completed'),
(41, 59, 1, 7000.00, 'GCash', 'TXN17473817404865', '+63412****131', 'asd ada', '', '2025-05-16 09:49:00', 'Completed'),
(42, 60, 3, 1500.00, 'PayMaya', 'TXN17473936136670', '+63412****131', 'asd ada', '', '2025-05-16 13:06:53', 'Completed'),
(43, 61, 7, 1500.00, 'Credit/Debit Card', 'TXN17475337895148', '************1241', 'Development bank of the philippines', '2029-12', '2025-05-18 04:03:09', 'Completed'),
(44, 64, 3, 4000.00, 'GCash', 'TXN17475658527622', '+63412****131', 'asd ada', '', '2025-05-18 12:57:32', 'Completed');

-- --------------------------------------------------------

--
-- Table structure for table `rooms`
--

CREATE TABLE `rooms` (
  `room_ID` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `price` decimal(10,2) NOT NULL,
  `max_guests` int(11) NOT NULL DEFAULT 2,
  `features` text NOT NULL,
  `facilities` text DEFAULT NULL,
  `image` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `rooms`
--

INSERT INTO `rooms` (`room_ID`, `name`, `price`, `max_guests`, `features`, `facilities`, `image`) VALUES
(5, 'Single Bed Room', 1500.00, 3, '1 Single Bed, Bedside table with lamp, Full-length mirror', 'Wi-Fi, Television, Aircon, Water Heater', '1747317333_1746266155_1745994620_bed3bb822d792fcdf53805faa4919b50.jpeg'),
(6, 'Double Bed Room', 4000.00, 6, '2 Separate Single Beds, Desk and chair,Full-length mirror', 'Wi-Fi, Television, Aircon, Water Heater', '1747317662_1745991208_cbd777875e3e8d636bccfc23752c46a7.jpeg'),
(7, 'Deluxe Room', 7000.00, 10, '1 King-size bed,Premium mattress,Sofa with coffee table,full-length mirror', 'Wi-Fi, Television, Aircon, Water Heater', '1747317823_93343221fa1941698a99c3a93edcd1ad.jpeg');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `user_ID` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `username` varchar(50) DEFAULT NULL,
  `email` varchar(100) NOT NULL,
  `phone` varchar(20) NOT NULL,
  `password` varchar(255) NOT NULL,
  `reset_code` varchar(10) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`user_ID`, `name`, `username`, `email`, `phone`, `password`, `reset_code`) VALUES
(1, 'Kendrick Obre Amparado', 'kendrick.amparado817', 'kendrick.amparado817@gmail.com', '09056002729', '$2y$10$6nq9lGzST7Gd8HmrTMUevuW8uMEm2.eAwLYjLdL3NMzupl0fhe8zC', '972284'),
(3, 'kends', 'user', 'kendra@gmail.com', '09922182700', '$2y$10$KuEppCtS2jYnxTZwYubydOj6HEw1Iy2pjnxUb2d0b2J8hlAW6TMym', '586275'),
(6, 'asda', 'asd', 'asdas@gmail.com', '09123415151', '$2y$10$LpPobI6B8Lixid9fqNKg2Oob1bX125Q0AejJkhJ69qCIFu3RjmeiS', NULL),
(7, 'kendrick Amparado', 'kendrickamparado233', 'amparado.kendrick123@gmail.com', '', '$2y$10$nUEfXxEo91f/Zh8LW0hGvuCrFw0kJRAZ6FyK6TEhLn5Plk7ASCyb.', NULL),
(8, 'asdasd qwerty', 'asdasdqwerty948', 'asdasd.qwerty011@gmail.com', '', '$2y$10$ayGEL56GY06JXKU6lUF0tei9FgXT5Udi3cDizwtInWLcNp8wpOvmG', NULL),
(9, 'user', 'asdasd', 'asdasd.qwerty0111@gmail.com', '312312313131', '$2y$10$HPKSn1ClhLc.DhNx.BNcK.vkRLla99PrnTOrulDyw.zT26FsLNtj2', NULL),
(10, 'elbert', 'elbert', 'elbert.cahanap@gmail.com', '12312313131', '$2y$10$5roFm/VoyBo06gBgq0eKrepzo2pabd9Tenq.y6izzdwfLmx6glwHG', NULL);

--
-- Indexes for dumped tables
--

--
-- Indexes for table `admin`
--
ALTER TABLE `admin`
  ADD PRIMARY KEY (`admin_ID`),
  ADD UNIQUE KEY `username` (`username`);

--
-- Indexes for table `bookings`
--
ALTER TABLE `bookings`
  ADD PRIMARY KEY (`booking_ID`),
  ADD KEY `room_ID` (`room_ID`),
  ADD KEY `user_email` (`user_email`);

--
-- Indexes for table `contact_messages`
--
ALTER TABLE `contact_messages`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `facilities`
--
ALTER TABLE `facilities`
  ADD PRIMARY KEY (`facility_ID`);

--
-- Indexes for table `payments`
--
ALTER TABLE `payments`
  ADD PRIMARY KEY (`payment_ID`),
  ADD KEY `booking_ID` (`booking_ID`),
  ADD KEY `user_ID` (`user_ID`);

--
-- Indexes for table `rooms`
--
ALTER TABLE `rooms`
  ADD PRIMARY KEY (`room_ID`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`user_ID`),
  ADD UNIQUE KEY `email` (`email`),
  ADD UNIQUE KEY `username` (`username`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `admin`
--
ALTER TABLE `admin`
  MODIFY `admin_ID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=1002;

--
-- AUTO_INCREMENT for table `bookings`
--
ALTER TABLE `bookings`
  MODIFY `booking_ID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=65;

--
-- AUTO_INCREMENT for table `contact_messages`
--
ALTER TABLE `contact_messages`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- AUTO_INCREMENT for table `facilities`
--
ALTER TABLE `facilities`
  MODIFY `facility_ID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT for table `payments`
--
ALTER TABLE `payments`
  MODIFY `payment_ID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=45;

--
-- AUTO_INCREMENT for table `rooms`
--
ALTER TABLE `rooms`
  MODIFY `room_ID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `user_ID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `bookings`
--
ALTER TABLE `bookings`
  ADD CONSTRAINT `bookings_ibfk_1` FOREIGN KEY (`room_ID`) REFERENCES `rooms` (`room_ID`),
  ADD CONSTRAINT `bookings_ibfk_2` FOREIGN KEY (`user_email`) REFERENCES `users` (`email`);

--
-- Constraints for table `payments`
--
ALTER TABLE `payments`
  ADD CONSTRAINT `payments_booking_fk` FOREIGN KEY (`booking_ID`) REFERENCES `bookings` (`booking_ID`) ON DELETE CASCADE,
  ADD CONSTRAINT `payments_user_fk` FOREIGN KEY (`user_ID`) REFERENCES `users` (`user_ID`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
