-- phpMyAdmin SQL Dump
-- version 3.3.1
-- http://www.phpmyadmin.net
--
-- Host: localhost
-- Generation Time: Sep 09, 2010 at 04:18 PM
-- Server version: 5.0.75
-- PHP Version: 5.2.6-3ubuntu4.5

SET FOREIGN_KEY_CHECKS=0;
SET SQL_MODE="NO_AUTO_VALUE_ON_ZERO";

--
-- Database: `octaveoctave`
--

-- --------------------------------------------------------

--
-- Table structure for table `media_duplicate`
--

DROP TABLE IF EXISTS `media_duplicate`;
CREATE TABLE IF NOT EXISTS `media_duplicate` (
  `id` int(11) unsigned NOT NULL auto_increment,
  `file_id` varchar(100) NOT NULL,
  `adapter` varchar(50) NOT NULL,
  `params` text NOT NULL,
  `dest` varchar(255) NOT NULL,
  PRIMARY KEY  (`id`),
  KEY `file_id` (`file_id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `media_file`
--

DROP TABLE IF EXISTS `media_file`;
CREATE TABLE IF NOT EXISTS `media_file` (
  `id` varchar(100) NOT NULL,
  `file_id` varchar(32) DEFAULT NULL,
  `local_filename` varchar(255) DEFAULT NULL,
  `mime` varchar(255) NOT NULL,
  `filename` varchar(255) NOT NULL,
  `name` varchar(255) NOT NULL,
  `filesize` int(11) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
  `use_count` int(11) unsigned NOT NULL DEFAULT '0',
  `user_id` int(11) unsigned DEFAULT NULL,
  `proxy_model` varchar(150) DEFAULT NULL,
  `proxy_pk` int(11) unsigned DEFAULT NULL,
  `belong_model` varchar(150) DEFAULT NULL,
  `belong_pk` int(11) unsigned DEFAULT NULL,
  `description` text,
  `sha1` varchar(40) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `fk_file__user_id___user__id` (`user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

ALTER TABLE  `media_file` ADD  `delete_original` INT( 1 ) NOT NULL DEFAULT  '1';

-- --------------------------------------------------------

--
-- Table structure for table `media_image`
--

DROP TABLE IF EXISTS `media_image`;
CREATE TABLE IF NOT EXISTS `media_image` (
  `id` int(11) unsigned NOT NULL auto_increment,
  `width` int(11) unsigned NOT NULL,
  `height` int(11) unsigned NOT NULL,
  PRIMARY KEY  (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `media_multiupload_ticket`
--

DROP TABLE IF EXISTS `media_multiupload_ticket`;
CREATE TABLE IF NOT EXISTS `media_multiupload_ticket` (
  `id` int(11) unsigned NOT NULL auto_increment,
  `ticket` varchar(32) NOT NULL,
  `expire` timestamp NOT NULL default CURRENT_TIMESTAMP on update CURRENT_TIMESTAMP,
  `proxy_model_id` int(11) unsigned default NULL,
  `proxy_pk` int(11) unsigned default NULL,
  `form_class_model_id` int(11) unsigned NOT NULL,
  `element_name` varchar(255) NOT NULL,
  `values` text default NULL,
  PRIMARY KEY  (`id`),
  KEY `proxy_model` (`proxy_model_id`),
  KEY `form_class_model` (`form_class_model_id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `media_video`
--

DROP TABLE IF EXISTS `media_video`;
CREATE TABLE IF NOT EXISTS `media_video` (
  `id` int(11) unsigned NOT NULL auto_increment,
  `width` int(11) unsigned NOT NULL,
  `height` int(11) unsigned NOT NULL,
  `duration` int(11) unsigned NOT NULL,
  PRIMARY KEY  (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `media_duplicate`
--
ALTER TABLE `media_duplicate`
  ADD CONSTRAINT `media_duplicate_ibfk_1` FOREIGN KEY (`file_id`) REFERENCES `media_file` (`id`);

--
-- Constraints for table `media_file`
--
ALTER TABLE `media_file`
  ADD CONSTRAINT `fk_file__user_id___user__id` FOREIGN KEY (`user_id`) REFERENCES `auth_user` (`id`);
SET FOREIGN_KEY_CHECKS=1;
ALTER TABLE  `media_multiupload_ticket` ADD INDEX (  `proxy_pk` );
ALTER TABLE  `media_file` ADD INDEX (  `proxy_model` );
ALTER TABLE  `media_file` ADD INDEX (  `proxy_pk` );
ALTER TABLE  `media_file` ADD INDEX (  `belong_model` );
ALTER TABLE  `media_file` ADD INDEX (  `belong_pk` );
ALTER TABLE  `media_file` ADD INDEX (  `file_id` );
ALTER TABLE  `media_file` ADD INDEX (  `local_filename` );


ALTER TABLE  `media_multiupload_ticket` ADD FOREIGN KEY (  `proxy_model_id` ) REFERENCES  `centurion_content_type` (
`id`
);

ALTER TABLE  `media_multiupload_ticket` ADD FOREIGN KEY (  `form_class_model_id` ) REFERENCES  `centurion_content_type` (
`id`
);
