set foreign_key_checks = 0;

drop table if exists user_profile;

CREATE TABLE `user_profile` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `user_id` int(11) unsigned NOT NULL,
  `nickname` varchar(50),
  `about` text,
  `website` varchar(150) DEFAULT NULL,
  `created_at` timestamp DEFAULT '0000-00-00 00:00:00',
  `updated_at` timestamp DEFAULT '0000-00-00 00:00:00',
  `avatar_id` varchar(100) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `fk_profile__user_id___user__id` (`user_id`),
  KEY `fk_profile__avatar_id___file__id` (`avatar_id`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8;

alter table user_profile
    add CONSTRAINT `fk_profile__user_id___user__id` FOREIGN KEY (`user_id`) REFERENCES `auth_user` (`id`);

alter table user_profile
    add CONSTRAINT `fk_profile__avatar_id___file__id` FOREIGN KEY (`avatar_id`) REFERENCES `media_file` (`id`);
