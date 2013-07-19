SET NAMES utf8;
SET FOREIGN_KEY_CHECKS = 0;

-- ----------------------------
--  Table structure for `users_acl_extra`
-- ----------------------------
DROP TABLE IF EXISTS `users_acl_extra`;
CREATE TABLE `users_acl_extra` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `email` varchar(254) NOT NULL,
  `create_hash` char(40) DEFAULT NULL,
  `hash_used` int(10) unsigned DEFAULT '0',
  PRIMARY KEY (`id`),
  UNIQUE KEY `email` (`email`)
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8;

SET FOREIGN_KEY_CHECKS = 1;