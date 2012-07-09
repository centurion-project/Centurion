-- phpMyAdmin SQL Dump
-- version 3.3.1
-- http://www.phpmyadmin.net
--
-- Host: localhost
-- Generation Time: Oct 25, 2010 at 06:12 PM
-- Server version: 5.0.75
-- PHP Version: 5.2.6-3ubuntu4.5

SET FOREIGN_KEY_CHECKS=0;
SET SQL_MODE="NO_AUTO_VALUE_ON_ZERO";

--
-- Database: `octaveoctave`
--

-- --------------------------------------------------------

--
-- Table structure for table `cms_flatpage`
--

DROP TABLE IF EXISTS `cms_flatpage`;
CREATE TABLE IF NOT EXISTS `cms_flatpage` (
  `id` int(11) unsigned NOT NULL auto_increment,
  `title` varchar(100) NOT NULL,
  `slug` varchar(100) default NULL,
  `description` varchar(255) default NULL,
  `keywords` varchar(255) default NULL,
  `body` text,
  `url` varchar(100) default NULL,
  `flatpage_template_id` int(11) unsigned NOT NULL,
  `published_at` timestamp NOT NULL default '0000-00-00 00:00:00',
  `created_at` timestamp NOT NULL default '0000-00-00 00:00:00',
  `updated_at` timestamp NOT NULL default '0000-00-00 00:00:00',
  `is_published` int(1) unsigned default '0',
  `mptt_lft` int(11) unsigned NOT NULL,
  `mptt_rgt` int(11) unsigned NOT NULL,
  `mptt_level` int(11) unsigned NOT NULL,
  `mptt_tree_id` int(11) unsigned default NULL,
  `mptt_parent_id` int(11) unsigned default NULL,
  `original_id` int(11) unsigned default NULL,
  `language_id` int(11) unsigned default NULL,
  `forward_url` varchar(255) default NULL,
  `flatpage_type` int(1) NOT NULL default '1',
  `route` varchar(50) default NULL,
  `class` varchar(255) default NULL,
  `cover_id` varchar(100) default NULL,
  PRIMARY KEY  (`id`),
  KEY `flatpage_template_id` (`flatpage_template_id`),
  KEY `flatpage_cover_id` (`cover_id`),
  KEY `mptt_parent_id` (`mptt_parent_id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `cms_flatpage_template`
--

DROP TABLE IF EXISTS `cms_flatpage_template`;
CREATE TABLE IF NOT EXISTS `cms_flatpage_template` (
  `id` int(11) unsigned NOT NULL auto_increment,
  `name` varchar(50) NOT NULL,
  `view_script` varchar(50) NOT NULL,
  `class` varchar(50) default NULL,
  PRIMARY KEY  (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 ;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `cms_flatpage`
--
ALTER TABLE `cms_flatpage`
  ADD CONSTRAINT `cms_flatpage_ibfk_1` FOREIGN KEY (`mptt_parent_id`) REFERENCES `cms_flatpage` (`id`),
  ADD CONSTRAINT `cms_flatpage__banner_id___media_file__id` FOREIGN KEY (`cover_id`) REFERENCES `media_file` (`id`),
  ADD CONSTRAINT `cms_flatpage__flatpage_template_id___cms_flatpage_template__id` FOREIGN KEY (`flatpage_template_id`) REFERENCES `cms_flatpage_template` (`id`) ON UPDATE CASCADE;
SET FOREIGN_KEY_CHECKS=1;


ALTER TABLE  `cms_flatpage` ADD INDEX (  `slug` );
ALTER TABLE  `cms_flatpage` ADD INDEX (  `is_published` );
ALTER TABLE  `cms_flatpage` ADD INDEX (  `mptt_lft` );
ALTER TABLE  `cms_flatpage` ADD INDEX (  `mptt_rgt` );
ALTER TABLE  `cms_flatpage` ADD INDEX (  `mptt_level` );
ALTER TABLE  `cms_flatpage` ADD INDEX (  `mptt_tree_id` );
ALTER TABLE  `cms_flatpage` ADD INDEX (  `original_id` ,  `language_id` ) ;

ALTER TABLE  `cms_flatpage` ADD INDEX (  `language_id` );


ALTER TABLE  `cms_flatpage` ADD FOREIGN KEY (  `original_id` ) REFERENCES  `cms_flatpage` (
`id`
);

ALTER TABLE  `cms_flatpage` ADD FOREIGN KEY (  `language_id` ) REFERENCES  `translation_language` (
`id`
);
