SET SQL_MODE="NO_AUTO_VALUE_ON_ZERO";

SET foreign_key_checks = 0;
--
-- Base de données: `centurion`
--

--
-- Contenu de la table `centurion_navigation`
--


INSERT INTO `centurion_navigation` (`id`, `label`, `module`, `controller`, `action`, `params`, `permission`, `route`, `uri`, `order`, `is_visible`, `is_in_menu`, `class`, `mptt_lft`, `mptt_rgt`, `mptt_level`, `mptt_tree_id`, `mptt_parent_id`, `proxy_model`, `proxy_pk`, `can_be_deleted`, `original_id`, `language_id`) VALUES
(1, 'Users', 'auth', 'admin-user', NULL, NULL, NULL, 'default', NULL, 1, 1, 1, 'sqdsdqsdqsd', 18, 27, 1, 5, 12, NULL, NULL, 1, NULL, NULL),
(2, 'Manage group permissions', 'auth', 'admin-group-permission', NULL, NULL, NULL, 'default', NULL, 3, 1, 1, NULL, 19, 20, 2, 5, 1, NULL, NULL, 1, NULL, NULL),
(3, 'Pages', 'admin', 'admin-navigation', NULL, NULL, NULL, NULL, NULL, 2, 1, 1, NULL, 34, 39, 1, 5, 12, NULL, NULL, 1, NULL, NULL),
(4, 'Settings', 'user', 'admin-profile', NULL, NULL, NULL, 'default', NULL, 3, 1, 1, NULL, 40, 49, 1, 5, 12, NULL, NULL, 1, NULL, NULL),
(5, 'Template', 'cms', 'admin-flatpage-template', NULL, NULL, NULL, NULL, NULL, NULL, 1, 1, NULL, 37, 38, 2, 5, 3, NULL, NULL, 1, NULL, NULL),
(7, 'Cache', 'admin', 'index', 'cache', NULL, NULL, NULL, NULL, 2, 1, 1, NULL, 41, 44, 2, 5, 4, NULL, NULL, 1, NULL, NULL),
(8, 'Clear cache', 'admin', 'index', 'clear-cache', NULL, NULL, NULL, NULL, NULL, 1, 1, NULL, 42, 43, 3, 5, 7, NULL, NULL, 1, NULL, NULL),
(11, 'Translation', 'translation', 'admin', NULL, NULL, NULL, NULL, NULL, NULL, 1, 1, NULL, 45, 46, 2, 5, 4, NULL, NULL, 1, NULL, NULL),
(12, 'Backoffice', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1, 1, NULL, 1, 50, 0, 5, NULL, NULL, NULL, 0, NULL, NULL),
(13, 'Error', 'admin', 'index', 'log', NULL, NULL, NULL, NULL, NULL, 1, 1, NULL, 47, 48, 2, 5, 4, NULL, NULL, 1, NULL, NULL),
(14, 'Pages unactivated', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1, 1, 'unactived', 1, 26, 0, 20, NULL, NULL, NULL, 0, NULL, NULL),
(16, 'Frontoffice', NULL, NULL, NULL, NULL, 'all', NULL, NULL, NULL, 1, 1, NULL, 1, 86, 0, 18, NULL, NULL, NULL, 0, NULL, NULL),
(20, 'Contents', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1, 1, NULL, 2, 17, 1, 5, 12, NULL, NULL, 1, NULL, NULL),
(105, 'Pages', 'admin', 'admin-navigation', NULL, NULL, NULL, NULL, NULL, NULL, 1, 1, NULL, 35, 36, 2, 5, 3, NULL, NULL, 1, NULL, NULL),
(118, 'Permission', 'auth', 'admin-permission', NULL, NULL, NULL, NULL, NULL, NULL, 1, 1, NULL, 21, 22, 2, 5, 1, NULL, NULL, 1, NULL, NULL),
(119, 'Script permissions', 'auth', 'admin-script-permission', NULL, NULL, NULL, NULL, NULL, NULL, 1, 1, NULL, 23, 24, 2, 5, 1, NULL, NULL, 1, NULL, NULL),
(121, 'Group permission', 'auth', 'admin-group-permission', NULL, NULL, NULL, NULL, NULL, NULL, 1, 1, NULL, 25, 26, 2, 5, 1, NULL, NULL, 1, NULL, 2);




INSERT INTO `centurion_content_type` (`id`, `name`) VALUES
(2, 'Cms_Model_DbTable_Flatpage'),
(3, 'Cms_Model_DbTable_Row_Flatpage'),
(8, 'Core_Model_DbTable_Navigation');
