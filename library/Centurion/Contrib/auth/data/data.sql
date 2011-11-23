-- MySQL dump 10.13  Distrib 5.1.41, for apple-darwin10.2.0 (i386)
--
-- Host: localhost    Database: centurion_testing
-- ------------------------------------------------------
-- Server version	5.1.40-log

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8 */;
/*!40103 SET @OLD_TIME_ZONE=@@TIME_ZONE */;
/*!40103 SET TIME_ZONE='+00:00' */;
/*!40014 SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;

--
-- Dumping data for table `auth_user`
--

/*!40000 ALTER TABLE `auth_user` DISABLE KEYS */;
INSERT INTO `auth_user` (`id`,`username`,`password`,`email`,`salt`,`algorithm`,`can_be_deleted`,`is_active`,`is_super_admin`,`created_at`,`last_login`,`updated_at`,`user_parent_id`)
VALUES
	(1, 'admin', '692a868f105e05b46f6f798ef3ec8554b7d658f3', 'ce@octaveoctave.com', 'a73056c2bbdca2d4148049493e296e70', 'sha1', 0, 1, 1, '2009-11-23 11:36:31', '0000-00-00 00:00:00', '2009-11-29 18:18:23', NULL),
	(2, 'anonymous', NULL, NULL, NULL, '', 0, 1, 0, '0000-00-00 00:00:00', '2010-11-29 03:17:19', '2010-11-29 15:17:19', NULL);
/*!40000 ALTER TABLE `auth_user` ENABLE KEYS */;

--
-- Dumping data for table `auth_user_permission`
--

/*!40000 ALTER TABLE `auth_user_permission` DISABLE KEYS */;
INSERT INTO `auth_user_permission` VALUES (1,1);
/*!40000 ALTER TABLE `auth_user_permission` ENABLE KEYS */;

--
-- Dumping data for table `auth_belong`
--

/*!40000 ALTER TABLE `auth_belong` DISABLE KEYS */;
INSERT INTO `auth_belong` VALUES (1,1);
INSERT INTO `auth_belong` (`user_id`, `group_id`) VALUES
(2, 5);
/*!40000 ALTER TABLE `auth_belong` ENABLE KEYS */;

--
-- Dumping data for table `auth_group`
--

/*!40000 ALTER TABLE `auth_group` DISABLE KEYS */;
INSERT INTO `auth_group` VALUES (1,'Administrator','Administrator',4),(2,'Webmaster','Webmaster',3),(3,'User','User',NULL),(4,'Moderator','Moderator',2),(5,'Anonymous','Anonymous', NULL);
/*!40000 ALTER TABLE `auth_group` ENABLE KEYS */;

--
-- Dumping data for table `auth_group_permission`
--

/*!40000 ALTER TABLE `auth_group_permission` DISABLE KEYS */;
INSERT INTO `auth_group_permission` VALUES (1,1),(1,3),(1,4),(1,5),(1,6),(1,7),(1,8);
/*!40000 ALTER TABLE `auth_group_permission` ENABLE KEYS */;
INSERT INTO `auth_group_permission` (`group_id`, `permission_id`) VALUES
(1, 9),
(2, 9),
(3, 9),
(4, 9),
(5, 9);

--
-- Dumping data for table `auth_permission`
--

/*!40000 ALTER TABLE `auth_permission` DISABLE KEYS */;
INSERT INTO `auth_permission` VALUES (1,'auth_admin-user_get','View an user'),(3,'auth_admin-user_post','Create an user'),(4,'auth_admin-user_put','Update user information'),(5,'auth_admin-user_index','View user index'),(6,'auth_admin-user_list','View user list'),(7,'auth_admin-group-permission_index','View permission per group'),(8,'auth_admin-group-permission_switch','Switch permission'),(9,'all','All');
/*!40000 ALTER TABLE `auth_permission` ENABLE KEYS */;