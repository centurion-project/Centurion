-- phpMyAdmin SQL Dump
-- version 3.3.2
-- http://www.phpmyadmin.net
--
-- Serveur: localhost
-- Généré le : Ven 12 Novembre 2010 à 18:21
-- Version du serveur: 5.1.41
-- Version de PHP: 5.3.1

SET FOREIGN_KEY_CHECKS=0;
SET SQL_MODE="NO_AUTO_VALUE_ON_ZERO";

--
-- Base de données: `centurion`
--

-- --------------------------------------------------------

--
-- Structure de la table `translation_language`
--

DROP TABLE IF EXISTS `translation_language`;
CREATE TABLE IF NOT EXISTS `translation_language` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `locale` varchar(20) NOT NULL,
  `name` varchar(255) NOT NULL,
  `flag` varchar(255) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8;

--
-- Dumping data for table `translation_language`
--

INSERT INTO `translation_language` (`id`, `locale`, `name`, `flag`) VALUES
(1, 'fr', '', '');

-- --------------------------------------------------------

--
-- Structure de la table `translation_tag`
--

DROP TABLE IF EXISTS `translation_tag`;
CREATE TABLE IF NOT EXISTS `translation_tag` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `tag` varchar(255) CHARACTER SET utf8 NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Structure de la table `translation_tag_uid`
--

DROP TABLE IF EXISTS `translation_tag_uid`;
CREATE TABLE IF NOT EXISTS `translation_tag_uid` (
  `uid_id` int(11) unsigned NOT NULL,
  `tag_id` int(11) unsigned NOT NULL,
  PRIMARY KEY  (`uid_id`,`tag_id`),
  KEY `FK_translation_tag_uid2` (`tag_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Structure de la table `translation_translation`
--

DROP TABLE IF EXISTS `translation_translation`;
CREATE TABLE IF NOT EXISTS `translation_translation` (
  `translation` text COLLATE utf8_bin NOT NULL,
  `uid_id` int(11) unsigned NOT NULL,
  `language_id` int(11) unsigned NOT NULL,
  PRIMARY KEY (`uid_id`,`language_id`),
  KEY `uid_id` (`uid_id`),
  KEY `language_id` (`language_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_bin;

-- --------------------------------------------------------

--
-- Structure de la table `translation_uid`
--

DROP TABLE IF EXISTS `translation_uid`;
CREATE TABLE IF NOT EXISTS `translation_uid` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `uid` text COLLATE utf8_bin NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_bin;

--
-- Contraintes pour les tables exportées
--

--
-- Constraints for table `translation_tag_uid`
--
ALTER TABLE `translation_tag_uid`
  ADD CONSTRAINT `FK_translation_tag_uid2` FOREIGN KEY (`tag_id`) REFERENCES `translation_tag` (`id`),
  ADD CONSTRAINT `FK_translation_tag_uid` FOREIGN KEY (`uid_id`) REFERENCES `translation_uid` (`id`);

--
-- Constraints for table `translation_translation`
--
ALTER TABLE `translation_translation`
  ADD CONSTRAINT `translation_translation_ibfk_1` FOREIGN KEY (`uid_id`) REFERENCES `translation_uid` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `translation_translation_ibfk_2` FOREIGN KEY (`language_id`) REFERENCES `translation_language` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;
