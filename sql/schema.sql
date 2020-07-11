BEGIN;

CREATE DATABASE IF NOT EXISTS `gingerberry`;
USE `gingerberry`;

CREATE TABLE IF NOT EXISTS `presentations` (
    `id` INT PRIMARY KEY AUTO_INCREMENT,
    `presentation_name` varchar(256) NOT NULL
) CHARSET = utf8mb4;

CREATE TABLE IF NOT EXISTS `slides` (
    `id` INT PRIMARY KEY AUTO_INCREMENT,
    `presentation_id` INT NOT NULL,
    `title` VARCHAR(256) NOT NULL,
    `start_sec` INT
) CHARSET = utf8mb4;

ALTER TABLE `slides`
ADD FOREIGN KEY (`presentation_id`) REFERENCES presentations(`id`) ON DELETE CASCADE;

COMMIT;
