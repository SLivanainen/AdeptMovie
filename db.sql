-- Adept Cinema MySQL Database Export
-- Compatible with MySQL 5.7+ / 8.0+ / MariaDB

CREATE DATABASE IF NOT EXISTS `adept_cinema` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE `adept_cinema`;

-- Categories Table
CREATE TABLE IF NOT EXISTS `categories` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `name` VARCHAR(100) NOT NULL,
  `slug` VARCHAR(100) NOT NULL,
  `display_order` INT DEFAULT 0,
  `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Movies Table
CREATE TABLE IF NOT EXISTS `movies` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `title` VARCHAR(255) NOT NULL,
  `category_id` INT NOT NULL,
  `poster_url` TEXT NOT NULL,
  `backdrop_url` TEXT,
  `description` TEXT NOT NULL,
  `rating` DECIMAL(3, 1) DEFAULT 7.5,
  `release_year` INT NOT NULL,
  `duration` VARCHAR(50) DEFAULT '2h 10m',
  `watch_link` TEXT NOT NULL,
  `featured` INT DEFAULT 0,
  `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
  INDEX (`category_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Banners Table
CREATE TABLE IF NOT EXISTS `banners` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `title` VARCHAR(255) NOT NULL,
  `tagline` VARCHAR(255),
  `image_url` TEXT NOT NULL,
  `movie_id` INT NULL,
  `button_link` TEXT,
  `is_active` INT DEFAULT 1,
  `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Users Table
CREATE TABLE IF NOT EXISTS `users` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `username` VARCHAR(100) NOT NULL UNIQUE,
  `email` VARCHAR(150) NOT NULL UNIQUE,
  `password` VARCHAR(255) NOT NULL,
  `role` VARCHAR(20) DEFAULT 'user',
  `avatar` VARCHAR(255),
  `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Watchlist Table
CREATE TABLE IF NOT EXISTS `watchlist` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `user_id` INT NOT NULL,
  `movie_id` INT NOT NULL,
  `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
  INDEX (`user_id`),
  INDEX (`movie_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Initial Seed Data
INSERT INTO `categories` (`name`, `slug`, `display_order`) VALUES
('Trending Now', 'trending-now', 1),
('Action & Thrillers', 'action-thrillers', 2),
('Sci-Fi & Cyberpunk', 'sci-fi-cyberpunk', 3),
('Top Rated Masterpieces', 'top-rated', 4),
('Anime & Animation', 'anime-animation', 5),
('Horror & Suspense', 'horror-suspense', 6);

INSERT INTO `users` (`username`, `email`, `password`, `role`, `avatar`) VALUES
('admin', 'admin@adeptcinema.com', '$2y$10$0p2dJbM0h6.zT1s0tXqT6O89q56yE95F01p2D0q69jK.u0lZ5gU9G', 'admin', 'https://images.unsplash.com/photo-1535713875002-d1d0cf377fde?w=150'),
('demouser', 'user@adeptcinema.com', '$2y$10$0p2dJbM0h6.zT1s0tXqT6O89q56yE95F01p2D0q69jK.u0lZ5gU9G', 'user', 'https://images.unsplash.com/photo-1534528741775-53994a69daeb?w=150');
