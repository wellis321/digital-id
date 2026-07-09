-- Test database schema fixture for CI and local test setup.
-- Generated from the working dev database (mysqldump --no-data) on 2026-07-09.
--
-- Why this file exists: the repo has three other schema sources
-- (shared-auth/migrations/core_schema.sql + sql/schema.sql, sql/complete_schema.sql,
-- and ~15 standalone sql/*.sql migration files) and none of them currently produce a
-- schema matching the real database - each is missing tables/columns the others have.
-- This dump is the actual, verified-working structure and is what CI and
-- tests/setup-test-db.php should be built from until those are reconciled.

--
-- Host: 127.0.0.1    Database: digital_ids
-- ------------------------------------------------------
-- Server version	8.0.44

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!50503 SET NAMES utf8mb4 */;
/*!40103 SET @OLD_TIME_ZONE=@@TIME_ZONE */;
/*!40103 SET TIME_ZONE='+00:00' */;
/*!40014 SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;

--
-- Table structure for table `check_in_sessions`
--

DROP TABLE IF EXISTS `check_in_sessions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `check_in_sessions` (
  `id` int NOT NULL AUTO_INCREMENT,
  `organisation_id` int NOT NULL,
  `session_name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `session_type` enum('fire_drill','fire_alarm','safety_meeting','emergency') COLLATE utf8mb4_unicode_ci NOT NULL,
  `started_by` int NOT NULL,
  `started_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `ended_at` timestamp NULL DEFAULT NULL,
  `location_id` int DEFAULT NULL,
  `location_name` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `metadata` json DEFAULT NULL,
  `microsoft_365_synced` tinyint(1) DEFAULT '0',
  `sharepoint_list_id` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `teams_channel_id` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `started_by` (`started_by`),
  KEY `idx_organisation` (`organisation_id`),
  KEY `idx_started_at` (`started_at`),
  KEY `idx_ended_at` (`ended_at`),
  CONSTRAINT `check_in_sessions_ibfk_1` FOREIGN KEY (`organisation_id`) REFERENCES `organisations` (`id`) ON DELETE CASCADE,
  CONSTRAINT `check_in_sessions_ibfk_2` FOREIGN KEY (`started_by`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `check_ins`
--

DROP TABLE IF EXISTS `check_ins`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `check_ins` (
  `id` int NOT NULL AUTO_INCREMENT,
  `employee_id` int NOT NULL,
  `check_in_type` enum('meeting','fire_drill','safety','door_access','lone_working','late_work') COLLATE utf8mb4_unicode_ci NOT NULL,
  `location_id` int DEFAULT NULL,
  `location_name` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `checked_in_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `checked_out_at` timestamp NULL DEFAULT NULL,
  `metadata` json DEFAULT NULL,
  `session_id` int DEFAULT NULL,
  `check_in_method` enum('qr_scan','manual','api') COLLATE utf8mb4_unicode_ci DEFAULT 'manual',
  `location_lat` decimal(10,8) DEFAULT NULL,
  `location_lng` decimal(11,8) DEFAULT NULL,
  `device_info` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_employee` (`employee_id`),
  KEY `idx_check_in_type` (`check_in_type`),
  KEY `idx_checked_in_at` (`checked_in_at`),
  KEY `idx_session_id` (`session_id`),
  CONSTRAINT `check_ins_ibfk_1` FOREIGN KEY (`employee_id`) REFERENCES `employees` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_check_ins_session` FOREIGN KEY (`session_id`) REFERENCES `check_in_sessions` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `digital_id_cards`
--

DROP TABLE IF EXISTS `digital_id_cards`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `digital_id_cards` (
  `id` int NOT NULL AUTO_INCREMENT,
  `employee_id` int NOT NULL,
  `qr_token` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `nfc_token` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `qr_token_expires_at` timestamp NULL DEFAULT NULL,
  `nfc_token_expires_at` timestamp NULL DEFAULT NULL,
  `issued_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `expires_at` timestamp NULL DEFAULT NULL,
  `is_revoked` tinyint(1) DEFAULT '0',
  `revoked_at` timestamp NULL DEFAULT NULL,
  `revoked_by` int DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `qr_token` (`qr_token`),
  UNIQUE KEY `nfc_token` (`nfc_token`),
  KEY `revoked_by` (`revoked_by`),
  KEY `idx_employee` (`employee_id`),
  KEY `idx_qr_token` (`qr_token`),
  KEY `idx_nfc_token` (`nfc_token`),
  KEY `idx_expires_at` (`expires_at`),
  KEY `idx_is_revoked` (`is_revoked`),
  CONSTRAINT `digital_id_cards_ibfk_1` FOREIGN KEY (`employee_id`) REFERENCES `employees` (`id`) ON DELETE CASCADE,
  CONSTRAINT `digital_id_cards_ibfk_2` FOREIGN KEY (`revoked_by`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `employees`
--

DROP TABLE IF EXISTS `employees`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `employees` (
  `id` int NOT NULL AUTO_INCREMENT,
  `user_id` int NOT NULL,
  `organisation_id` int NOT NULL,
  `employee_reference` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `display_reference` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `employee_number` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `photo_path` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `photo_approval_status` enum('pending','approved','rejected','none') COLLATE utf8mb4_unicode_ci DEFAULT 'none',
  `photo_pending_path` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'Path to uploaded photo awaiting approval',
  `photo_rejection_reason` text COLLATE utf8mb4_unicode_ci COMMENT 'Reason if photo was rejected',
  `photo_approved_at` timestamp NULL DEFAULT NULL,
  `photo_approved_by` int DEFAULT NULL,
  `id_card_data` json DEFAULT NULL,
  `is_active` tinyint(1) DEFAULT '1',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `staff_service_person_id` int DEFAULT NULL COMMENT 'Reference to Staff Service people.id - links Digital ID employee to Staff Service person record',
  `last_synced_from_staff_service` timestamp NULL DEFAULT NULL COMMENT 'Timestamp of last successful sync from Staff Service',
  `signature_url` varchar(500) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'Cached signature URL from Staff Service',
  `staff_service_photo_url` varchar(500) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'Cached photo URL from Staff Service (absolute URL requiring API key to fetch)',
  PRIMARY KEY (`id`),
  UNIQUE KEY `unique_org_employee_ref` (`organisation_id`,`employee_reference`),
  UNIQUE KEY `unique_org_display_ref` (`organisation_id`,`display_reference`),
  KEY `idx_user` (`user_id`),
  KEY `idx_organisation` (`organisation_id`),
  KEY `idx_employee_reference` (`employee_reference`),
  KEY `idx_display_reference` (`display_reference`),
  KEY `idx_employee_number` (`employee_number`),
  KEY `idx_photo_approval_status` (`photo_approval_status`),
  KEY `fk_photo_approved_by` (`photo_approved_by`),
  KEY `idx_staff_service_person_id` (`staff_service_person_id`),
  CONSTRAINT `employees_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  CONSTRAINT `employees_ibfk_2` FOREIGN KEY (`organisation_id`) REFERENCES `organisations` (`id`) ON DELETE CASCADE,
  CONSTRAINT `employees_ibfk_3` FOREIGN KEY (`photo_approved_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  CONSTRAINT `fk_photo_approved_by` FOREIGN KEY (`photo_approved_by`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `entra_sync`
--

DROP TABLE IF EXISTS `entra_sync`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `entra_sync` (
  `id` int NOT NULL AUTO_INCREMENT,
  `employee_id` int NOT NULL,
  `organisation_id` int NOT NULL,
  `entra_user_id` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `last_synced_at` timestamp NULL DEFAULT NULL,
  `sync_status` enum('active','pending','failed','disabled') COLLATE utf8mb4_unicode_ci DEFAULT 'pending',
  `sync_error` text COLLATE utf8mb4_unicode_ci,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `unique_entra_user` (`entra_user_id`),
  KEY `idx_employee` (`employee_id`),
  KEY `idx_organisation` (`organisation_id`),
  KEY `idx_sync_status` (`sync_status`),
  CONSTRAINT `entra_sync_ibfk_1` FOREIGN KEY (`employee_id`) REFERENCES `employees` (`id`) ON DELETE CASCADE,
  CONSTRAINT `entra_sync_ibfk_2` FOREIGN KEY (`organisation_id`) REFERENCES `organisations` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `microsoft_365_sync_log`
--

DROP TABLE IF EXISTS `microsoft_365_sync_log`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `microsoft_365_sync_log` (
  `id` int NOT NULL AUTO_INCREMENT,
  `organisation_id` int NOT NULL,
  `sync_type` enum('check_in','session','attendance_report') COLLATE utf8mb4_unicode_ci NOT NULL,
  `entity_id` int NOT NULL,
  `entity_type` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `sync_status` enum('pending','success','failed') COLLATE utf8mb4_unicode_ci DEFAULT 'pending',
  `sync_error` text COLLATE utf8mb4_unicode_ci,
  `synced_at` timestamp NULL DEFAULT NULL,
  `microsoft_365_id` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_organisation` (`organisation_id`),
  KEY `idx_sync_status` (`sync_status`),
  KEY `idx_entity` (`entity_type`,`entity_id`),
  CONSTRAINT `microsoft_365_sync_log_ibfk_1` FOREIGN KEY (`organisation_id`) REFERENCES `organisations` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `organisation_settings`
--

DROP TABLE IF EXISTS `organisation_settings`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `organisation_settings` (
  `id` int NOT NULL AUTO_INCREMENT,
  `organisation_id` int NOT NULL,
  `setting_key` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `setting_value` text COLLATE utf8mb4_unicode_ci,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `unique_org_setting` (`organisation_id`,`setting_key`),
  KEY `idx_organisation_id` (`organisation_id`),
  KEY `idx_setting_key` (`setting_key`),
  CONSTRAINT `organisation_settings_ibfk_1` FOREIGN KEY (`organisation_id`) REFERENCES `organisations` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=13 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `organisational_unit_members`
--

DROP TABLE IF EXISTS `organisational_unit_members`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `organisational_unit_members` (
  `id` int NOT NULL AUTO_INCREMENT,
  `unit_id` int NOT NULL,
  `user_id` int NOT NULL,
  `role` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT 'member',
  `joined_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `unique_unit_member` (`unit_id`,`user_id`),
  KEY `idx_unit` (`unit_id`),
  KEY `idx_user` (`user_id`),
  CONSTRAINT `organisational_unit_members_ibfk_1` FOREIGN KEY (`unit_id`) REFERENCES `organisational_units` (`id`) ON DELETE CASCADE,
  CONSTRAINT `organisational_unit_members_ibfk_2` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `organisational_units`
--

DROP TABLE IF EXISTS `organisational_units`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `organisational_units` (
  `id` int NOT NULL AUTO_INCREMENT,
  `organisation_id` int NOT NULL,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `unit_type` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `description` text COLLATE utf8mb4_unicode_ci,
  `parent_unit_id` int DEFAULT NULL,
  `manager_user_id` int DEFAULT NULL,
  `display_order` int DEFAULT '0',
  `is_active` tinyint(1) DEFAULT '1',
  `metadata` json DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_organisation` (`organisation_id`),
  KEY `idx_parent_unit` (`parent_unit_id`),
  KEY `idx_manager` (`manager_user_id`),
  CONSTRAINT `organisational_units_ibfk_1` FOREIGN KEY (`organisation_id`) REFERENCES `organisations` (`id`) ON DELETE CASCADE,
  CONSTRAINT `organisational_units_ibfk_3` FOREIGN KEY (`parent_unit_id`) REFERENCES `organisational_units` (`id`) ON DELETE SET NULL,
  CONSTRAINT `organisational_units_ibfk_4` FOREIGN KEY (`manager_user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `organisations`
--

DROP TABLE IF EXISTS `organisations`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `organisations` (
  `id` int NOT NULL AUTO_INCREMENT,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `contract_number_prefix` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'Custom prefix for auto-generated contract numbers (e.g. SCC-)',
  `domain` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `company_registration_number` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'Companies House registration number',
  `care_inspectorate_registration` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'Care Inspectorate (Scotland) or CQC (England) registration number',
  `charity_number` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'Charity Commission or OSCR registered charity number',
  `vat_number` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'VAT registration number',
  `registered_address` text COLLATE utf8mb4_unicode_ci COMMENT 'Registered office address',
  `trading_address` text COLLATE utf8mb4_unicode_ci COMMENT 'Trading / operational address if different from registered address',
  `phone` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'Main organisation telephone number',
  `website` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'Organisation website URL',
  `care_inspectorate_rating` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'Most recent Care Inspectorate / CQC rating (e.g. Excellent, Good)',
  `last_inspection_date` date DEFAULT NULL COMMENT 'Date of most recent regulatory inspection',
  `main_contact_name` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'Primary contact person full name',
  `main_contact_email` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'Primary contact person email address',
  `main_contact_phone` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'Primary contact person telephone number',
  `geographic_coverage` text COLLATE utf8mb4_unicode_ci COMMENT 'Geographic areas the organisation operates in',
  `service_types` text COLLATE utf8mb4_unicode_ci COMMENT 'Types of care services provided',
  `languages_spoken` text COLLATE utf8mb4_unicode_ci COMMENT 'Languages spoken by staff, relevant for tender submissions',
  `specialist_expertise` text COLLATE utf8mb4_unicode_ci COMMENT 'Specialist areas of expertise (e.g. dementia, learning disabilities)',
  `seats_allocated` int NOT NULL DEFAULT '0',
  `logo_path` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `seats_used` int NOT NULL DEFAULT '0',
  `person_singular` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT 'person',
  `person_plural` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT 'people',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `entra_enabled` tinyint(1) DEFAULT '0',
  `entra_tenant_id` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `entra_client_id` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `reference_prefix` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'Prefix for display references (e.g., SAMH)',
  `reference_pattern` enum('incremental','random_alphanumeric','custom') COLLATE utf8mb4_unicode_ci DEFAULT 'incremental' COMMENT 'Pattern for generating display references',
  `reference_start_number` int DEFAULT '1' COMMENT 'Starting number for incremental references',
  `reference_digits` int DEFAULT '6' COMMENT 'Number of digits for incremental references',
  `m365_sharepoint_site_url` varchar(500) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `m365_sharepoint_list_id` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `m365_teams_channel_id` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `m365_power_automate_webhook_url` varchar(500) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `m365_sync_enabled` tinyint(1) DEFAULT '0',
  PRIMARY KEY (`id`),
  UNIQUE KEY `domain` (`domain`),
  KEY `idx_domain` (`domain`),
  KEY `idx_care_inspectorate_reg` (`care_inspectorate_registration`),
  KEY `idx_company_reg` (`company_registration_number`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `people`
--

DROP TABLE IF EXISTS `people`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `people` (
  `id` int NOT NULL AUTO_INCREMENT,
  `organisation_id` int NOT NULL,
  `person_type` enum('staff','person_we_support') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'staff',
  `user_id` int DEFAULT NULL,
  `first_name` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `last_name` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `email` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `phone` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `date_of_birth` date DEFAULT NULL,
  `employee_reference` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `nhs_number` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `is_active` tinyint(1) DEFAULT '1',
  `photo_path` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `photo_approval_status` enum('approved','pending','rejected') COLLATE utf8mb4_unicode_ci DEFAULT 'approved',
  `photo_pending_path` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `unique_org_employee_ref` (`organisation_id`,`employee_reference`),
  KEY `idx_organisation` (`organisation_id`),
  KEY `idx_user` (`user_id`),
  KEY `idx_person_type` (`person_type`),
  KEY `idx_employee_reference` (`employee_reference`),
  KEY `idx_is_active` (`is_active`),
  CONSTRAINT `people_ibfk_1` FOREIGN KEY (`organisation_id`) REFERENCES `organisations` (`id`) ON DELETE CASCADE,
  CONSTRAINT `people_ibfk_2` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `person_organisational_units`
--

DROP TABLE IF EXISTS `person_organisational_units`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `person_organisational_units` (
  `id` int NOT NULL AUTO_INCREMENT,
  `person_id` int NOT NULL,
  `organisational_unit_id` int NOT NULL,
  `role_in_unit` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT 'member',
  `is_primary` tinyint(1) DEFAULT '0',
  `start_date` date DEFAULT NULL,
  `end_date` date DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `unique_person_unit` (`person_id`,`organisational_unit_id`),
  KEY `idx_person` (`person_id`),
  KEY `idx_organisational_unit` (`organisational_unit_id`),
  KEY `idx_is_primary` (`is_primary`),
  CONSTRAINT `person_organisational_units_ibfk_1` FOREIGN KEY (`person_id`) REFERENCES `people` (`id`) ON DELETE CASCADE,
  CONSTRAINT `person_organisational_units_ibfk_2` FOREIGN KEY (`organisational_unit_id`) REFERENCES `organisational_units` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `roles`
--

DROP TABLE IF EXISTS `roles`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `roles` (
  `id` int NOT NULL AUTO_INCREMENT,
  `name` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `description` text COLLATE utf8mb4_unicode_ci,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `name` (`name`)
) ENGINE=InnoDB AUTO_INCREMENT=8 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `staff_profiles`
--

DROP TABLE IF EXISTS `staff_profiles`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `staff_profiles` (
  `id` int NOT NULL AUTO_INCREMENT,
  `person_id` int NOT NULL,
  `job_title` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `employment_start_date` date DEFAULT NULL,
  `employment_end_date` date DEFAULT NULL,
  `line_manager_id` int DEFAULT NULL,
  `emergency_contact_name` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `emergency_contact_phone` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `notes` text COLLATE utf8mb4_unicode_ci,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `unique_person` (`person_id`),
  KEY `idx_line_manager` (`line_manager_id`),
  CONSTRAINT `staff_profiles_ibfk_1` FOREIGN KEY (`person_id`) REFERENCES `people` (`id`) ON DELETE CASCADE,
  CONSTRAINT `staff_profiles_ibfk_2` FOREIGN KEY (`line_manager_id`) REFERENCES `people` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `user_roles`
--

DROP TABLE IF EXISTS `user_roles`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `user_roles` (
  `id` int NOT NULL AUTO_INCREMENT,
  `user_id` int NOT NULL,
  `role_id` int NOT NULL,
  `assigned_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `assigned_by` int DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `unique_user_role` (`user_id`,`role_id`),
  KEY `assigned_by` (`assigned_by`),
  KEY `idx_user` (`user_id`),
  KEY `idx_role` (`role_id`),
  CONSTRAINT `user_roles_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  CONSTRAINT `user_roles_ibfk_2` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`) ON DELETE CASCADE,
  CONSTRAINT `user_roles_ibfk_3` FOREIGN KEY (`assigned_by`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=16 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `users`
--

DROP TABLE IF EXISTS `users`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `users` (
  `id` int NOT NULL AUTO_INCREMENT,
  `organisation_id` int DEFAULT NULL,
  `email` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `password_hash` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `first_name` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `last_name` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `is_active` tinyint(1) DEFAULT '1',
  `email_verified` tinyint(1) DEFAULT '0',
  `verification_token` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `verification_token_expires_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `last_login` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `unique_email` (`email`),
  KEY `idx_organisation` (`organisation_id`),
  KEY `idx_email` (`email`),
  KEY `idx_verification_token` (`verification_token`),
  CONSTRAINT `users_ibfk_1` FOREIGN KEY (`organisation_id`) REFERENCES `organisations` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=9 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `verification_logs`
--

DROP TABLE IF EXISTS `verification_logs`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `verification_logs` (
  `id` int NOT NULL AUTO_INCREMENT,
  `id_card_id` int DEFAULT NULL,
  `employee_id` int NOT NULL,
  `verification_type` enum('visual','qr','nfc') COLLATE utf8mb4_unicode_ci NOT NULL,
  `verified_by` int DEFAULT NULL,
  `verified_by_ip` varchar(45) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `verified_by_device` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `verification_result` enum('success','failed','expired','revoked') COLLATE utf8mb4_unicode_ci NOT NULL,
  `verified_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `location_lat` decimal(10,8) DEFAULT NULL,
  `location_lng` decimal(11,8) DEFAULT NULL,
  `notes` text COLLATE utf8mb4_unicode_ci,
  PRIMARY KEY (`id`),
  KEY `verified_by` (`verified_by`),
  KEY `idx_id_card` (`id_card_id`),
  KEY `idx_employee` (`employee_id`),
  KEY `idx_verification_type` (`verification_type`),
  KEY `idx_verification_result` (`verification_result`),
  KEY `idx_verified_at` (`verified_at`),
  CONSTRAINT `verification_logs_ibfk_1` FOREIGN KEY (`id_card_id`) REFERENCES `digital_id_cards` (`id`) ON DELETE SET NULL,
  CONSTRAINT `verification_logs_ibfk_2` FOREIGN KEY (`employee_id`) REFERENCES `employees` (`id`) ON DELETE CASCADE,
  CONSTRAINT `verification_logs_ibfk_3` FOREIGN KEY (`verified_by`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping routines for database 'digital_ids'
--
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed on 2026-07-09 20:26:27
