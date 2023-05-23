create  DATABASE next_task;

use next_task;

CREATE TABLE `users` (
  `id` int AUTO_INCREMENT NOT NULL,
  `name` varchar(255) NOT NULL,
  `email` varchar(255) NOT NULL,
  `password` varchar(255) NOT NULL,
  `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
);
CREATE TABLE `tokens_users` (
  `id` INT AUTO_INCREMENT NOT NULL,
  `user_id` INT NOT NULL,
  `token` VARCHAR(255) NOT NULL,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  CONSTRAINT `fk_user_tokens_users`
    FOREIGN KEY (`user_id`)
    REFERENCES `users` (`id`)
    ON DELETE CASCADE
);

CREATE TABLE `projects`(
  `id` int AUTO_INCREMENT NOT NULL,
  `description` varchar(255) NOT NULL,
  `color` varchar(255),
  `users_id` int NOT NULL,
  PRIMARY KEY (`id`)
);

CREATE TABLE `sections` (
  `id` int AUTO_INCREMENT NOT NULL,
  `description` varchar(255) NOT NULL,
  `projects_id` int NOT NULL,
  PRIMARY KEY (`id`)
);

CREATE TABLE `tasks` (
  `id` int AUTO_INCREMENT NOT NULL,
  `name` varchar(255) NOT NULL,
  `description` varchar(255),
  `color` varchar(255),
  `expiration_date` date, 
  `sections_id` int NOT NULL,
  `priorities_id` int NOT NULL,
  PRIMARY KEY (`id`)
);

CREATE TABLE `priorities` (
  `id` int AUTO_INCREMENT NOT NULL,
  `description` varchar(255) NOT NULL,
  PRIMARY KEY (`id`)
);

ALTER TABLE `projects` ADD FOREIGN KEY (`users_id`) REFERENCES `users` (`id`);

ALTER TABLE `sections` ADD FOREIGN KEY (`projects_id`) REFERENCES `projects` (`id`);

ALTER TABLE `tasks` ADD FOREIGN KEY (`sections_id`) REFERENCES `sections` (`id`);

ALTER TABLE `tasks` ADD FOREIGN KEY (`priorities_id`) REFERENCES `priorities` (`id`);

INSERT INTO priorities (id, description)
VALUES (1,'Baixa'),(2,'Média'),(3,'Alta');
