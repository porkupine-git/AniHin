-- AniXoHindi Professional Database Schema
-- Ready for MySQL / MariaDB

CREATE DATABASE IF NOT EXISTS `anixohindi` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE `anixohindi`;

-- Users table (Google & AniList OAuth)
CREATE TABLE IF NOT EXISTS `users` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `oauth_provider` ENUM('google', 'anilist') NOT NULL,
    `oauth_id` VARCHAR(191) NOT NULL,
    `username` VARCHAR(100) NOT NULL,
    `email` VARCHAR(191) NULL,
    `avatar` VARCHAR(255) NULL,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `last_login` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    UNIQUE KEY `unique_oauth` (`oauth_provider`, `oauth_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Anime table
CREATE TABLE IF NOT EXISTS `anime` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `slug` VARCHAR(191) NOT NULL UNIQUE,
    `title` VARCHAR(255) NOT NULL,
    `hindi_title` VARCHAR(255) NULL,
    `japanese_title` VARCHAR(255) NULL,
    `poster` VARCHAR(255) NOT NULL,
    `banner` VARCHAR(255) NOT NULL,
    `synopsis` TEXT NOT NULL,
    `show_type` VARCHAR(20) DEFAULT 'TV',
    `rating` VARCHAR(10) DEFAULT '16+',
    `score` DECIMAL(3, 2) DEFAULT 8.50,
    `release_year` VARCHAR(10) DEFAULT '2024',
    `status` VARCHAR(30) DEFAULT 'Ongoing',
    `is_hindi_dubbed` TINYINT(1) DEFAULT 1,
    `is_subbed` TINYINT(1) DEFAULT 1,
    `genres` VARCHAR(255) NOT NULL,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Episodes table (stores embed URLs only)
CREATE TABLE IF NOT EXISTS `episodes` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `anime_id` INT NOT NULL,
    `episode_number` INT NOT NULL,
    `title` VARCHAR(255) NOT NULL,
    `embed_url` TEXT NOT NULL,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (`anime_id`) REFERENCES `anime`(`id`) ON DELETE CASCADE,
    UNIQUE KEY `unique_ep` (`anime_id`, `episode_number`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Watch History table
CREATE TABLE IF NOT EXISTS `watch_history` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `user_id` INT NOT NULL,
    `anime_id` INT NOT NULL,
    `episode_id` INT NOT NULL,
    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE,
    FOREIGN KEY (`anime_id`) REFERENCES `anime`(`id`) ON DELETE CASCADE,
    FOREIGN KEY (`episode_id`) REFERENCES `episodes`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
