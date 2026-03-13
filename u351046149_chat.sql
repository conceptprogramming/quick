-- phpMyAdmin SQL Dump
-- version 5.2.2
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1:3306
-- Generation Time: Mar 13, 2026 at 04:47 AM
-- Server version: 11.8.3-MariaDB-log
-- PHP Version: 7.2.34

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `u351046149_chat`
--

-- --------------------------------------------------------

--
-- Table structure for table `credit_ledger`
--

CREATE TABLE `credit_ledger` (
  `id` int(10) UNSIGNED NOT NULL,
  `user_id` int(10) UNSIGNED NOT NULL,
  `credit_change` int(11) NOT NULL,
  `credit_balance` int(10) UNSIGNED NOT NULL,
  `feature` enum('chat','summary','quiz','topup','subscription','subscription_expired') NOT NULL,
  `note` varchar(255) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `paypal_order_id` varchar(100) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `credit_ledger`
--

INSERT INTO `credit_ledger` (`id`, `user_id`, `credit_change`, `credit_balance`, `feature`, `note`, `created_at`, `paypal_order_id`) VALUES
(1, 1, -2, 18, 'quiz', NULL, '2026-03-02 08:17:45', NULL),
(2, 1, -2, 16, 'quiz', NULL, '2026-03-02 08:31:05', NULL),
(3, 1, -1, 15, 'chat', NULL, '2026-03-02 08:32:44', NULL),
(4, 1, -1, 14, 'chat', NULL, '2026-03-02 08:32:52', NULL),
(5, 1, -3, 11, 'summary', NULL, '2026-03-02 08:38:57', NULL),
(6, 1, -3, 8, 'summary', NULL, '2026-03-02 08:43:18', NULL),
(7, 1, -3, 5, 'summary', NULL, '2026-03-02 08:48:01', NULL),
(8, 3, -1, 19, 'chat', NULL, '2026-03-02 08:58:44', NULL),
(9, 2, -2, 18, 'quiz', NULL, '2026-03-04 05:14:42', NULL),
(10, 2, -1, 17, 'chat', NULL, '2026-03-04 05:45:34', NULL),
(11, 2, -1, 16, 'chat', NULL, '2026-03-04 05:45:58', NULL),
(12, 2, -1, 15, 'chat', NULL, '2026-03-04 05:46:16', NULL),
(13, 2, -1, 14, 'chat', NULL, '2026-03-04 05:46:36', NULL),
(14, 2, -1, 13, 'chat', NULL, '2026-03-04 05:46:47', NULL),
(15, 2, -3, 10, 'summary', NULL, '2026-03-04 05:47:42', NULL),
(16, 2, -3, 7, 'summary', NULL, '2026-03-04 05:47:57', NULL),
(17, 2, 100, 107, 'subscription', NULL, '2026-03-04 09:50:28', NULL),
(18, 2, 300, 407, 'subscription', NULL, '2026-03-04 09:52:13', NULL),
(19, 2, 100, 1507, 'subscription', NULL, '2026-03-04 10:40:50', NULL),
(20, 1, -1, 4, 'chat', NULL, '2026-03-05 05:50:14', NULL),
(21, 1, -1, 3, 'chat', NULL, '2026-03-05 05:50:26', NULL),
(22, 1, -1, 2, 'chat', NULL, '2026-03-05 05:50:35', NULL),
(23, 2, -3, 1504, 'summary', NULL, '2026-03-05 05:52:15', NULL),
(24, 2, -3, 1501, 'summary', NULL, '2026-03-05 05:52:37', NULL),
(25, 2, -1, 1500, 'chat', NULL, '2026-03-05 06:21:26', NULL),
(26, 1, 100, 102, 'subscription', NULL, '2026-03-06 08:16:20', NULL),
(27, 1, -1, 101, 'chat', NULL, '2026-03-06 08:21:59', NULL),
(28, 1, -1, 100, 'chat', NULL, '2026-03-06 08:22:14', NULL),
(29, 1, -1, 99, 'chat', NULL, '2026-03-06 08:22:33', NULL),
(30, 1, -1, 98, 'chat', NULL, '2026-03-06 09:03:43', NULL),
(31, 1, -1, 97, 'chat', NULL, '2026-03-06 09:03:57', NULL),
(32, 1, -3, 94, 'summary', NULL, '2026-03-06 09:06:36', NULL),
(33, 1, -1, 93, 'chat', NULL, '2026-03-06 09:08:14', NULL),
(34, 2, -1, 1499, 'chat', NULL, '2026-03-07 06:17:11', NULL),
(35, 2, -1, 1498, 'chat', NULL, '2026-03-07 06:17:47', NULL),
(36, 2, -1, 1497, 'chat', NULL, '2026-03-07 08:33:28', NULL),
(37, 2, -3, 1494, 'summary', NULL, '2026-03-07 08:33:57', NULL),
(38, 2, -3, 1491, 'summary', NULL, '2026-03-07 08:34:08', NULL),
(39, 2, -1, 1490, 'chat', NULL, '2026-03-07 08:40:25', NULL),
(40, 2, -3, 1487, 'summary', NULL, '2026-03-07 08:41:29', NULL),
(41, 2, -3, 1484, 'summary', NULL, '2026-03-07 08:43:38', NULL),
(42, 2, -3, 1481, 'summary', NULL, '2026-03-07 08:43:58', NULL),
(43, 2, -3, 1478, 'summary', NULL, '2026-03-07 08:44:15', NULL),
(44, 2, -1, 1477, 'chat', NULL, '2026-03-07 08:51:22', NULL),
(45, 2, -1, 1476, 'chat', NULL, '2026-03-07 08:53:07', NULL),
(46, 2, -1, 1475, 'chat', NULL, '2026-03-07 08:53:38', NULL),
(47, 2, -1, 1474, 'chat', NULL, '2026-03-07 08:53:44', NULL),
(48, 2, -1, 1473, 'chat', NULL, '2026-03-07 09:03:29', NULL),
(49, 2, -1, 1472, 'chat', NULL, '2026-03-07 09:04:21', NULL),
(50, 2, 300, 1772, 'subscription', NULL, '2026-03-07 09:12:24', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `otps`
--

CREATE TABLE `otps` (
  `id` int(10) UNSIGNED NOT NULL,
  `email` varchar(191) NOT NULL,
  `otp` char(6) NOT NULL,
  `attempts` tinyint(4) NOT NULL DEFAULT 0,
  `expires_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `used` tinyint(1) NOT NULL DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `otps`
--

INSERT INTO `otps` (`id`, `email`, `otp`, `attempts`, `expires_at`, `used`, `created_at`) VALUES
(33, '28@gmail.com', '422346', 1, '2026-03-07 05:48:26', 1, '2026-03-07 05:20:53'),
(34, 'rajt8@yahoo.com', '426721', 0, '2026-03-07 05:26:57', 1, '2026-03-07 05:26:38'),
(35, 'myapp@gmail.com', '805264', 0, '2026-03-07 05:48:57', 1, '2026-03-07 05:48:26'),
(36, 'amit@testing.com', '220203', 0, '2026-03-07 05:57:32', 1, '2026-03-07 05:57:04');

-- --------------------------------------------------------

--
-- Table structure for table `payments`
--

CREATE TABLE `payments` (
  `id` int(10) UNSIGNED NOT NULL,
  `user_id` int(10) UNSIGNED NOT NULL,
  `type` enum('subscription','topup') NOT NULL,
  `plan` varchar(50) DEFAULT NULL,
  `credits_added` int(10) UNSIGNED NOT NULL DEFAULT 0,
  `amount` decimal(8,2) NOT NULL,
  `currency` char(3) NOT NULL DEFAULT 'USD',
  `paypal_txn_id` varchar(100) NOT NULL,
  `status` enum('completed','refunded','failed') NOT NULL DEFAULT 'completed',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `rate_limits`
--

CREATE TABLE `rate_limits` (
  `id` int(10) UNSIGNED NOT NULL,
  `identifier` varchar(191) NOT NULL,
  `action` varchar(50) NOT NULL,
  `hits` int(10) UNSIGNED NOT NULL DEFAULT 1,
  `window_start` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `rate_limits`
--

INSERT INTO `rate_limits` (`id`, `identifier`, `action`, `hits`, `window_start`) VALUES
(104, '103.177.129.58', 'otp_send', 1, '2026-03-07 05:48:26'),
(105, '103.177.129.58', 'otp_verify', 1, '2026-03-07 05:48:57'),
(106, '49.43.5.31', 'otp_send', 1, '2026-03-07 05:57:04'),
(107, '49.43.5.31', 'otp_verify', 1, '2026-03-07 05:57:32'),
(123, '103.177.129.58', 'upload', 2, '2026-03-07 09:01:28'),
(125, '103.177.129.58', 'ai_request', 2, '2026-03-07 09:03:27');

-- --------------------------------------------------------

--
-- Table structure for table `subscriptions`
--

CREATE TABLE `subscriptions` (
  `id` int(10) UNSIGNED NOT NULL,
  `user_id` int(10) UNSIGNED NOT NULL,
  `plan` enum('basic','pro','professional') NOT NULL,
  `status` enum('active','cancelled','expired','paused') NOT NULL DEFAULT 'active',
  `paypal_sub_id` varchar(100) NOT NULL,
  `started_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `renews_at` timestamp NULL DEFAULT NULL,
  `cancelled_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `usage_counters`
--

CREATE TABLE `usage_counters` (
  `id` int(10) UNSIGNED NOT NULL,
  `user_id` int(10) UNSIGNED NOT NULL,
  `month` char(7) NOT NULL,
  `pdfs_uploaded` int(10) UNSIGNED NOT NULL DEFAULT 0,
  `chat_messages` int(10) UNSIGNED NOT NULL DEFAULT 0,
  `summaries` int(10) UNSIGNED NOT NULL DEFAULT 0,
  `qa_questions` int(10) UNSIGNED NOT NULL DEFAULT 0,
  `quizzes` int(10) UNSIGNED NOT NULL DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `bonus_pdfs` int(11) NOT NULL DEFAULT 0,
  `bonus_chats` int(11) NOT NULL DEFAULT 0,
  `bonus_summaries` int(11) NOT NULL DEFAULT 0,
  `bonus_quizzes` int(11) NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `usage_counters`
--

INSERT INTO `usage_counters` (`id`, `user_id`, `month`, `pdfs_uploaded`, `chat_messages`, `summaries`, `qa_questions`, `quizzes`, `created_at`, `updated_at`, `bonus_pdfs`, `bonus_chats`, `bonus_summaries`, `bonus_quizzes`) VALUES
(16, 1, '2026-03', 7, 9, 2, 0, 0, '2026-03-02 08:38:36', '2026-03-06 09:08:14', 0, 0, 0, 0),
(21, 3, '2026-03', 1, 1, 0, 0, 0, '2026-03-02 08:58:00', '2026-03-02 08:58:44', 0, 0, 0, 0),
(23, 2, '2026-03', 8, 16, 10, 0, 1, '2026-03-04 05:13:08', '2026-03-07 09:04:21', 0, 0, 0, 0);

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(10) UNSIGNED NOT NULL,
  `email` varchar(191) NOT NULL,
  `plan` enum('free','basic','pro','professional') NOT NULL DEFAULT 'free',
  `status` enum('active','suspended') NOT NULL DEFAULT 'active',
  `credits` int(10) UNSIGNED NOT NULL DEFAULT 20,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `paypal_subscription_id` varchar(100) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `email`, `plan`, `status`, `credits`, `created_at`, `updated_at`, `paypal_subscription_id`) VALUES
(1, 'amazingpuzzle70@gmail.com', 'basic', 'active', 93, '2026-03-02 05:51:32', '2026-03-06 09:08:14', 'I-L9VBAWGLX15L'),
(2, 'myappstudio28@gmail.com', 'pro', 'active', 1772, '2026-03-02 05:59:53', '2026-03-07 09:12:24', 'I-TRKK6YMC7CPR'),
(3, 'rajthorat28@yahoo.com', 'free', 'active', 19, '2026-03-02 08:56:46', '2026-03-02 08:58:44', NULL),
(4, 'conceptdigitalstore@yahoo.com', 'free', 'active', 20, '2026-03-07 05:09:00', '2026-03-07 05:09:00', NULL),
(5, 'amit@totodreammarketing.com', 'free', 'active', 20, '2026-03-07 05:57:04', '2026-03-07 05:57:04', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `user_sessions`
--

CREATE TABLE `user_sessions` (
  `id` int(10) UNSIGNED NOT NULL,
  `user_id` int(10) UNSIGNED NOT NULL,
  `token` varchar(128) NOT NULL,
  `ip_address` varchar(45) DEFAULT NULL,
  `user_agent` varchar(255) DEFAULT NULL,
  `last_active` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `expires_at` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `user_sessions`
--

INSERT INTO `user_sessions` (`id`, `user_id`, `token`, `ip_address`, `user_agent`, `last_active`, `expires_at`, `created_at`) VALUES
(17, 2, '4639fa85ebed5535322a152d8adf845187dbfa80978779bd9400af2042a72b5908dea1473a6cff2a111e6ee71f85717edf549055ebe4a2b6a1dfd8dbde6416e4', '103.177.129.58', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36 Edg/145.0.0.0', '2026-03-07 13:21:18', '2026-04-06 05:48:57', '2026-03-07 05:48:57'),
(18, 5, '426e7db0dd2912d8dd5547b9e173b207257fac60f350103542edf5ba71fb6a67dc13f53a6acc9064da57117e2ad2a29f39fa724940e667519537abae231afbf0', '49.43.5.31', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', '2026-03-09 17:25:29', '2026-04-06 05:57:32', '2026-03-07 05:57:32');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `credit_ledger`
--
ALTER TABLE `credit_ledger`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_user_id` (`user_id`),
  ADD KEY `idx_created` (`created_at`);

--
-- Indexes for table `otps`
--
ALTER TABLE `otps`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_email_otp` (`email`,`otp`),
  ADD KEY `idx_expires` (`expires_at`);

--
-- Indexes for table `payments`
--
ALTER TABLE `payments`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_user_id` (`user_id`),
  ADD KEY `idx_txn` (`paypal_txn_id`);

--
-- Indexes for table `rate_limits`
--
ALTER TABLE `rate_limits`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uq_identifier_action` (`identifier`,`action`),
  ADD KEY `idx_window` (`window_start`);

--
-- Indexes for table `subscriptions`
--
ALTER TABLE `subscriptions`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_user_id` (`user_id`),
  ADD KEY `idx_paypal_sub` (`paypal_sub_id`);

--
-- Indexes for table `usage_counters`
--
ALTER TABLE `usage_counters`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_user_month` (`user_id`,`month`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uq_email` (`email`);

--
-- Indexes for table `user_sessions`
--
ALTER TABLE `user_sessions`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uq_token` (`token`),
  ADD KEY `idx_user_id` (`user_id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `credit_ledger`
--
ALTER TABLE `credit_ledger`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=51;

--
-- AUTO_INCREMENT for table `otps`
--
ALTER TABLE `otps`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=37;

--
-- AUTO_INCREMENT for table `payments`
--
ALTER TABLE `payments`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `rate_limits`
--
ALTER TABLE `rate_limits`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=126;

--
-- AUTO_INCREMENT for table `subscriptions`
--
ALTER TABLE `subscriptions`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `usage_counters`
--
ALTER TABLE `usage_counters`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=78;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `user_sessions`
--
ALTER TABLE `user_sessions`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=19;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `credit_ledger`
--
ALTER TABLE `credit_ledger`
  ADD CONSTRAINT `fk_ledger_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `payments`
--
ALTER TABLE `payments`
  ADD CONSTRAINT `fk_pay_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `subscriptions`
--
ALTER TABLE `subscriptions`
  ADD CONSTRAINT `fk_sub_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `usage_counters`
--
ALTER TABLE `usage_counters`
  ADD CONSTRAINT `fk_usage_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `user_sessions`
--
ALTER TABLE `user_sessions`
  ADD CONSTRAINT `fk_sessions_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
