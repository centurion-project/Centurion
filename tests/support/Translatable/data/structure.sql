-- phpMyAdmin SQL Dump
-- version 3.4.5deb1
-- http://www.phpmyadmin.net
--
-- Client: localhost
-- Généré le : Lun 26 Mars 2012 à 14:06
-- Version du serveur: 5.1.61
-- Version de PHP: 5.3.6-13ubuntu3.6

SET FOREIGN_KEY_CHECKS=0;
SET SQL_MODE="NO_AUTO_VALUE_ON_ZERO";
SET AUTOCOMMIT=0;
START TRANSACTION;
SET time_zone = "+00:00";

--
-- Base de données: `cfao_test`
--

-- --------------------------------------------------------

--
-- Structure de la table `test_m_translation_first_model`
--

DROP TABLE IF EXISTS `test_m_translation_first_model`;
CREATE TABLE IF NOT EXISTS `test_m_translation_first_model` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `original_id` int(11) DEFAULT NULL,
  `language_id` int(10) unsigned NOT NULL,
  `title` varchar(100) NOT NULL,
  `content` text,
  `second1_id` int(11) DEFAULT NULL,
  `second2_id` int(11) DEFAULT NULL,
  `is_active` int(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `original_id` (`original_id`),
  KEY `language_id` (`language_id`),
  KEY `second1_id` (`second1_id`),
  KEY `second2_id` (`second2_id`),
  KEY `is_active` (`is_active`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Structure de la table `test_m_translation_fourth_model`
--

DROP TABLE IF EXISTS `test_m_translation_fourth_model`;
CREATE TABLE IF NOT EXISTS `test_m_translation_fourth_model` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `original_id` int(11) DEFAULT NULL,
  `language_id` int(10) unsigned NOT NULL,
  `title` varchar(100) NOT NULL,
  `content` text,
  `first_id` int(11) DEFAULT NULL,
  `is_active` int(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `original_id` (`original_id`),
  KEY `language_id` (`language_id`),
  KEY `first_id` (`first_id`),
  KEY `is_active` (`is_active`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Structure de la table `test_m_translation_second_model`
--

DROP TABLE IF EXISTS `test_m_translation_second_model`;
CREATE TABLE IF NOT EXISTS `test_m_translation_second_model` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `original_id` int(11) DEFAULT NULL,
  `language_id` int(10) unsigned NOT NULL,
  `title` varchar(100) NOT NULL,
  `content` text,
  `is_active` int(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `original_id` (`original_id`),
  KEY `language_id` (`language_id`),
  KEY `is_active` (`is_active`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Structure de la table `test_m_translation_third_model`
--

DROP TABLE IF EXISTS `test_m_translation_third_model`;
CREATE TABLE IF NOT EXISTS `test_m_translation_third_model` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `original_id` int(11) DEFAULT NULL,
  `language_id` int(10) unsigned NOT NULL,
  `title` varchar(100) NULL,
  `content` text,
  `first_id` int(11) DEFAULT NULL,
  `is_active` int(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `original_id` (`original_id`),
  KEY `language_id` (`language_id`),
  KEY `first_id` (`first_id`),
  KEY `is_active` (`is_active`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

--
-- Contraintes pour les tables exportées
--

--
-- Contraintes pour la table `test_m_translation_first_model`
--
ALTER TABLE `test_m_translation_first_model`
  ADD CONSTRAINT `test_m_translation_first_model_ibfk_3` FOREIGN KEY (`second1_id`) REFERENCES `test_m_translation_second_model` (`id`),
  ADD CONSTRAINT `test_m_translation_first_model_ibfk_4` FOREIGN KEY (`second2_id`) REFERENCES `test_m_translation_second_model` (`id`),
  ADD CONSTRAINT `test_m_translation_first_model_ibfk_1` FOREIGN KEY (`original_id`) REFERENCES `test_m_translation_first_model` (`id`),
  ADD CONSTRAINT `test_m_translation_first_model_ibfk_2` FOREIGN KEY (`language_id`) REFERENCES `translation_language` (`id`);

--
-- Contraintes pour la table `test_m_translation_fourth_model`
--
ALTER TABLE `test_m_translation_fourth_model`
  ADD CONSTRAINT `test_m_translation_fourth_model_ibfk_2` FOREIGN KEY (`language_id`) REFERENCES `translation_language` (`id`),
  ADD CONSTRAINT `test_m_translation_fourth_model_ibfk_1` FOREIGN KEY (`original_id`) REFERENCES `test_m_translation_fourth_model` (`id`),
  ADD CONSTRAINT `test_m_translation_fourth_model_ibfk_4` FOREIGN KEY (`first_id`) REFERENCES `test_m_translation_first_model` (`id`);

--
-- Contraintes pour la table `test_m_translation_second_model`
--
ALTER TABLE `test_m_translation_second_model`
  ADD CONSTRAINT `test_m_translation_second_model_ibfk_2` FOREIGN KEY (`language_id`) REFERENCES `translation_language` (`id`),
  ADD CONSTRAINT `test_m_translation_second_model_ibfk_1` FOREIGN KEY (`original_id`) REFERENCES `test_m_translation_second_model` (`id`);

--
-- Contraintes pour la table `test_m_translation_third_model`
--
ALTER TABLE `test_m_translation_third_model`
  ADD CONSTRAINT `test_m_translation_third_model_ibfk_2` FOREIGN KEY (`language_id`) REFERENCES `translation_language` (`id`),
  ADD CONSTRAINT `test_m_translation_third_model_ibfk_1` FOREIGN KEY (`original_id`) REFERENCES `test_m_translation_third_model` (`id`),
  ADD CONSTRAINT `test_m_translation_third_model_ibfk_3` FOREIGN KEY (`first_id`) REFERENCES `test_m_translation_first_model` (`id`);

SET FOREIGN_KEY_CHECKS=1;
COMMIT;
