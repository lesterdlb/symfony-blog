CREATE DATABASE IF NOT EXISTS `symfony_db` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE `symfony_db`;

DROP TABLE IF EXISTS `doctrine_migration_versions`;
CREATE TABLE `doctrine_migration_versions`
(
    `version`        varchar(191) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL,
    `executed_at`    datetime DEFAULT NULL,
    `execution_time` int      DEFAULT NULL,
    PRIMARY KEY (`version`)
) ENGINE = InnoDB
  DEFAULT CHARSET = utf8mb3
  COLLATE = utf8_unicode_ci;

INSERT INTO `doctrine_migration_versions`
VALUES ('DoctrineMigrations\\Version20220404224936', '2022-04-04 22:49:49', 293);


DROP TABLE IF EXISTS `user`;
CREATE TABLE `user`
(
    `id`       int                                                     NOT NULL AUTO_INCREMENT,
    `email`    varchar(180) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL,
    `roles`    json                                                    NOT NULL,
    `password` varchar(255) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL,
    `name`     varchar(255) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL,
    PRIMARY KEY (`id`),
    UNIQUE KEY `UNIQ_8D93D649E7927C74` (`email`)
) ENGINE = InnoDB
  AUTO_INCREMENT = 25
  DEFAULT CHARSET = utf8mb3
  COLLATE = utf8_unicode_ci;

INSERT INTO `user`
VALUES (1, 'admin@mail.com', '[
  \"ROLE_MODERATOR\"
]', '$2y$13$CZEz.WLxBXbHl/4TkSQ.huqC80h089w069x0/lhEFU8.MobbqDBRG', 'Admin'),
       (2, 'userA@mail.com', '[
         \"ROLE_EDITOR\"
       ]', '$2y$13$a7u5VPfKcmKRIvAKDA743urpsngeLt1j3/Ts7rigGSsUwlYbswaEu', 'User A'),
       (3, 'userB@mail.com', '[
         \"ROLE_EDITOR\"
       ]', '$2y$13$7qq9tOW0PnZ9bhoGgYj.IuoIlkW.Wo.LoTCYV3/LynEIrmazdZsuC', 'User B');

DROP TABLE IF EXISTS `post`;
CREATE TABLE `post`
(
    `id`      int                                                     NOT NULL AUTO_INCREMENT,
    `user_id` int                                                     NOT NULL,
    `title`   varchar(255) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL,
    `date`    date                                                    NOT NULL,
    `content` longtext CHARACTER SET utf8 COLLATE utf8_unicode_ci     NOT NULL,
    `status`  varchar(255) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL,
    PRIMARY KEY (`id`),
    KEY `IDX_5A8A6C8DA76ED395` (`user_id`),
    CONSTRAINT `FK_5A8A6C8DA76ED395` FOREIGN KEY (`user_id`) REFERENCES `user` (`id`)
) ENGINE = InnoDB
  AUTO_INCREMENT = 1016
  DEFAULT CHARSET = utf8mb3
  COLLATE = utf8_unicode_ci;


INSERT INTO `post`
VALUES (503, 1, 'About Schmidt', '2021-08-29', 'Synergized hybrid infrastructure', 'PUBLISHED'),
       (504, 1, 'Hard Ticket to Hawaii', '2021-01-27', 'Operative fault-tolerant access', 'PUBLISHED'),
       (505, 2, 'Cameraman, The', '2021-09-24', 'Expanded system-worthy benchmark', 'REJECTED'),
       (508, 2, 'Cradle Will Rock', '2021-06-23', 'Focused zero defect extranet', 'PUBLISHED'),
       (509, 2, 'Love Exposure (Ai No Mukidashi)', '2021-04-15', 'Optional tangible extranet', 'PUBLISHED'),
       (510, 1, 'Young Unknowns, The', '2020-12-15', 'Progressive optimal superstructure', 'PUBLISHED'),
       (511, 3,
        'Pete Kelly\'s Blues', '2020-12-03', 'Pre-emptive content-based access', 'REJECTED'),
       (513, 3, 'Ants in the Pants', '2021-12-08', 'Pre-emptive logistical matrices', 'REJECTED'),
       (514, 1, 'Alyce Kills', '2021-04-27', 'Switchable user-facing open architecture', 'PUBLISHED'),
       (515, 3, 'To the Limit (Am Limit)', '2021-04-12', 'Focused heuristic hardware', 'DRAFT'),
       (517, 1, 'Ride Beyond Vengeance', '2021-01-23', 'Customizable motivating project', 'PUBLISHED'),
       (1002, 3, 'Henry Poole is Here', '2021-09-13', 'Public-key radical knowledge user', 'REJECTED'),
       (1012, 1, 'New Post', '2022-04-23', 'Content', 'PUBLISHED'),
       (1013, 3, 'User B Post', '2022-04-23', 'Content', 'DRAFT'),
       (1015, 2, 'User A new post', '2022-04-24', 'Content', 'DRAFT');

