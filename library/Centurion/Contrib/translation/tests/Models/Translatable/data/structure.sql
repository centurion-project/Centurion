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
-- Structure de la table `test_m_seo_custo_model`
--

DROP TABLE IF EXISTS `test_m_seo_custo_model`;
CREATE TABLE IF NOT EXISTS `test_m_seo_custo_model` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `title` varchar(100) NOT NULL,
  `content` text,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=5 ;

-- --------------------------------------------------------

--
-- Structure de la table `test_m_seo_first_model`
--

DROP TABLE IF EXISTS `test_m_seo_first_model`;
CREATE TABLE IF NOT EXISTS `test_m_seo_first_model` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `title` varchar(100) NOT NULL,
  `body` text,
  `chapo` text,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=6 ;

-- --------------------------------------------------------

--
-- Structure de la table `test_m_seo_second_model`
--

DROP TABLE IF EXISTS `test_m_seo_second_model`;
CREATE TABLE IF NOT EXISTS `test_m_seo_second_model` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `title` varchar(100) NOT NULL,
  `content` text,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=3 ;

-- --------------------------------------------------------

--
-- Structure de la table `test_m_seo_translatable_model`
--

DROP TABLE IF EXISTS `test_m_seo_translatable_model`;
CREATE TABLE IF NOT EXISTS `test_m_seo_translatable_model` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `original_id` int(11) DEFAULT NULL,
  `language_id` int(10) unsigned NOT NULL,
  `title` varchar(100) DEFAULT NULL,
  `content` text,
  `is_active` int(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `original_id` (`original_id`),
  KEY `language_id` (`language_id`),
  KEY `is_active` (`is_active`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

--
-- Contraintes pour les tables exportées
--

--
-- Contraintes pour la table `seo_meta`
--
ALTER TABLE `seo_meta`
  ADD CONSTRAINT `seo_meta_ibfk_1` FOREIGN KEY (`language_id`) REFERENCES `translation_language` (`id`),
  ADD CONSTRAINT `seo_meta_ibfk_2` FOREIGN KEY (`model_id`) REFERENCES `centurion_content_type` (`id`);
SET FOREIGN_KEY_CHECKS=1;

SET FOREIGN_KEY_CHECKS=1;
COMMIT;
