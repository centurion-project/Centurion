SET SQL_MODE="NO_AUTO_VALUE_ON_ZERO";
SET foreign_key_checks = 0;

--
-- Base de donn�es: `sam`
--

-- --------------------------------------------------------

--
-- Structure de la table `centurion_content_type`
--

DROP TABLE IF EXISTS `centurion_content_type`;

CREATE TABLE IF NOT EXISTS `centurion_content_type` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 ;

-- --------------------------------------------------------

drop table if exists centurion_navigation;

CREATE TABLE IF NOT EXISTS `centurion_navigation` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `label` varchar(150) DEFAULT NULL,
  `module` varchar(100) DEFAULT NULL,
  `controller` varchar(100) DEFAULT NULL,
  `action` varchar(100) DEFAULT NULL,
  `params` text,
  `permission` varchar(255) DEFAULT NULL,
  `route` varchar(100) DEFAULT NULL,
  `uri` varchar(255) DEFAULT NULL,
  `order` int(11) DEFAULT NULL,
  `is_visible` int(1) NOT NULL DEFAULT '1',
  `is_in_menu` int(1) NOT NULL DEFAULT '1',
  `class` varchar(50) DEFAULT NULL,
  `mptt_lft` int(11) unsigned NOT NULL,
  `mptt_rgt` int(11) unsigned NOT NULL,
  `mptt_level` int(11) unsigned NOT NULL,
  `mptt_tree_id` int(11) unsigned DEFAULT NULL,
  `mptt_parent_id` int(11) unsigned DEFAULT NULL,
  `proxy_model` int(11) unsigned DEFAULT NULL,
  `proxy_pk` int(11) unsigned DEFAULT NULL,
  `can_be_deleted` int(11) unsigned DEFAULT 1,
  `original_id` int(11) DEFAULT NULL,
  `language_id` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `proxy_model` (`proxy_model`,`proxy_pk`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8;


ALTER TABLE  `centurion_navigation` ADD FOREIGN KEY ( `proxy_model` ) REFERENCES  `centurion_content_type` (
`id`
);
alter table centurion_navigation add constraint fk_navigation__navigation_parent_id___navigation__id foreign key (mptt_parent_id)
      references centurion_navigation (id) on delete restrict on update restrict;

ALTER TABLE  `centurion_content_type` ADD INDEX (  `name` );

ALTER TABLE  `centurion_navigation` ADD INDEX (  `order` );
ALTER TABLE  `centurion_navigation` ADD INDEX (  `is_visible` );
ALTER TABLE  `centurion_navigation` ADD INDEX (  `is_in_menu` );
ALTER TABLE  `centurion_navigation` ADD INDEX (  `mptt_lft` );
ALTER TABLE  `centurion_navigation` ADD INDEX (  `mptt_rgt` );
ALTER TABLE  `centurion_navigation` ADD INDEX (  `mptt_level` );
ALTER TABLE  `centurion_navigation` ADD INDEX (  `mptt_tree_id` );
ALTER TABLE  `centurion_navigation` ADD INDEX (  `mptt_parent_id` );
ALTER TABLE  `centurion_navigation` ADD INDEX (  `original_id` ,  `language_id` ) ;
ALTER TABLE  `centurion_navigation` CHANGE  `original_id`  `original_id` INT( 11 ) UNSIGNED NULL DEFAULT NULL;
ALTER TABLE  `centurion_navigation` ADD FOREIGN KEY (  `original_id` ) REFERENCES  `centurion_navigation` (
`id`
);

ALTER TABLE  `centurion_navigation` ADD INDEX (  `can_be_deleted` );

ALTER TABLE  `centurion_navigation` ADD INDEX (  `language_id` );

ALTER TABLE  `centurion_navigation` CHANGE  `language_id`  `language_id` INT( 11 ) UNSIGNED NULL DEFAULT NULL;
ALTER TABLE  `centurion_navigation` ADD FOREIGN KEY (  `language_id` ) REFERENCES  `translation_language` (
`id`
);


ALTER TABLE  `centurion_navigation` ADD FOREIGN KEY (  `language_id` ) REFERENCES  `translation_language` (
`id`
);

