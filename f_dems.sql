-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Jul 11, 2025 at 09:50 AM
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
-- Database: `f_dems`
--

-- --------------------------------------------------------

--
-- Table structure for table `account_information_table`
--

CREATE TABLE `account_information_table` (
  `account_information_id` int(200) NOT NULL,
  `pre_reg_id` int(200) NOT NULL,
  `bank_Ewallet` varchar(200) NOT NULL,
  `account_name` varchar(200) NOT NULL,
  `account_type` varchar(200) NOT NULL,
  `account_number` int(200) NOT NULL,
  `house_ownership` varchar(200) NOT NULL,
  `shelter_damage_classification` varchar(200) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `account_information_table`
--

INSERT INTO `account_information_table` (`account_information_id`, `pre_reg_id`, `bank_Ewallet`, `account_name`, `account_type`, `account_number`, `house_ownership`, `shelter_damage_classification`) VALUES
(1, 0, '123', '123', '123', 213, '', ''),
(2, 0, '123', '123', '123', 123, '', ''),
(3, 0, '123', '123', '123', 123, '', ''),
(4, 0, '123', '123', '123', 123, '', ''),
(5, 0, '123', '123', '123', 23123, '', ''),
(6, 0, 'qwe', 'qwe', 'qweqwe', 12312, '', ''),
(7, 0, '123', '123', '123', 123, '', ''),
(8, 0, '123', '123', '123', 123, '', ''),
(9, 0, '123', '123', '123', 123, '', ''),
(10, 0, '123123123123', '123', '123', 123, '', ''),
(11, 0, '123', '123', '123', 123, '', '');

-- --------------------------------------------------------

--
-- Table structure for table `admin_table`
--

CREATE TABLE `admin_table` (
  `admin_id` int(11) NOT NULL,
  `f_name` varchar(200) NOT NULL,
  `l_name` varchar(200) NOT NULL,
  `username` varchar(200) NOT NULL,
  `password` varchar(200) NOT NULL,
  `session_token` varchar(200) NOT NULL,
  `role` varchar(200) NOT NULL,
  `evac_loc_id` int(200) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `admin_table`
--

INSERT INTO `admin_table` (`admin_id`, `f_name`, `l_name`, `username`, `password`, `session_token`, `role`, `evac_loc_id`) VALUES
(12, 'admnin', 'qewqeew', 'admin', 'admin', '2f5647e5551cf383054d91379df9ffdb4b49a36dccfe1dbb2dd3eca1dd79136b', 'Admin', 0),
(13, 'wynbernard', 'deysolong', 'wynbernard', '123', '', 'Staff', 0);

-- --------------------------------------------------------

--
-- Table structure for table `age_class_table`
--

CREATE TABLE `age_class_table` (
  `age_class_id` int(11) NOT NULL,
  `classification` varchar(200) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `age_class_table`
--

INSERT INTO `age_class_table` (`age_class_id`, `classification`) VALUES
(1, 'Adult'),
(2, 'Child'),
(3, 'Infant');

-- --------------------------------------------------------

--
-- Table structure for table `barangay_manegement_table`
--

CREATE TABLE `barangay_manegement_table` (
  `barangay_id` int(200) NOT NULL,
  `barangay_name` varchar(200) NOT NULL,
  `barangay_captain_name` varchar(200) NOT NULL,
  `signature_brgy_captain` varchar(200) NOT NULL,
  `latitude` varchar(200) NOT NULL,
  `longitude` varchar(200) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `barangay_manegement_table`
--

INSERT INTO `barangay_manegement_table` (`barangay_id`, `barangay_name`, `barangay_captain_name`, `signature_brgy_captain`, `latitude`, `longitude`) VALUES
(12, 'Abuanan', 'cj campanil', 'signature_brgy_captain/signature_1752218536.png', '10.525303', '122.992405'),
(13, 'Alianza', 'cj campanil', 'signature_brgy_captain/signature_1752218572.png', '10.47312649567524', '122.93030619621277'),
(14, 'Atipuluan', 'cj campanil', 'signature_brgy_captain/signature_1752218692.png', '10.510957', '122.956613'),
(15, 'Bacong-Montilla', 'cj campanil', 'signature_brgy_captain/signature_1752218775.png', '10.518911', '123.034413'),
(16, 'Bagroy', 'cj campanil', 'signature_brgy_captain/signature_1752218825.png', '10.476903', '122.872344'),
(17, 'Balingasag', 'cj campanil', 'signature_brgy_captain/signature_1752218891.png', '10.531358', '122.845902'),
(18, 'Binubuhan', 'cj campanil', 'signature_brgy_captain/signature_1752218943.png', '10.457217', '123.007414'),
(19, 'Busay', 'cj campanil', 'signature_brgy_captain/signature_1752218988.png', '10.53699', '122.888389'),
(20, 'Calumangan', 'cj campanil', 'signature_brgy_captain/signature_1752219057.png', '10.559815', '122.876952'),
(21, 'Caridad', 'cj campanil', 'signature_brgy_captain/signature_1752219094.png', '10.481566', '122.905834'),
(22, 'Dulao', 'cj campanil', 'signature_brgy_captain/signature_1752219180.png', '10.548635', '122.951539'),
(23, 'Ilijan', 'cj campanil', 'signature_brgy_captain/signature_1752219262.png', '10.452595', '123.055093'),
(24, 'Lag-asan', 'cj campanil', 'signature_brgy_captain/signature_1752219316.png', '10.530229', '122.838639'),
(25, 'Ma-ao Barrio', 'cj campanil', 'signature_brgy_captain/signature_1752219414.png', '10.48912', '122.990152'),
(26, 'Jorge L. Araneta (Ma-ao Central)', 'cj campanil', 'signature_brgy_captain/signature_1752219482.png', '10.476017', '122.94597'),
(27, 'Mailum', 'cj campanil', 'signature_brgy_captain/signature_1752219585.png', '10.461574', '123.049332'),
(28, 'Malingin', 'cj campanil', 'signature_brgy_captain/signature_1752219613.png', '10.493851', '122.917868'),
(29, 'Napoles', 'cj campanil', 'signature_brgy_captain/signature_1752219641.png', '10.512539', '122.898055'),
(30, 'Pacol', 'cj campanil', 'signature_brgy_captain/signature_1752219664.png', '10.494838', '122.867339'),
(31, 'Poblacion', 'cj campanil', 'signature_brgy_captain/signature_1752219693.png', '10.5393', '122.836686'),
(32, 'Sagasa', 'cj campanil', 'signature_brgy_captain/signature_1752219733.png', '10.469413', '122.892895'),
(33, 'Tabunan', 'cj campanil', 'signature_brgy_captain/signature_1752219758.png', '10.576331', '122.93699'),
(34, 'Taloc', 'cj campanil', 'signature_brgy_captain/signature_1752219786.png', '10.586604', '122.909675'),
(35, 'Sampinit', 'cj campanil', 'signature_brgy_captain/signature_1752219819.png', '10.544057', '122.853456');

-- --------------------------------------------------------

--
-- Table structure for table `disaster_table`
--

CREATE TABLE `disaster_table` (
  `disaster_id` int(11) NOT NULL,
  `disaster_name` varchar(200) NOT NULL,
  `date` date NOT NULL,
  `level` varchar(200) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `disaster_table`
--

INSERT INTO `disaster_table` (`disaster_id`, `disaster_name`, `date`, `level`) VALUES
(1, 'Bagyong  Inday', '2025-04-29', '4');

-- --------------------------------------------------------

--
-- Table structure for table `evac_loc_table`
--

CREATE TABLE `evac_loc_table` (
  `evac_loc_id` int(11) NOT NULL,
  `city` varchar(200) NOT NULL,
  `barangay_id` int(200) NOT NULL,
  `purok` varchar(200) NOT NULL,
  `name` varchar(200) NOT NULL,
  `total_capacity` int(200) NOT NULL,
  `longitude` int(200) NOT NULL,
  `latitude` int(200) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `evac_reg_table`
--

CREATE TABLE `evac_reg_table` (
  `evac_reg_id` int(11) NOT NULL,
  `pre_reg_id` int(200) NOT NULL,
  `evac_loc_id` int(200) NOT NULL,
  `room_id` int(200) NOT NULL,
  `date_reg` date NOT NULL,
  `disaster_id` int(200) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `family_table`
--

CREATE TABLE `family_table` (
  `family_id` int(11) NOT NULL,
  `region` varchar(200) NOT NULL,
  `province` varchar(200) NOT NULL,
  `city_municipality` varchar(200) NOT NULL,
  `district` varchar(200) NOT NULL,
  `barangay_id` int(200) NOT NULL,
  `house_block_number` varchar(200) NOT NULL,
  `street` varchar(200) NOT NULL,
  `sub_village` varchar(200) NOT NULL,
  `zip_code` int(200) NOT NULL,
  `longitude` int(200) NOT NULL,
  `latitude` int(200) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `family_table`
--

INSERT INTO `family_table` (`family_id`, `region`, `province`, `city_municipality`, `district`, `barangay_id`, `house_block_number`, `street`, `sub_village`, `zip_code`, `longitude`, `latitude`) VALUES
(11, '', '', '', '', 0, '', '', '', 0, 0, 0),
(12, '', '', '', '', 0, '', '', '', 0, 0, 0),
(13, '', '', '', '', 0, '', '', '', 0, 0, 0),
(14, '', '', '', '', 0, '', '', '', 0, 0, 0),
(15, '', '', '', '', 0, '', '', '', 0, 0, 0),
(16, '', '', '', '', 0, '', '', '', 0, 0, 0),
(18, '', '', '', '', 0, '', '', '', 0, 0, 0),
(19, '', '', '', '', 0, '', '', '', 0, 0, 0),
(20, '', '', '', '', 0, '', '', '', 0, 0, 0),
(21, '', '', '', '', 0, '', '', '', 0, 0, 0),
(22, 'Region VI (Western Visayas)', 'Capiz', 'Dumalag', '', 3, 'wer', 'wer', '123', 123, 0, 0),
(23, 'National Capital Region (NCR)', 'City Of Manila', 'Port Area', '', 4, '123', '123', '123', 123, 0, 0),
(24, 'Region VI (Western Visayas)', 'Iloilo', 'Dumangas', '', 5, '123', '123', '123', 123, 0, 0),
(25, 'Region VI (Western Visayas)', 'Iloilo', 'Dumangas', '', 5, '123', '123', '123', 123, 0, 0),
(26, 'Region IX (Zamboanga Peninzula)', 'Zamboanga Del Norte', 'Siayan', '', 6, '123', '213', '123', 123, 0, 0),
(27, 'Region I (Ilocos Region)', 'Ilocos Sur', 'Cabugao', '', 7, 'qwe', 'qwe', 'qwe', 0, 0, 0),
(28, 'Region VI (Western Visayas)', 'Iloilo', 'Dumangas', '', 5, 'qwe', '123', '123', 123, 0, 0),
(29, 'Region X (Northern Mindanao)', 'Bukidnon', 'Quezon', '', 9, '123', '123', '123', 123, 0, 0),
(30, 'Region I (Ilocos Region)', 'Ilocos Sur', 'Banayoyo', '', 10, 'qwe', '123', '123', 123, 0, 0);

-- --------------------------------------------------------

--
-- Table structure for table `logs_table`
--

CREATE TABLE `logs_table` (
  `logs_id` int(11) NOT NULL,
  `evac_reg_id` int(200) NOT NULL,
  `date_time` datetime NOT NULL,
  `status` varchar(200) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `logs_table`
--

INSERT INTO `logs_table` (`logs_id`, `evac_reg_id`, `date_time`, `status`) VALUES
(37, 43, '2025-04-09 18:26:01', 'In'),
(38, 44, '2025-04-09 19:18:57', 'In'),
(39, 45, '2025-04-10 00:37:31', 'In'),
(40, 46, '2025-04-21 00:24:04', 'In'),
(41, 47, '2025-04-21 00:25:19', 'In'),
(42, 48, '2025-04-21 00:29:34', 'In'),
(43, 49, '2025-04-21 00:33:26', 'In');

-- --------------------------------------------------------

--
-- Table structure for table `pre_reg_table`
--

CREATE TABLE `pre_reg_table` (
  `pre_reg_id` int(11) NOT NULL,
  `f_name` varchar(200) NOT NULL,
  `m_name` varchar(200) NOT NULL,
  `l_name` varchar(200) NOT NULL,
  `name_ext` varchar(200) NOT NULL,
  `contact_no` bigint(200) NOT NULL,
  `email_address` varchar(200) NOT NULL,
  `password` varchar(200) NOT NULL,
  `gender` varchar(200) NOT NULL,
  `registered_as` varchar(200) NOT NULL,
  `civil_status` varchar(200) NOT NULL,
  `solo_address_id` int(200) NOT NULL,
  `family_id` int(200) NOT NULL,
  `relation_to_family` varchar(200) NOT NULL,
  `highest_education_attainment` varchar(200) NOT NULL,
  `type_vulnerability` varchar(200) NOT NULL,
  `age_class_id` int(200) NOT NULL,
  `qr_id` int(200) NOT NULL,
  `registered_date` date NOT NULL,
  `date_of_birth` date DEFAULT NULL,
  `place_of_birth` varchar(200) NOT NULL,
  `user_session_token` varchar(200) NOT NULL,
  `profile_pic` varchar(200) NOT NULL,
  `mother_maiden_name` varchar(200) NOT NULL,
  `religion` varchar(200) NOT NULL,
  `occupation` varchar(200) NOT NULL,
  `monthly_income` int(200) NOT NULL,
  `id_card_presented` varchar(200) NOT NULL,
  `id_card_number` int(200) NOT NULL,
  `account_information_id` int(200) NOT NULL,
  `signature` varchar(200) NOT NULL,
  `id_card_image` varchar(200) NOT NULL,
  `indigenous_people` varchar(200) NOT NULL,
  `4ps_beneficiary` varchar(200) NOT NULL,
  `ethnicity` varchar(200) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `pre_reg_table`
--

INSERT INTO `pre_reg_table` (`pre_reg_id`, `f_name`, `m_name`, `l_name`, `name_ext`, `contact_no`, `email_address`, `password`, `gender`, `registered_as`, `civil_status`, `solo_address_id`, `family_id`, `relation_to_family`, `highest_education_attainment`, `type_vulnerability`, `age_class_id`, `qr_id`, `registered_date`, `date_of_birth`, `place_of_birth`, `user_session_token`, `profile_pic`, `mother_maiden_name`, `religion`, `occupation`, `monthly_income`, `id_card_presented`, `id_card_number`, `account_information_id`, `signature`, `id_card_image`, `indigenous_people`, `4ps_beneficiary`, `ethnicity`) VALUES
(85, 'renato', 'wqe', 'tulmo', 'jr', 9032328623, 'renato@gmail.com', '$2y$10$d9E/zL8lBiv8sKBxBO6D3.X4sZvFjzCQhdUupyJkw65HGZO5Nxjym', 'Female', 'Solo', 'separated', 2, 0, 'Head of Family', 'Some High School', '', 3, 30, '2025-07-11', '2025-07-10', '123', '', '../profile_images/profile_6870a420567e93.65110219.jpg', '123123', '123', '123', 123, '123', 123, 11, '../../signature_image/signature_draw_1752212512.png', '../../id_card_image/natoy.jpg', 'on', 'on', '321');

-- --------------------------------------------------------

--
-- Table structure for table `qr_table`
--

CREATE TABLE `qr_table` (
  `qr_id` int(11) NOT NULL,
  `pre_reg_id` int(200) NOT NULL,
  `code` varchar(200) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `qr_table`
--

INSERT INTO `qr_table` (`qr_id`, `pre_reg_id`, `code`) VALUES
(1, 19, '../../../qrcodes/1743137089_19.png'),
(2, 20, '../../../qrcodes/1743041663_20.png'),
(3, 21, '../../../qrcodes/1743042803_21.png'),
(4, 22, '../../../qrcodes/1743124855_22.png'),
(5, 23, '../../../qrcodes/1743151833_23.png'),
(6, 24, '../../../qrcodes/1743436733_24.png'),
(7, 25, '../../../qrcodes/1743686071_25.png'),
(8, 26, '../../../qrcodes/1743686154_26.png'),
(9, 27, '../../../qrcodes/1743687680_27.png'),
(10, 28, '../../../qrcodes/1743688427_28.png'),
(11, 29, '../../../qrcodes/1743689168_29.png'),
(12, 30, '../../../qrcodes/1743689471_30.png'),
(13, 36, '../../../qrcodes/1744034332_36.png'),
(14, 38, '../../../qrcodes/1744041343_38.png'),
(15, 39, '../../../qrcodes/1744073067_39.png'),
(16, 40, '../../../qrcodes/1744172146_40.png'),
(17, 41, '../../../qrcodes/1745938247_41.png'),
(18, 42, '../../../qrcodes/1744197306_42.png'),
(19, 43, '../../../qrcodes/1745742552_43.png'),
(20, 44, '../../../qrcodes/1746006220_44.png'),
(21, 45, '../../../qrcodes/1744419987_45.png'),
(22, 52, '../../../qrcodes/1745729931_52.png'),
(23, 53, '../../../qrcodes/1744711457_53.png'),
(24, 54, '../../../qrcodes/1745742162_54.png'),
(25, 68, '../../../qrcodes/1745740831_68.png'),
(26, 76, '../../../qrcodes/1745760006_76.png'),
(27, 80, '../../../qrcodes/1745928192_80.png'),
(28, 81, '../../../qrcodes/1745929354_81.png'),
(29, 82, '../../../qrcodes/1745932956_82.png'),
(30, 85, 'uploads/qr_codes/1752212512_85.png');

-- --------------------------------------------------------

--
-- Table structure for table `resource_allocation_table`
--

CREATE TABLE `resource_allocation_table` (
  `resource_id` int(11) NOT NULL,
  `resource_name` varchar(200) NOT NULL,
  `quantity` int(200) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `resource_distribution_table`
--

CREATE TABLE `resource_distribution_table` (
  `resource_distribution_id` int(11) NOT NULL,
  `evac_reg_id` int(200) NOT NULL,
  `resource_id` int(200) NOT NULL,
  `quantity` int(200) NOT NULL,
  `date_time` datetime NOT NULL,
  `cost` int(200) NOT NULL,
  `provider` varchar(200) NOT NULL,
  `unit` varchar(200) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `room_reservation_table`
--

CREATE TABLE `room_reservation_table` (
  `room_reservation_id` int(11) NOT NULL,
  `pre_reg_id` int(200) NOT NULL,
  `evac_loc_id` int(200) NOT NULL,
  `room_id` int(200) NOT NULL,
  `date_time` datetime NOT NULL,
  `status` varchar(200) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `room_reservation_table`
--

INSERT INTO `room_reservation_table` (`room_reservation_id`, `pre_reg_id`, `evac_loc_id`, `room_id`, `date_time`, `status`) VALUES
(71, 44, 0, 3, '2025-04-18 21:28:56', ''),
(72, 47, 0, 3, '2025-04-18 21:28:56', ''),
(73, 48, 0, 3, '2025-04-18 21:28:56', ''),
(74, 49, 0, 3, '2025-04-18 21:28:56', ''),
(75, 50, 0, 3, '2025-04-18 21:28:56', ''),
(126, 52, 0, 1, '2025-04-24 20:40:05', ''),
(163, 57, 0, 3, '2025-04-29 13:27:03', ''),
(164, 58, 0, 3, '2025-04-29 13:27:03', ''),
(165, 78, 0, 3, '2025-04-29 13:27:03', ''),
(181, 41, 0, 3, '2025-04-29 22:29:26', ''),
(182, 46, 0, 3, '2025-04-29 22:29:26', ''),
(183, 51, 0, 3, '2025-04-29 22:29:26', ''),
(184, 55, 0, 3, '2025-04-29 22:29:26', ''),
(185, 56, 0, 3, '2025-04-29 22:29:26', '');

-- --------------------------------------------------------

--
-- Table structure for table `room_table`
--

CREATE TABLE `room_table` (
  `room_id` int(11) NOT NULL,
  `room_capacity` int(200) NOT NULL,
  `room_name` varchar(200) NOT NULL,
  `evac_loc_id` int(200) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `room_table`
--

INSERT INTO `room_table` (`room_id`, `room_capacity`, `room_name`, `evac_loc_id`) VALUES
(1, 10, 'room1', 1),
(3, 20, 'room2', 1),
(4, 30, 'room3', 1),
(5, 20, 'room4', 1),
(7, 20, 'room1', 6),
(8, 232, 'room2', 0),
(9, 21, 'room2', 2),
(10, 21, 'room4', 2),
(11, 200, 'room3', 2),
(12, 20, 'room5', 1),
(13, 30, 'room2', 4),
(14, 20, 'room1', 10),
(15, 20, 'room2', 10);

-- --------------------------------------------------------

--
-- Table structure for table `solo_address_table`
--

CREATE TABLE `solo_address_table` (
  `solo_address_id` int(200) NOT NULL,
  `pre_reg_id` int(200) NOT NULL,
  `region` varchar(200) NOT NULL,
  `province` varchar(200) NOT NULL,
  `city_municipality` varchar(200) NOT NULL,
  `district` varchar(200) NOT NULL,
  `barangay_id` int(200) NOT NULL,
  `house_block_number` varchar(200) NOT NULL,
  `street` varchar(200) NOT NULL,
  `sub_village` varchar(200) NOT NULL,
  `zip_code` int(200) NOT NULL,
  `longitude` int(200) NOT NULL,
  `latitude` int(200) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `solo_address_table`
--

INSERT INTO `solo_address_table` (`solo_address_id`, `pre_reg_id`, `region`, `province`, `city_municipality`, `district`, `barangay_id`, `house_block_number`, `street`, `sub_village`, `zip_code`, `longitude`, `latitude`) VALUES
(1, 0, 'Region XI (Davao Region)', 'Compostela Valley', 'New Bataan', '', 8, '123', 'Prk. Camingawan Proper', '123', 6101, 0, 0),
(2, 0, 'Cordillera Administrative Region (CAR)', 'Benguet', 'Tuba', '', 11, 'eqwe', '123', '123', 123, 0, 0);

--
-- Indexes for dumped tables
--

--
-- Indexes for table `account_information_table`
--
ALTER TABLE `account_information_table`
  ADD PRIMARY KEY (`account_information_id`);

--
-- Indexes for table `admin_table`
--
ALTER TABLE `admin_table`
  ADD PRIMARY KEY (`admin_id`);

--
-- Indexes for table `age_class_table`
--
ALTER TABLE `age_class_table`
  ADD PRIMARY KEY (`age_class_id`);

--
-- Indexes for table `barangay_manegement_table`
--
ALTER TABLE `barangay_manegement_table`
  ADD PRIMARY KEY (`barangay_id`);

--
-- Indexes for table `disaster_table`
--
ALTER TABLE `disaster_table`
  ADD PRIMARY KEY (`disaster_id`);

--
-- Indexes for table `evac_loc_table`
--
ALTER TABLE `evac_loc_table`
  ADD PRIMARY KEY (`evac_loc_id`);

--
-- Indexes for table `evac_reg_table`
--
ALTER TABLE `evac_reg_table`
  ADD PRIMARY KEY (`evac_reg_id`);

--
-- Indexes for table `family_table`
--
ALTER TABLE `family_table`
  ADD PRIMARY KEY (`family_id`);

--
-- Indexes for table `logs_table`
--
ALTER TABLE `logs_table`
  ADD PRIMARY KEY (`logs_id`);

--
-- Indexes for table `pre_reg_table`
--
ALTER TABLE `pre_reg_table`
  ADD PRIMARY KEY (`pre_reg_id`);

--
-- Indexes for table `qr_table`
--
ALTER TABLE `qr_table`
  ADD PRIMARY KEY (`qr_id`);

--
-- Indexes for table `resource_allocation_table`
--
ALTER TABLE `resource_allocation_table`
  ADD PRIMARY KEY (`resource_id`);

--
-- Indexes for table `resource_distribution_table`
--
ALTER TABLE `resource_distribution_table`
  ADD PRIMARY KEY (`resource_distribution_id`);

--
-- Indexes for table `room_reservation_table`
--
ALTER TABLE `room_reservation_table`
  ADD PRIMARY KEY (`room_reservation_id`);

--
-- Indexes for table `room_table`
--
ALTER TABLE `room_table`
  ADD PRIMARY KEY (`room_id`);

--
-- Indexes for table `solo_address_table`
--
ALTER TABLE `solo_address_table`
  ADD PRIMARY KEY (`solo_address_id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `account_information_table`
--
ALTER TABLE `account_information_table`
  MODIFY `account_information_id` int(200) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=12;

--
-- AUTO_INCREMENT for table `admin_table`
--
ALTER TABLE `admin_table`
  MODIFY `admin_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=14;

--
-- AUTO_INCREMENT for table `age_class_table`
--
ALTER TABLE `age_class_table`
  MODIFY `age_class_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `barangay_manegement_table`
--
ALTER TABLE `barangay_manegement_table`
  MODIFY `barangay_id` int(200) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=36;

--
-- AUTO_INCREMENT for table `disaster_table`
--
ALTER TABLE `disaster_table`
  MODIFY `disaster_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `evac_loc_table`
--
ALTER TABLE `evac_loc_table`
  MODIFY `evac_loc_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=15;

--
-- AUTO_INCREMENT for table `evac_reg_table`
--
ALTER TABLE `evac_reg_table`
  MODIFY `evac_reg_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=50;

--
-- AUTO_INCREMENT for table `family_table`
--
ALTER TABLE `family_table`
  MODIFY `family_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=31;

--
-- AUTO_INCREMENT for table `logs_table`
--
ALTER TABLE `logs_table`
  MODIFY `logs_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=44;

--
-- AUTO_INCREMENT for table `pre_reg_table`
--
ALTER TABLE `pre_reg_table`
  MODIFY `pre_reg_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=86;

--
-- AUTO_INCREMENT for table `qr_table`
--
ALTER TABLE `qr_table`
  MODIFY `qr_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=31;

--
-- AUTO_INCREMENT for table `resource_allocation_table`
--
ALTER TABLE `resource_allocation_table`
  MODIFY `resource_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `resource_distribution_table`
--
ALTER TABLE `resource_distribution_table`
  MODIFY `resource_distribution_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `room_reservation_table`
--
ALTER TABLE `room_reservation_table`
  MODIFY `room_reservation_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=186;

--
-- AUTO_INCREMENT for table `room_table`
--
ALTER TABLE `room_table`
  MODIFY `room_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=16;

--
-- AUTO_INCREMENT for table `solo_address_table`
--
ALTER TABLE `solo_address_table`
  MODIFY `solo_address_id` int(200) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
