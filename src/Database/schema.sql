-- Server version	5.7.16-log

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
-- Table structure for table `kf_answers`
--

DROP TABLE IF EXISTS `kf_answers`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `kf_answers` (
  `answer_id` int(11) NOT NULL AUTO_INCREMENT,
  `object_id` int(11) DEFAULT NULL,
  `object_type` varchar(200) DEFAULT NULL,
  `question_id` int(11) DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`answer_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `kf_answers`
--

LOCK TABLES `kf_answers` WRITE;
/*!40000 ALTER TABLE `kf_answers` DISABLE KEYS */;
/*!40000 ALTER TABLE `kf_answers` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `kf_experiments`
--

DROP TABLE IF EXISTS `kf_experiments`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `kf_experiments` (
  `experiment_id` int(11) NOT NULL AUTO_INCREMENT,
  `uuid` varchar(36) DEFAULT NULL,
  `stage` int(11) DEFAULT NULL,
  `document` json DEFAULT NULL,
  `images` json DEFAULT NULL,
  `show_in` json DEFAULT NULL,
  `tags` json DEFAULT NULL,
  `links` json DEFAULT NULL,
  `funding` json DEFAULT NULL,
  `disabled` tinyint(4) DEFAULT '0',
  `updated_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `settings` json DEFAULT NULL,
  PRIMARY KEY (`experiment_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `kf_experiments`
--

LOCK TABLES `kf_experiments` WRITE;
/*!40000 ALTER TABLE `kf_experiments` DISABLE KEYS */;
/*!40000 ALTER TABLE `kf_experiments` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `kf_groups`
--

DROP TABLE IF EXISTS `kf_groups`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `kf_groups` (
  `id` mediumint(8) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(20) NOT NULL,
  `description` varchar(100) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `kf_groups`
--

LOCK TABLES `kf_groups` WRITE;
/*!40000 ALTER TABLE `kf_groups` DISABLE KEYS */;
INSERT INTO `kf_groups` VALUES (1,'admin','Administrator'),(2,'members','General'),(3,'staff','Platform staff');
/*!40000 ALTER TABLE `kf_groups` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `kf_lang_fields`
--

DROP TABLE IF EXISTS `kf_lang_fields`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `kf_lang_fields` (
  `lang_field_id` int(11) NOT NULL AUTO_INCREMENT,
  `content_key` varchar(200) NOT NULL,
  `lang_code` varchar(2) NOT NULL,
  `object_id` int(200) NOT NULL,
  `object_type` varchar(200) NOT NULL,
  `content` mediumtext NOT NULL,
  `format` varchar(200) NOT NULL,
  `type` varchar(100) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
  PRIMARY KEY (`lang_field_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `kf_lang_fields`
--

LOCK TABLES `kf_lang_fields` WRITE;
/*!40000 ALTER TABLE `kf_lang_fields` DISABLE KEYS */;
/*!40000 ALTER TABLE `kf_lang_fields` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `kf_profiles`
--

DROP TABLE IF EXISTS `kf_profiles`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `kf_profiles` (
  `id` mediumint(8) unsigned NOT NULL AUTO_INCREMENT,
  `user_id` mediumint(8) unsigned DEFAULT NULL,
  `first_name` varchar(50) DEFAULT NULL,
  `last_name` varchar(50) DEFAULT NULL,
  `company` varchar(100) DEFAULT NULL,
  `phone` varchar(20) DEFAULT NULL,
  `document_number` varchar(20) DEFAULT NULL,
  `profile_pic` varchar(200) DEFAULT NULL,
  `description` varchar(400) DEFAULT NULL,
  `content` mediumtext,
  `images` json DEFAULT NULL,
  `links` json DEFAULT NULL,
  `tags` json DEFAULT NULL,
  `document` json DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `id` (`id`),
  KEY `user_id` (`user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `kf_profiles`
--

LOCK TABLES `kf_profiles` WRITE;
/*!40000 ALTER TABLE `kf_profiles` DISABLE KEYS */;
/*!40000 ALTER TABLE `kf_profiles` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `kf_profiles_skills`
--

DROP TABLE IF EXISTS `kf_profiles_skills`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `kf_profiles_skills` (
  `profile_skill_id` int(11) NOT NULL AUTO_INCREMENT,
  `skill_id` int(11) NOT NULL,
  `profile_id` int(11) NOT NULL,
  PRIMARY KEY (`profile_skill_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `kf_profiles_skills`
--

LOCK TABLES `kf_profiles_skills` WRITE;
/*!40000 ALTER TABLE `kf_profiles_skills` DISABLE KEYS */;
/*!40000 ALTER TABLE `kf_profiles_skills` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `kf_questions`
--

DROP TABLE IF EXISTS `kf_questions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `kf_questions` (
  `question_id` int(11) NOT NULL AUTO_INCREMENT,
  `object_id` varchar(200) DEFAULT NULL,
  `object_type` varchar(200) DEFAULT NULL,
  `context` varchar(300) DEFAULT NULL,
  `scope` varchar(100) DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`question_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `kf_questions`
--

LOCK TABLES `kf_questions` WRITE;
/*!40000 ALTER TABLE `kf_questions` DISABLE KEYS */;
/*!40000 ALTER TABLE `kf_questions` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `kf_rate_limited_events`
--

DROP TABLE IF EXISTS `kf_rate_limited_events`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `kf_rate_limited_events` (
  `rate_limited_event_id` int(11) NOT NULL AUTO_INCREMENT,
  `key` varchar(400) DEFAULT NULL,
  `set_time` int(11) DEFAULT NULL,
  `expire_time` int(11) DEFAULT NULL,
  `allowed_occurances` int(11) DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`rate_limited_event_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `kf_rate_limited_events`
--

LOCK TABLES `kf_rate_limited_events` WRITE;
/*!40000 ALTER TABLE `kf_rate_limited_events` DISABLE KEYS */;
/*!40000 ALTER TABLE `kf_rate_limited_events` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `kf_relationships`
--

DROP TABLE IF EXISTS `kf_relationships`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `kf_relationships` (
  `relationship_id` int(11) NOT NULL AUTO_INCREMENT,
  `follower_id` int(11) NOT NULL,
  `follower_table` varchar(100) NOT NULL,
  `leader_id` int(11) NOT NULL,
  `leader_table` varchar(100) NOT NULL,
  `relationship_type` varchar(200) NOT NULL,
  `created_at` datetime DEFAULT NULL,
  `updated_at` datetime DEFAULT NULL,
  `deleted_at` datetime DEFAULT NULL,
  PRIMARY KEY (`relationship_id`),
  KEY `relationship_id` (`relationship_id`),
  KEY `follower_id` (`follower_id`),
  KEY `follower_table` (`follower_table`),
  KEY `leader_id` (`leader_id`),
  KEY `leader_table` (`leader_table`),
  KEY `relationship_type` (`relationship_type`(191))
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `kf_relationships`
--

LOCK TABLES `kf_relationships` WRITE;
/*!40000 ALTER TABLE `kf_relationships` DISABLE KEYS */;
/*!40000 ALTER TABLE `kf_relationships` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `kf_roles`
--

DROP TABLE IF EXISTS `kf_roles`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `kf_roles` (
  `role_id` int(11) NOT NULL AUTO_INCREMENT,
  `role_name` varchar(200) NOT NULL,
  `description` mediumtext NOT NULL,
  `project_id` int(11) NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  PRIMARY KEY (`role_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `kf_roles`
--

LOCK TABLES `kf_roles` WRITE;
/*!40000 ALTER TABLE `kf_roles` DISABLE KEYS */;
/*!40000 ALTER TABLE `kf_roles` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `kf_settings`
--

DROP TABLE IF EXISTS `kf_settings`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `kf_settings` (
  `settings_id` int(11) NOT NULL AUTO_INCREMENT,
  `variable` varchar(200) NOT NULL,
  `value` varchar(200) NOT NULL,
  PRIMARY KEY (`settings_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `kf_settings`
--

LOCK TABLES `kf_settings` WRITE;
/*!40000 ALTER TABLE `kf_settings` DISABLE KEYS */;
/*!40000 ALTER TABLE `kf_settings` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `kf_skill_categories`
--

DROP TABLE IF EXISTS `kf_skill_categories`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `kf_skill_categories` (
  `skill_category_id` int(11) NOT NULL AUTO_INCREMENT,
  `category_name` varchar(300) NOT NULL,
  PRIMARY KEY (`skill_category_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `kf_skill_categories`
--

LOCK TABLES `kf_skill_categories` WRITE;
/*!40000 ALTER TABLE `kf_skill_categories` DISABLE KEYS */;
/*!40000 ALTER TABLE `kf_skill_categories` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `kf_skills`
--

DROP TABLE IF EXISTS `kf_skills`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `kf_skills` (
  `skill_id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(300) NOT NULL,
  `skill_category_id` int(11) DEFAULT NULL,
  PRIMARY KEY (`skill_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `kf_skills`
--

LOCK TABLES `kf_skills` WRITE;
/*!40000 ALTER TABLE `kf_skills` DISABLE KEYS */;
/*!40000 ALTER TABLE `kf_skills` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `kf_tags`
--

DROP TABLE IF EXISTS `kf_tags`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `kf_tags` (
  `tag_id` int(11) NOT NULL AUTO_INCREMENT,
  `label_language` json DEFAULT NULL,
  `active` tinyint(4) DEFAULT '1',
  `updated_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`tag_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `kf_tags`
--

LOCK TABLES `kf_tags` WRITE;
/*!40000 ALTER TABLE `kf_tags` DISABLE KEYS */;
/*!40000 ALTER TABLE `kf_tags` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `kf_team_members`
--

DROP TABLE IF EXISTS `kf_team_members`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `kf_team_members` (
  `team_member_id` int(11) NOT NULL AUTO_INCREMENT,
  `uuid` varchar(36) DEFAULT NULL,
  `object_type` varchar(45) DEFAULT NULL,
  `object_id` int(11) DEFAULT NULL,
  `user_id` int(11) DEFAULT NULL,
  `roles` json DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`team_member_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `kf_team_members`
--

LOCK TABLES `kf_team_members` WRITE;
/*!40000 ALTER TABLE `kf_team_members` DISABLE KEYS */;
/*!40000 ALTER TABLE `kf_team_members` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `kf_users`
--

DROP TABLE IF EXISTS `kf_users`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `kf_users` (
  `id` mediumint(8) unsigned NOT NULL AUTO_INCREMENT,
  `group_id` mediumint(8) unsigned NOT NULL,
  `ip_address` char(16) NOT NULL,
  `username` varchar(40) NOT NULL,
  `password` varchar(255) NOT NULL,
  `hash_type` varchar(35) NOT NULL DEFAULT 'ion',
  `salt` varchar(40) DEFAULT NULL,
  `email` varchar(254) NOT NULL,
  `activation_code` varchar(40) DEFAULT NULL,
  `forgotten_password_code` varchar(40) DEFAULT NULL,
  `remember_code` varchar(40) DEFAULT NULL,
  `created_on` int(11) unsigned NOT NULL,
  `last_login` int(11) unsigned DEFAULT NULL,
  `active` tinyint(1) unsigned DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT NULL,
  `permissions` mediumtext,
  `settings` json DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `kf_users`
--

LOCK TABLES `kf_users` WRITE;
/*!40000 ALTER TABLE `kf_users` DISABLE KEYS */;
/*!40000 ALTER TABLE `kf_users` ENABLE KEYS */;
UNLOCK TABLES;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed on 2017-09-28 12:32:42
