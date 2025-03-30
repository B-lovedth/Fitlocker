-- Remove existing tables if they exist (correct order for dependencies)
-- Remove existing tables if they exist (correct order for dependencies)
DROP TABLE IF EXISTS `customers`;
DROP TABLE IF EXISTS `families`;
DROP TABLE IF EXISTS `users`;

-- Create tables in proper dependency order
CREATE TABLE `users` (
  `user_id` int(11) NOT NULL AUTO_INCREMENT,
  `username` varchar(50) NOT NULL,
  `email` varchar(100) NOT NULL,
  `password` varchar(255) NOT NULL,
  `created_at` datetime DEFAULT current_timestamp(),
  PRIMARY KEY (`user_id`),
  UNIQUE KEY `username` (`username`),
  UNIQUE KEY `email` (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE `families` (
  `family_id` int(11) NOT NULL AUTO_INCREMENT,
  `family_name` varchar(100) NOT NULL,
  `family_address` varchar(255) DEFAULT NULL,
  `user_id` int(11) NOT NULL,
  `created_at` datetime DEFAULT current_timestamp(),
  PRIMARY KEY (`family_id`),
  KEY `user_id` (`user_id`),
  CONSTRAINT `families_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

DROP TABLE IF EXISTS `families`;
DROP TABLE IF EXISTS `users`;

-- Create tables in proper dependency order
CREATE TABLE `users` (
  `user_id` int(11) NOT NULL AUTO_INCREMENT,
  `username` varchar(50) NOT NULL,
  `email` varchar(100) NOT NULL,
  `password` varchar(255) NOT NULL,
  `created_at` datetime DEFAULT current_timestamp(),
  PRIMARY KEY (`user_id`),
  UNIQUE KEY `username` (`username`),
  UNIQUE KEY `email` (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE `families` (
  `family_id` int(11) NOT NULL AUTO_INCREMENT,
  `family_name` varchar(100) NOT NULL,
  `family_address` varchar(255) DEFAULT NULL,
  `user_id` int(11) NOT NULL,
  `created_at` datetime DEFAULT current_timestamp(),
  PRIMARY KEY (`family_id`),
  KEY `user_id` (`user_id`),
  CONSTRAINT `families_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE `customers` (
  `customer_id` int(11) NOT NULL AUTO_INCREMENT,
  `customer_id` int(11) NOT NULL AUTO_INCREMENT,
  `first_name` varchar(50) NOT NULL,
  `last_name` varchar(50) NOT NULL,
  `address` varchar(255) DEFAULT NULL,
  `phone` int(12) DEFAULT NULL,
  `age` int(11) DEFAULT NULL,
  `gender` enum('male','female','other') NOT NULL,
  `height` decimal(5,2) DEFAULT NULL,
  `length` decimal(5,2) DEFAULT NULL,
  `chest` decimal(5,2) DEFAULT NULL,
  `waist` decimal(5,2) DEFAULT NULL,
  `hip` decimal(5,2) DEFAULT NULL,
  `sleeve` decimal(5,2) DEFAULT NULL,
  `inseam` decimal(5,2) DEFAULT NULL,
  `outseam` decimal(5,2) DEFAULT NULL,
  `shoulder` decimal(5,2) DEFAULT NULL,
  `short_length` decimal(5,2) DEFAULT NULL,
  `family_id` int(11) DEFAULT NULL,
  `user_id` int(11) NOT NULL,
  `created_at` datetime DEFAULT current_timestamp(),
  `family_id` int(11) DEFAULT NULL,
  `user_id` int(11) NOT NULL,
  `created_at` datetime DEFAULT current_timestamp(),
  PRIMARY KEY (`customer_id`),
  KEY `family_id` (`family_id`),
  KEY `user_id` (`user_id`),
  CONSTRAINT `customers_ibfk_1` FOREIGN KEY (`family_id`) REFERENCES `families` (`family_id`),
  CONSTRAINT `customers_ibfk_2` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;