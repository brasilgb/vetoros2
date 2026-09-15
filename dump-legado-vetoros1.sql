-- MySQL dump 10.13  Distrib 8.0.19, for Win64 (x86_64)
--
-- Host: 172.20.70.200    Database: saasos
-- ------------------------------------------------------
-- Server version	11.8.6-MariaDB-0+deb13u1 from Debian

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
-- Table structure for table `account_payable_logs`
--

DROP TABLE IF EXISTS `account_payable_logs`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `account_payable_logs` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `tenant_id` bigint(20) unsigned NOT NULL,
  `account_payable_id` bigint(20) unsigned NOT NULL,
  `user_id` bigint(20) unsigned DEFAULT NULL,
  `action` varchar(80) NOT NULL,
  `data` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`data`)),
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `account_payable_logs_tenant_id_foreign` (`tenant_id`),
  KEY `account_payable_logs_user_id_foreign` (`user_id`),
  KEY `account_payable_logs_account_payable_id_created_at_index` (`account_payable_id`,`created_at`),
  CONSTRAINT `account_payable_logs_account_payable_id_foreign` FOREIGN KEY (`account_payable_id`) REFERENCES `accounts_payable` (`id`) ON DELETE CASCADE,
  CONSTRAINT `account_payable_logs_tenant_id_foreign` FOREIGN KEY (`tenant_id`) REFERENCES `tenants` (`id`) ON DELETE CASCADE,
  CONSTRAINT `account_payable_logs_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `accounts_payable`
--

DROP TABLE IF EXISTS `accounts_payable`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `accounts_payable` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `tenant_id` bigint(20) unsigned NOT NULL,
  `bill_number` int(10) unsigned DEFAULT NULL,
  `supplier_name` varchar(255) DEFAULT NULL,
  `source_type` varchar(40) DEFAULT NULL,
  `source_id` bigint(20) unsigned DEFAULT NULL,
  `description` varchar(255) NOT NULL,
  `category` varchar(120) DEFAULT NULL,
  `total_amount` decimal(10,2) NOT NULL,
  `paid_amount` decimal(10,2) NOT NULL DEFAULT 0.00,
  `balance_amount` decimal(10,2) NOT NULL,
  `due_date` date DEFAULT NULL,
  `status` varchar(20) NOT NULL DEFAULT 'pending',
  `payment_method` varchar(40) DEFAULT NULL,
  `last_paid_at` timestamp NULL DEFAULT NULL,
  `notes` text DEFAULT NULL,
  `created_by` bigint(20) unsigned DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `accounts_payable_created_by_foreign` (`created_by`),
  KEY `accounts_payable_tenant_id_status_index` (`tenant_id`,`status`),
  KEY `accounts_payable_tenant_id_due_date_index` (`tenant_id`,`due_date`),
  KEY `accounts_payable_source_type_source_id_index` (`source_type`,`source_id`),
  CONSTRAINT `accounts_payable_created_by_foreign` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  CONSTRAINT `accounts_payable_tenant_id_foreign` FOREIGN KEY (`tenant_id`) REFERENCES `tenants` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `accounts_receivable`
--

DROP TABLE IF EXISTS `accounts_receivable`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `accounts_receivable` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `tenant_id` bigint(20) unsigned NOT NULL,
  `customer_id` bigint(20) unsigned DEFAULT NULL,
  `source_type` varchar(30) NOT NULL,
  `source_id` bigint(20) unsigned NOT NULL,
  `description` varchar(255) DEFAULT NULL,
  `total_amount` decimal(12,2) NOT NULL,
  `paid_amount` decimal(12,2) NOT NULL DEFAULT 0.00,
  `balance_amount` decimal(12,2) NOT NULL DEFAULT 0.00,
  `due_date` date DEFAULT NULL,
  `status` varchar(20) NOT NULL DEFAULT 'pending',
  `payment_method` varchar(30) DEFAULT NULL,
  `installment_number` smallint(5) unsigned NOT NULL DEFAULT 1,
  `installments_total` smallint(5) unsigned NOT NULL DEFAULT 1,
  `last_paid_at` timestamp NULL DEFAULT NULL,
  `notes` text DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `accounts_receivable_source_unique` (`tenant_id`,`source_type`,`source_id`,`installment_number`),
  KEY `accounts_receivable_customer_id_foreign` (`customer_id`),
  KEY `accounts_receivable_status_due_idx` (`tenant_id`,`status`,`due_date`),
  KEY `accounts_receivable_customer_idx` (`tenant_id`,`customer_id`),
  CONSTRAINT `accounts_receivable_customer_id_foreign` FOREIGN KEY (`customer_id`) REFERENCES `customers` (`id`) ON DELETE SET NULL,
  CONSTRAINT `accounts_receivable_tenant_id_foreign` FOREIGN KEY (`tenant_id`) REFERENCES `tenants` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `admin_fiscal_documents`
--

DROP TABLE IF EXISTS `admin_fiscal_documents`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `admin_fiscal_documents` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `tenant_id` bigint(20) unsigned NOT NULL,
  `payment_id` bigint(20) unsigned DEFAULT NULL,
  `type` varchar(20) NOT NULL DEFAULT 'nfse',
  `provider` varchar(50) NOT NULL DEFAULT 'focus_nfe',
  `environment` varchar(20) DEFAULT NULL,
  `provider_reference` varchar(120) DEFAULT NULL,
  `number` varchar(120) DEFAULT NULL,
  `series` varchar(20) DEFAULT NULL,
  `access_key` varchar(160) DEFAULT NULL,
  `status` varchar(30) NOT NULL DEFAULT 'registered',
  `amount` decimal(12,2) NOT NULL DEFAULT 0.00,
  `description` varchar(500) DEFAULT NULL,
  `pdf_url` varchar(500) DEFAULT NULL,
  `xml_url` varchar(500) DEFAULT NULL,
  `issued_at` timestamp NULL DEFAULT NULL,
  `registered_by` bigint(20) unsigned DEFAULT NULL,
  `request_payload` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`request_payload`)),
  `response_payload` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`response_payload`)),
  `error_message` text DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `admin_fiscal_documents_registered_by_foreign` (`registered_by`),
  KEY `admin_fiscal_documents_tenant_id_type_status_index` (`tenant_id`,`type`,`status`),
  KEY `admin_fiscal_documents_payment_id_index` (`payment_id`),
  KEY `admin_fiscal_documents_provider_reference_index` (`provider_reference`),
  CONSTRAINT `admin_fiscal_documents_payment_id_foreign` FOREIGN KEY (`payment_id`) REFERENCES `payments` (`id`) ON DELETE SET NULL,
  CONSTRAINT `admin_fiscal_documents_registered_by_foreign` FOREIGN KEY (`registered_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  CONSTRAINT `admin_fiscal_documents_tenant_id_foreign` FOREIGN KEY (`tenant_id`) REFERENCES `tenants` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `admin_fiscal_settings`
--

DROP TABLE IF EXISTS `admin_fiscal_settings`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `admin_fiscal_settings` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `enabled` tinyint(1) NOT NULL DEFAULT 0,
  `provider` varchar(50) NOT NULL DEFAULT 'focus_nfe',
  `environment` varchar(20) NOT NULL DEFAULT 'sandbox',
  `api_token` text DEFAULT NULL,
  `webhook_secret` text DEFAULT NULL,
  `legal_name` varchar(255) DEFAULT NULL,
  `trade_name` varchar(255) DEFAULT NULL,
  `cnpj` varchar(50) DEFAULT NULL,
  `municipal_registration` varchar(50) DEFAULT NULL,
  `service_city_code` varchar(20) DEFAULT NULL,
  `service_list_item` varchar(30) DEFAULT NULL,
  `default_iss_rate` decimal(8,4) DEFAULT NULL,
  `tax_regime` varchar(50) DEFAULT NULL,
  `zip_code` varchar(50) DEFAULT NULL,
  `state` varchar(50) DEFAULT NULL,
  `city` varchar(80) DEFAULT NULL,
  `district` varchar(80) DEFAULT NULL,
  `street` varchar(120) DEFAULT NULL,
  `number` varchar(50) DEFAULT NULL,
  `complement` varchar(100) DEFAULT NULL,
  `default_service_description` varchar(500) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `branches`
--

DROP TABLE IF EXISTS `branches`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `branches` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `tenant_id` bigint(20) unsigned DEFAULT NULL,
  `branch_cnpj` varchar(255) NOT NULL,
  `branch_name` varchar(255) NOT NULL,
  `branch_number` varchar(255) NOT NULL,
  `contact_name` varchar(255) NOT NULL,
  `contact_email` varchar(255) NOT NULL,
  `contact_phone` varchar(255) NOT NULL,
  `contact_whatsapp` varchar(255) NOT NULL,
  `logo` varchar(100) DEFAULT NULL,
  `cep` varchar(50) DEFAULT NULL,
  `state` varchar(50) DEFAULT NULL,
  `city` varchar(50) DEFAULT NULL,
  `district` varchar(50) DEFAULT NULL,
  `street` varchar(50) DEFAULT NULL,
  `number` varchar(50) DEFAULT NULL,
  `complement` varchar(50) DEFAULT NULL,
  `status` tinyint(1) NOT NULL,
  `observations` text DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `branches_tenant_id_foreign` (`tenant_id`),
  CONSTRAINT `branches_tenant_id_foreign` FOREIGN KEY (`tenant_id`) REFERENCES `tenants` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `budgets`
--

DROP TABLE IF EXISTS `budgets`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `budgets` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `tenant_id` bigint(20) unsigned DEFAULT NULL,
  `budget_number` bigint(20) NOT NULL,
  `equipment_id` bigint(20) unsigned NOT NULL,
  `model` varchar(255) DEFAULT NULL,
  `service` varchar(500) DEFAULT NULL,
  `description` text DEFAULT NULL,
  `estimated_time` varchar(50) DEFAULT NULL,
  `part_value` decimal(10,2) DEFAULT NULL,
  `labor_value` decimal(10,2) NOT NULL,
  `total_value` decimal(10,2) NOT NULL,
  `warranty` varchar(50) DEFAULT NULL,
  `validity` int(11) DEFAULT NULL,
  `obs` text DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `budgets_equipment_id_foreign` (`equipment_id`),
  KEY `budgets_tenant_number_idx` (`tenant_id`,`budget_number`),
  CONSTRAINT `budgets_equipment_id_foreign` FOREIGN KEY (`equipment_id`) REFERENCES `equipment` (`id`) ON DELETE CASCADE,
  CONSTRAINT `budgets_tenant_id_foreign` FOREIGN KEY (`tenant_id`) REFERENCES `tenants` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `cache`
--

DROP TABLE IF EXISTS `cache`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `cache` (
  `key` varchar(255) NOT NULL,
  `value` mediumtext NOT NULL,
  `expiration` int(11) NOT NULL,
  PRIMARY KEY (`key`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `cache_locks`
--

DROP TABLE IF EXISTS `cache_locks`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `cache_locks` (
  `key` varchar(255) NOT NULL,
  `owner` varchar(255) NOT NULL,
  `expiration` int(11) NOT NULL,
  PRIMARY KEY (`key`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `cash_session_logs`
--

DROP TABLE IF EXISTS `cash_session_logs`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `cash_session_logs` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `tenant_id` bigint(20) unsigned NOT NULL,
  `cash_session_id` bigint(20) unsigned NOT NULL,
  `user_id` bigint(20) unsigned DEFAULT NULL,
  `action` varchar(80) NOT NULL,
  `data` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`data`)),
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `cash_session_logs_tenant_id_foreign` (`tenant_id`),
  KEY `cash_session_logs_user_id_foreign` (`user_id`),
  KEY `cash_session_logs_cash_session_id_created_at_index` (`cash_session_id`,`created_at`),
  CONSTRAINT `cash_session_logs_cash_session_id_foreign` FOREIGN KEY (`cash_session_id`) REFERENCES `cash_sessions` (`id`) ON DELETE CASCADE,
  CONSTRAINT `cash_session_logs_tenant_id_foreign` FOREIGN KEY (`tenant_id`) REFERENCES `tenants` (`id`) ON DELETE CASCADE,
  CONSTRAINT `cash_session_logs_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `cash_session_movements`
--

DROP TABLE IF EXISTS `cash_session_movements`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `cash_session_movements` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `tenant_id` bigint(20) unsigned NOT NULL,
  `cash_session_id` bigint(20) unsigned NOT NULL,
  `user_id` bigint(20) unsigned NOT NULL,
  `type` varchar(30) NOT NULL,
  `amount` decimal(12,2) NOT NULL,
  `description` text DEFAULT NULL,
  `source_type` varchar(50) DEFAULT NULL,
  `source_id` bigint(20) unsigned DEFAULT NULL,
  `cancelled_at` timestamp NULL DEFAULT NULL,
  `cancelled_by` bigint(20) unsigned DEFAULT NULL,
  `cancellation_reason` text DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `cash_session_movements_user_id_foreign` (`user_id`),
  KEY `cash_session_movements_tenant_id_cash_session_id_type_index` (`tenant_id`,`cash_session_id`,`type`),
  KEY `cash_session_movements_cash_session_id_created_at_index` (`cash_session_id`,`created_at`),
  KEY `cash_session_movements_cancelled_by_foreign` (`cancelled_by`),
  KEY `cash_session_movements_cash_session_id_type_cancelled_at_index` (`cash_session_id`,`type`,`cancelled_at`),
  KEY `cash_session_movements_source_idx` (`source_type`,`source_id`),
  CONSTRAINT `cash_session_movements_cancelled_by_foreign` FOREIGN KEY (`cancelled_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  CONSTRAINT `cash_session_movements_cash_session_id_foreign` FOREIGN KEY (`cash_session_id`) REFERENCES `cash_sessions` (`id`) ON DELETE CASCADE,
  CONSTRAINT `cash_session_movements_tenant_id_foreign` FOREIGN KEY (`tenant_id`) REFERENCES `tenants` (`id`) ON DELETE CASCADE,
  CONSTRAINT `cash_session_movements_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `cash_sessions`
--

DROP TABLE IF EXISTS `cash_sessions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `cash_sessions` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `tenant_id` bigint(20) unsigned NOT NULL,
  `opened_by` bigint(20) unsigned NOT NULL,
  `closed_by` bigint(20) unsigned DEFAULT NULL,
  `opened_at` timestamp NOT NULL,
  `closed_at` timestamp NULL DEFAULT NULL,
  `opening_balance` decimal(12,2) NOT NULL DEFAULT 0.00,
  `closing_balance` decimal(12,2) DEFAULT NULL,
  `expected_balance` decimal(12,2) DEFAULT NULL,
  `difference` decimal(12,2) DEFAULT NULL,
  `total_completed_sales` decimal(12,2) NOT NULL DEFAULT 0.00,
  `total_order_payments` decimal(12,2) NOT NULL DEFAULT 0.00,
  `total_cancelled_sales` decimal(12,2) NOT NULL DEFAULT 0.00,
  `manual_entries` decimal(12,2) NOT NULL DEFAULT 0.00,
  `manual_exits` decimal(12,2) NOT NULL DEFAULT 0.00,
  `status` varchar(20) NOT NULL DEFAULT 'open',
  `notes` text DEFAULT NULL,
  `closing_notes` text DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `cash_sessions_opened_by_foreign` (`opened_by`),
  KEY `cash_sessions_closed_by_foreign` (`closed_by`),
  KEY `cash_sessions_tenant_id_status_index` (`tenant_id`,`status`),
  KEY `cash_sessions_tenant_id_opened_at_index` (`tenant_id`,`opened_at`),
  CONSTRAINT `cash_sessions_closed_by_foreign` FOREIGN KEY (`closed_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  CONSTRAINT `cash_sessions_opened_by_foreign` FOREIGN KEY (`opened_by`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  CONSTRAINT `cash_sessions_tenant_id_foreign` FOREIGN KEY (`tenant_id`) REFERENCES `tenants` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `checklists`
--

DROP TABLE IF EXISTS `checklists`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `checklists` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `tenant_id` bigint(20) unsigned DEFAULT NULL,
  `equipment_id` bigint(20) unsigned DEFAULT NULL,
  `checklist_number` int(11) NOT NULL,
  `checklist` varchar(500) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `checklists_equipment_id_foreign` (`equipment_id`),
  KEY `checklists_tenant_number_idx` (`tenant_id`,`checklist_number`),
  CONSTRAINT `checklists_equipment_id_foreign` FOREIGN KEY (`equipment_id`) REFERENCES `equipment` (`id`) ON DELETE CASCADE,
  CONSTRAINT `checklists_tenant_id_foreign` FOREIGN KEY (`tenant_id`) REFERENCES `tenants` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `companies`
--

DROP TABLE IF EXISTS `companies`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `companies` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `tenant_id` bigint(20) unsigned DEFAULT NULL,
  `shortname` varchar(50) DEFAULT NULL,
  `companyname` varchar(50) DEFAULT NULL,
  `cnpj` varchar(50) DEFAULT NULL,
  `logo` varchar(100) DEFAULT NULL,
  `zip_code` varchar(50) DEFAULT NULL,
  `state` varchar(50) DEFAULT NULL,
  `city` varchar(50) DEFAULT NULL,
  `district` varchar(50) DEFAULT NULL,
  `street` varchar(50) DEFAULT NULL,
  `number` varchar(50) DEFAULT NULL,
  `complement` varchar(50) DEFAULT NULL,
  `telephone` varchar(50) DEFAULT NULL,
  `whatsapp` varchar(50) DEFAULT NULL,
  `site` varchar(50) DEFAULT NULL,
  `email` varchar(50) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `companies_tenant_id_foreign` (`tenant_id`),
  CONSTRAINT `companies_tenant_id_foreign` FOREIGN KEY (`tenant_id`) REFERENCES `tenants` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `customers`
--

DROP TABLE IF EXISTS `customers`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `customers` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `tenant_id` bigint(20) unsigned NOT NULL,
  `customer_number` int(11) NOT NULL,
  `name` varchar(255) NOT NULL,
  `cpfcnpj` varchar(50) DEFAULT NULL,
  `birth` date DEFAULT NULL,
  `email` varchar(50) DEFAULT NULL,
  `zipcode` varchar(20) DEFAULT NULL,
  `state` varchar(20) DEFAULT NULL,
  `city` varchar(50) DEFAULT NULL,
  `district` varchar(50) DEFAULT NULL,
  `street` varchar(80) DEFAULT NULL,
  `complement` varchar(80) DEFAULT NULL,
  `number` int(11) DEFAULT NULL,
  `phone` varchar(20) DEFAULT NULL,
  `contactname` varchar(50) DEFAULT NULL,
  `whatsapp` varchar(255) DEFAULT NULL,
  `contactphone` varchar(20) DEFAULT NULL,
  `observations` text DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `contact` varchar(50) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `customers_tenant_number_idx` (`tenant_id`,`customer_number`),
  CONSTRAINT `customers_tenant_id_foreign` FOREIGN KEY (`tenant_id`) REFERENCES `tenants` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=1659 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `equipment`
--

DROP TABLE IF EXISTS `equipment`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `equipment` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `tenant_id` bigint(20) unsigned DEFAULT NULL,
  `equipment_number` int(11) NOT NULL,
  `equipment` varchar(255) NOT NULL,
  `chart` tinyint(1) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `equipment_tenant_id_foreign` (`tenant_id`),
  CONSTRAINT `equipment_tenant_id_foreign` FOREIGN KEY (`tenant_id`) REFERENCES `tenants` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `expense_logs`
--

DROP TABLE IF EXISTS `expense_logs`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `expense_logs` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `tenant_id` bigint(20) unsigned NOT NULL,
  `expense_id` bigint(20) unsigned NOT NULL,
  `user_id` bigint(20) unsigned DEFAULT NULL,
  `action` varchar(80) NOT NULL,
  `data` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`data`)),
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `expense_logs_tenant_id_foreign` (`tenant_id`),
  KEY `expense_logs_user_id_foreign` (`user_id`),
  KEY `expense_logs_expense_id_created_at_index` (`expense_id`,`created_at`),
  CONSTRAINT `expense_logs_expense_id_foreign` FOREIGN KEY (`expense_id`) REFERENCES `expenses` (`id`) ON DELETE CASCADE,
  CONSTRAINT `expense_logs_tenant_id_foreign` FOREIGN KEY (`tenant_id`) REFERENCES `tenants` (`id`) ON DELETE CASCADE,
  CONSTRAINT `expense_logs_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `expenses`
--

DROP TABLE IF EXISTS `expenses`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `expenses` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `tenant_id` bigint(20) unsigned NOT NULL,
  `created_by` bigint(20) unsigned DEFAULT NULL,
  `expense_date` date NOT NULL,
  `description` varchar(500) DEFAULT NULL,
  `category` varchar(255) DEFAULT NULL,
  `amount` decimal(12,2) NOT NULL,
  `notes` text DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `expense_number` int(10) unsigned DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `expenses_tenant_id_expense_number_unique` (`tenant_id`,`expense_number`),
  KEY `expenses_created_by_foreign` (`created_by`),
  KEY `expenses_tenant_id_expense_date_index` (`tenant_id`,`expense_date`),
  KEY `expenses_tenant_id_category_index` (`tenant_id`,`category`),
  CONSTRAINT `expenses_created_by_foreign` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  CONSTRAINT `expenses_tenant_id_foreign` FOREIGN KEY (`tenant_id`) REFERENCES `tenants` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `failed_jobs`
--

DROP TABLE IF EXISTS `failed_jobs`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `failed_jobs` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `uuid` varchar(255) NOT NULL,
  `connection` text NOT NULL,
  `queue` text NOT NULL,
  `payload` longtext NOT NULL,
  `exception` longtext NOT NULL,
  `failed_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `failed_jobs_uuid_unique` (`uuid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `features`
--

DROP TABLE IF EXISTS `features`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `features` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `period_id` bigint(20) unsigned DEFAULT NULL,
  `name` varchar(255) NOT NULL,
  `order` int(11) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `features_period_id_foreign` (`period_id`),
  CONSTRAINT `features_period_id_foreign` FOREIGN KEY (`period_id`) REFERENCES `periods` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `fiscal_documents`
--

DROP TABLE IF EXISTS `fiscal_documents`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `fiscal_documents` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `tenant_id` bigint(20) unsigned NOT NULL,
  `documentable_type` varchar(255) NOT NULL,
  `documentable_id` bigint(20) unsigned NOT NULL,
  `type` varchar(20) NOT NULL,
  `provider` varchar(50) NOT NULL DEFAULT 'manual',
  `environment` varchar(20) DEFAULT NULL,
  `provider_reference` varchar(120) DEFAULT NULL,
  `number` varchar(120) DEFAULT NULL,
  `series` varchar(20) DEFAULT NULL,
  `access_key` varchar(160) DEFAULT NULL,
  `status` varchar(30) NOT NULL DEFAULT 'registered',
  `pdf_url` varchar(500) DEFAULT NULL,
  `xml_url` varchar(500) DEFAULT NULL,
  `issued_at` timestamp NULL DEFAULT NULL,
  `registered_by` bigint(20) unsigned DEFAULT NULL,
  `notes` text DEFAULT NULL,
  `request_payload` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`request_payload`)),
  `response_payload` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`response_payload`)),
  `error_message` text DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `fiscal_documents_documentable_type_documentable_id_index` (`documentable_type`,`documentable_id`),
  KEY `fiscal_documents_registered_by_foreign` (`registered_by`),
  KEY `fiscal_documents_tenant_id_type_status_index` (`tenant_id`,`type`,`status`),
  CONSTRAINT `fiscal_documents_registered_by_foreign` FOREIGN KEY (`registered_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  CONSTRAINT `fiscal_documents_tenant_id_foreign` FOREIGN KEY (`tenant_id`) REFERENCES `tenants` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `fiscal_settings`
--

DROP TABLE IF EXISTS `fiscal_settings`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `fiscal_settings` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `tenant_id` bigint(20) unsigned NOT NULL,
  `enabled` tinyint(1) NOT NULL DEFAULT 0,
  `provider` varchar(50) NOT NULL DEFAULT 'focus_nfe',
  `environment` varchar(20) NOT NULL DEFAULT 'sandbox',
  `api_token` text DEFAULT NULL,
  `webhook_secret` text DEFAULT NULL,
  `nfe_enabled` tinyint(1) NOT NULL DEFAULT 0,
  `nfse_enabled` tinyint(1) NOT NULL DEFAULT 0,
  `nfse_mode` varchar(20) NOT NULL DEFAULT 'municipal',
  `nfse_simple_option` tinyint(3) unsigned DEFAULT NULL,
  `nfse_special_tax_regime` tinyint(3) unsigned NOT NULL DEFAULT 0,
  `nfse_operation_indicator` varchar(6) NOT NULL DEFAULT '050101',
  `company_tax_regime` varchar(50) DEFAULT NULL,
  `state_registration` varchar(50) DEFAULT NULL,
  `municipal_registration` varchar(50) DEFAULT NULL,
  `service_city_code` varchar(20) DEFAULT NULL,
  `service_list_item` varchar(30) DEFAULT NULL,
  `default_iss_rate` decimal(8,4) DEFAULT NULL,
  `nfse_ibs_cbs_situation` varchar(3) DEFAULT NULL,
  `nfse_ibs_cbs_classification` varchar(6) DEFAULT NULL,
  `default_nfe_series` varchar(20) DEFAULT NULL,
  `default_nfse_series` varchar(20) DEFAULT NULL,
  `default_commercial_unit` varchar(10) NOT NULL DEFAULT 'UN',
  `default_tax_unit` varchar(10) NOT NULL DEFAULT 'UN',
  `default_icms_origin` varchar(5) NOT NULL DEFAULT '0',
  `default_icms_situation` varchar(10) NOT NULL DEFAULT '102',
  `default_pis_situation` varchar(10) NOT NULL DEFAULT '99',
  `default_cofins_situation` varchar(10) NOT NULL DEFAULT '99',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `fiscal_settings_tenant_id_unique` (`tenant_id`),
  CONSTRAINT `fiscal_settings_tenant_id_foreign` FOREIGN KEY (`tenant_id`) REFERENCES `tenants` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `help_topics`
--

DROP TABLE IF EXISTS `help_topics`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `help_topics` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `slug` varchar(255) NOT NULL,
  `title` varchar(255) NOT NULL,
  `content` longtext NOT NULL,
  `category` varchar(255) DEFAULT NULL,
  `position` int(10) unsigned NOT NULL DEFAULT 0,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `help_topics_slug_unique` (`slug`)
) ENGINE=InnoDB AUTO_INCREMENT=31 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `images`
--

DROP TABLE IF EXISTS `images`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `images` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `tenant_id` bigint(20) unsigned DEFAULT NULL,
  `order_id` bigint(20) unsigned NOT NULL,
  `filename` varchar(255) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `images_tenant_id_foreign` (`tenant_id`),
  KEY `images_order_id_foreign` (`order_id`),
  CONSTRAINT `images_order_id_foreign` FOREIGN KEY (`order_id`) REFERENCES `orders` (`id`) ON DELETE CASCADE,
  CONSTRAINT `images_tenant_id_foreign` FOREIGN KEY (`tenant_id`) REFERENCES `tenants` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `job_batches`
--

DROP TABLE IF EXISTS `job_batches`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `job_batches` (
  `id` varchar(255) NOT NULL,
  `name` varchar(255) NOT NULL,
  `total_jobs` int(11) NOT NULL,
  `pending_jobs` int(11) NOT NULL,
  `failed_jobs` int(11) NOT NULL,
  `failed_job_ids` longtext NOT NULL,
  `options` mediumtext DEFAULT NULL,
  `cancelled_at` int(11) DEFAULT NULL,
  `created_at` int(11) NOT NULL,
  `finished_at` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `jobs`
--

DROP TABLE IF EXISTS `jobs`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `jobs` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `queue` varchar(255) NOT NULL,
  `payload` longtext NOT NULL,
  `attempts` tinyint(3) unsigned NOT NULL,
  `reserved_at` int(10) unsigned DEFAULT NULL,
  `available_at` int(10) unsigned NOT NULL,
  `created_at` int(10) unsigned NOT NULL,
  PRIMARY KEY (`id`),
  KEY `jobs_queue_index` (`queue`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `maintenance_contract_logs`
--

DROP TABLE IF EXISTS `maintenance_contract_logs`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `maintenance_contract_logs` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `tenant_id` bigint(20) unsigned NOT NULL,
  `maintenance_contract_id` bigint(20) unsigned NOT NULL,
  `user_id` bigint(20) unsigned DEFAULT NULL,
  `action` varchar(80) NOT NULL,
  `data` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`data`)),
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `maintenance_contract_logs_tenant_id_foreign` (`tenant_id`),
  KEY `maintenance_contract_logs_user_id_foreign` (`user_id`),
  KEY `maintenance_contract_logs_contract_created_idx` (`maintenance_contract_id`,`created_at`),
  CONSTRAINT `maintenance_contract_logs_maintenance_contract_id_foreign` FOREIGN KEY (`maintenance_contract_id`) REFERENCES `maintenance_contracts` (`id`) ON DELETE CASCADE,
  CONSTRAINT `maintenance_contract_logs_tenant_id_foreign` FOREIGN KEY (`tenant_id`) REFERENCES `tenants` (`id`) ON DELETE CASCADE,
  CONSTRAINT `maintenance_contract_logs_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=9 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `maintenance_contracts`
--

DROP TABLE IF EXISTS `maintenance_contracts`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `maintenance_contracts` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `tenant_id` bigint(20) unsigned NOT NULL,
  `customer_id` bigint(20) unsigned NOT NULL,
  `contract_number` int(10) unsigned DEFAULT NULL,
  `description` varchar(255) NOT NULL,
  `monthly_amount` decimal(10,2) NOT NULL,
  `billing_day` tinyint(3) unsigned NOT NULL DEFAULT 1,
  `start_date` date NOT NULL,
  `duration_months` smallint(5) unsigned DEFAULT NULL,
  `end_date` date DEFAULT NULL,
  `visit_frequency_days` smallint(5) unsigned DEFAULT NULL,
  `preferred_technician_id` bigint(20) unsigned DEFAULT NULL,
  `next_billing_date` date DEFAULT NULL,
  `next_schedule_date` date DEFAULT NULL,
  `status` varchar(20) NOT NULL DEFAULT 'active',
  `notes` text DEFAULT NULL,
  `created_by` bigint(20) unsigned DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `maintenance_contracts_customer_id_foreign` (`customer_id`),
  KEY `maintenance_contracts_preferred_technician_id_foreign` (`preferred_technician_id`),
  KEY `maintenance_contracts_created_by_foreign` (`created_by`),
  KEY `maintenance_contracts_tenant_id_status_index` (`tenant_id`,`status`),
  KEY `maintenance_contracts_tenant_id_next_billing_date_index` (`tenant_id`,`next_billing_date`),
  KEY `maintenance_contracts_tenant_id_next_schedule_date_index` (`tenant_id`,`next_schedule_date`),
  CONSTRAINT `maintenance_contracts_created_by_foreign` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  CONSTRAINT `maintenance_contracts_customer_id_foreign` FOREIGN KEY (`customer_id`) REFERENCES `customers` (`id`) ON DELETE CASCADE,
  CONSTRAINT `maintenance_contracts_preferred_technician_id_foreign` FOREIGN KEY (`preferred_technician_id`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  CONSTRAINT `maintenance_contracts_tenant_id_foreign` FOREIGN KEY (`tenant_id`) REFERENCES `tenants` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `messages`
--

DROP TABLE IF EXISTS `messages`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `messages` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `tenant_id` bigint(20) unsigned DEFAULT NULL,
  `sender_id` bigint(20) DEFAULT NULL,
  `recipient_id` bigint(20) DEFAULT NULL,
  `message_number` text NOT NULL,
  `title` text NOT NULL,
  `message` text NOT NULL,
  `status` tinyint(1) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `messages_tenant_id_foreign` (`tenant_id`),
  KEY `messages_recipient_status_id_idx` (`recipient_id`,`status`,`id`),
  KEY `messages_sender_id_idx` (`sender_id`,`id`),
  CONSTRAINT `messages_tenant_id_foreign` FOREIGN KEY (`tenant_id`) REFERENCES `tenants` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `migrations`
--

DROP TABLE IF EXISTS `migrations`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `migrations` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `migration` varchar(255) NOT NULL,
  `batch` int(11) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=145 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `operational_audits`
--

DROP TABLE IF EXISTS `operational_audits`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `operational_audits` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `tenant_id` bigint(20) unsigned NOT NULL,
  `user_id` bigint(20) unsigned DEFAULT NULL,
  `entity_type` varchar(80) NOT NULL,
  `entity_id` bigint(20) unsigned DEFAULT NULL,
  `action` varchar(80) NOT NULL,
  `data` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`data`)),
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `operational_audits_user_id_foreign` (`user_id`),
  KEY `operational_audits_tenant_id_entity_type_entity_id_index` (`tenant_id`,`entity_type`,`entity_id`),
  KEY `operational_audits_tenant_id_action_created_at_index` (`tenant_id`,`action`,`created_at`),
  CONSTRAINT `operational_audits_tenant_id_foreign` FOREIGN KEY (`tenant_id`) REFERENCES `tenants` (`id`) ON DELETE CASCADE,
  CONSTRAINT `operational_audits_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `order_commissions`
--

DROP TABLE IF EXISTS `order_commissions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `order_commissions` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `tenant_id` bigint(20) unsigned NOT NULL,
  `order_id` bigint(20) unsigned NOT NULL,
  `user_id` bigint(20) unsigned NOT NULL,
  `account_payable_id` bigint(20) unsigned DEFAULT NULL,
  `base_amount` decimal(10,2) NOT NULL,
  `commission_percentage` decimal(5,2) NOT NULL,
  `commission_amount` decimal(10,2) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `order_commissions_order_id_unique` (`order_id`),
  KEY `order_commissions_user_id_foreign` (`user_id`),
  KEY `order_commissions_account_payable_id_foreign` (`account_payable_id`),
  KEY `order_commissions_tenant_id_user_id_index` (`tenant_id`,`user_id`),
  CONSTRAINT `order_commissions_account_payable_id_foreign` FOREIGN KEY (`account_payable_id`) REFERENCES `accounts_payable` (`id`) ON DELETE SET NULL,
  CONSTRAINT `order_commissions_order_id_foreign` FOREIGN KEY (`order_id`) REFERENCES `orders` (`id`) ON DELETE CASCADE,
  CONSTRAINT `order_commissions_tenant_id_foreign` FOREIGN KEY (`tenant_id`) REFERENCES `tenants` (`id`) ON DELETE CASCADE,
  CONSTRAINT `order_commissions_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `order_items`
--

DROP TABLE IF EXISTS `order_items`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `order_items` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `tenant_id` bigint(20) unsigned NOT NULL,
  `order_id` bigint(20) unsigned NOT NULL,
  `item_type` varchar(20) NOT NULL,
  `source_type` varchar(30) DEFAULT NULL,
  `source_id` bigint(20) unsigned DEFAULT NULL,
  `description` varchar(500) DEFAULT NULL,
  `quantity` decimal(12,3) NOT NULL DEFAULT 1.000,
  `unit_price` decimal(12,2) NOT NULL DEFAULT 0.00,
  `total_price` decimal(12,2) NOT NULL DEFAULT 0.00,
  `unit_cost` decimal(12,2) DEFAULT NULL,
  `sort_order` int(10) unsigned NOT NULL DEFAULT 0,
  `meta` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`meta`)),
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `order_items_tenant_id_item_type_index` (`tenant_id`,`item_type`),
  KEY `order_items_order_id_item_type_index` (`order_id`,`item_type`),
  KEY `order_items_source_type_source_id_index` (`source_type`,`source_id`),
  CONSTRAINT `order_items_order_id_foreign` FOREIGN KEY (`order_id`) REFERENCES `orders` (`id`) ON DELETE CASCADE,
  CONSTRAINT `order_items_tenant_id_foreign` FOREIGN KEY (`tenant_id`) REFERENCES `tenants` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `order_logs`
--

DROP TABLE IF EXISTS `order_logs`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `order_logs` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `order_id` bigint(20) unsigned NOT NULL,
  `user_id` bigint(20) unsigned DEFAULT NULL,
  `action` varchar(255) NOT NULL,
  `data` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`data`)),
  `created_at` timestamp NOT NULL,
  PRIMARY KEY (`id`),
  KEY `order_logs_user_id_foreign` (`user_id`),
  KEY `order_logs_order_created_idx` (`order_id`,`created_at`),
  CONSTRAINT `order_logs_order_id_foreign` FOREIGN KEY (`order_id`) REFERENCES `orders` (`id`) ON DELETE CASCADE,
  CONSTRAINT `order_logs_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `order_parts`
--

DROP TABLE IF EXISTS `order_parts`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `order_parts` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `order_id` bigint(20) unsigned NOT NULL,
  `part_id` bigint(20) unsigned NOT NULL,
  `quantity` int(11) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `order_parts_order_id_foreign` (`order_id`),
  KEY `order_parts_part_id_foreign` (`part_id`),
  CONSTRAINT `order_parts_order_id_foreign` FOREIGN KEY (`order_id`) REFERENCES `orders` (`id`) ON DELETE CASCADE,
  CONSTRAINT `order_parts_part_id_foreign` FOREIGN KEY (`part_id`) REFERENCES `parts` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `order_payments`
--

DROP TABLE IF EXISTS `order_payments`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `order_payments` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `order_id` bigint(20) unsigned NOT NULL,
  `cash_session_id` bigint(20) unsigned DEFAULT NULL,
  `amount` decimal(10,2) NOT NULL,
  `payment_method` varchar(255) NOT NULL,
  `paid_at` timestamp NULL DEFAULT NULL,
  `notes` text DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `order_payments_order_paid_at_idx` (`order_id`,`paid_at`),
  KEY `order_payments_cash_session_id_paid_at_index` (`cash_session_id`,`paid_at`),
  CONSTRAINT `order_payments_cash_session_id_foreign` FOREIGN KEY (`cash_session_id`) REFERENCES `cash_sessions` (`id`) ON DELETE SET NULL,
  CONSTRAINT `order_payments_order_id_foreign` FOREIGN KEY (`order_id`) REFERENCES `orders` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `order_status_history`
--

DROP TABLE IF EXISTS `order_status_history`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `order_status_history` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `order_id` bigint(20) unsigned NOT NULL,
  `status` tinyint(4) NOT NULL,
  `note` varchar(255) NOT NULL,
  `changed_by` bigint(20) unsigned DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `order_status_history_changed_by_foreign` (`changed_by`),
  KEY `order_status_history_order_created_idx` (`order_id`,`created_at`),
  CONSTRAINT `order_status_history_changed_by_foreign` FOREIGN KEY (`changed_by`) REFERENCES `users` (`id`),
  CONSTRAINT `order_status_history_order_id_foreign` FOREIGN KEY (`order_id`) REFERENCES `orders` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `orders`
--

DROP TABLE IF EXISTS `orders`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `orders` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `tenant_id` bigint(20) unsigned DEFAULT NULL,
  `customer_id` bigint(20) unsigned DEFAULT NULL,
  `equipment_id` bigint(20) unsigned DEFAULT NULL,
  `user_id` bigint(20) unsigned DEFAULT NULL,
  `order_type` varchar(30) NOT NULL DEFAULT 'equipment',
  `order_number` int(11) NOT NULL,
  `barcode` varchar(13) DEFAULT NULL,
  `tracking_token` varchar(40) NOT NULL,
  `model` varchar(50) DEFAULT NULL,
  `password` varchar(50) DEFAULT NULL,
  `defect` text NOT NULL,
  `service_type` varchar(150) DEFAULT NULL,
  `service_details` text DEFAULT NULL,
  `materials_used` text DEFAULT NULL,
  `state_conservation` text DEFAULT NULL,
  `accessories` text DEFAULT NULL,
  `budget_description` text DEFAULT NULL,
  `budget_value` decimal(10,2) DEFAULT NULL,
  `budget_link` text DEFAULT NULL,
  `service_status` tinyint(4) DEFAULT NULL,
  `observations` text DEFAULT NULL,
  `customer_update_note` text DEFAULT NULL,
  `customer_update_note_at` timestamp NULL DEFAULT NULL,
  `services_performed` text DEFAULT NULL,
  `technician_diagnosis` text DEFAULT NULL,
  `technician_solution` text DEFAULT NULL,
  `technician_observations` text DEFAULT NULL,
  `technician_checklist_items` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`technician_checklist_items`)),
  `technician_checklist_completed_at` timestamp NULL DEFAULT NULL,
  `technician_attended_at` timestamp NULL DEFAULT NULL,
  `technician_local_payment_received` tinyint(1) NOT NULL DEFAULT 0,
  `technician_local_payment_status` varchar(20) DEFAULT NULL,
  `technician_local_payment_amount` decimal(10,2) DEFAULT NULL,
  `technician_local_payment_method` varchar(50) DEFAULT NULL,
  `technician_local_payment_notes` text DEFAULT NULL,
  `technician_local_payment_received_at` timestamp NULL DEFAULT NULL,
  `technician_local_payment_user_id` bigint(20) unsigned DEFAULT NULL,
  `parts_value` decimal(10,2) DEFAULT NULL,
  `service_value` decimal(10,2) DEFAULT NULL,
  `service_cost` decimal(10,2) DEFAULT NULL,
  `delivery_forecast` date DEFAULT NULL,
  `delivery_date` datetime DEFAULT NULL,
  `warranty_days` smallint(5) unsigned DEFAULT NULL,
  `warranty_expires_at` timestamp NULL DEFAULT NULL,
  `is_warranty_return` tinyint(1) NOT NULL DEFAULT 0,
  `warranty_source_order_id` bigint(20) unsigned DEFAULT NULL,
  `feedback` tinyint(1) DEFAULT NULL,
  `customer_signature_captured_at` timestamp NULL DEFAULT NULL,
  `customer_notification_acknowledged_at` timestamp NULL DEFAULT NULL,
  `customer_pickup_acknowledged_at` timestamp NULL DEFAULT NULL,
  `customer_feedback_rating` tinyint(3) unsigned DEFAULT NULL,
  `customer_feedback_comment` text DEFAULT NULL,
  `customer_feedback_submitted_at` timestamp NULL DEFAULT NULL,
  `customer_feedback_reminder_sent_at` timestamp NULL DEFAULT NULL,
  `customer_feedback_request_expired_at` timestamp NULL DEFAULT NULL,
  `customer_feedback_recovery_assigned_to` bigint(20) unsigned DEFAULT NULL,
  `customer_feedback_recovery_status` varchar(30) DEFAULT NULL,
  `customer_feedback_recovery_notes` text DEFAULT NULL,
  `customer_feedback_recovery_updated_at` timestamp NULL DEFAULT NULL,
  `budget_follow_up_paused_at` timestamp NULL DEFAULT NULL,
  `budget_follow_up_paused_by` bigint(20) unsigned DEFAULT NULL,
  `budget_follow_up_pause_reason` text DEFAULT NULL,
  `budget_follow_up_snoozed_until` datetime DEFAULT NULL,
  `budget_follow_up_assigned_to` bigint(20) unsigned DEFAULT NULL,
  `budget_follow_up_response_status` varchar(255) DEFAULT NULL,
  `budget_follow_up_response_at` timestamp NULL DEFAULT NULL,
  `payment_follow_up_paused_at` timestamp NULL DEFAULT NULL,
  `payment_follow_up_paused_by` bigint(20) unsigned DEFAULT NULL,
  `payment_follow_up_pause_reason` text DEFAULT NULL,
  `payment_follow_up_snoozed_until` datetime DEFAULT NULL,
  `payment_follow_up_assigned_to` bigint(20) unsigned DEFAULT NULL,
  `payment_follow_up_response_status` varchar(255) DEFAULT NULL,
  `payment_follow_up_response_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `fiscal_document_number` varchar(120) DEFAULT NULL,
  `fiscal_document_key` varchar(120) DEFAULT NULL,
  `fiscal_document_url` varchar(500) DEFAULT NULL,
  `fiscal_issued_at` timestamp NULL DEFAULT NULL,
  `fiscal_registered_by` bigint(20) unsigned DEFAULT NULL,
  `fiscal_notes` text DEFAULT NULL,
  `public_access_key` text DEFAULT NULL,
  `public_access_key_hash` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `orders_tracking_token_unique` (`tracking_token`),
  UNIQUE KEY `orders_tenant_id_order_number_unique` (`tenant_id`,`order_number`),
  UNIQUE KEY `orders_tenant_barcode_unique` (`tenant_id`,`barcode`),
  KEY `orders_customer_id_foreign` (`customer_id`),
  KEY `orders_equipment_id_foreign` (`equipment_id`),
  KEY `orders_user_id_foreign` (`user_id`),
  KEY `orders_tenant_status_created_idx` (`tenant_id`,`service_status`,`created_at`),
  KEY `orders_tenant_customer_created_idx` (`tenant_id`,`customer_id`,`created_at`),
  KEY `orders_tenant_created_idx` (`tenant_id`,`created_at`),
  KEY `orders_fiscal_registered_by_foreign` (`fiscal_registered_by`),
  KEY `orders_warranty_source_order_id_foreign` (`warranty_source_order_id`),
  KEY `orders_budget_follow_up_paused_by_foreign` (`budget_follow_up_paused_by`),
  KEY `orders_payment_follow_up_paused_by_foreign` (`payment_follow_up_paused_by`),
  KEY `orders_customer_feedback_recovery_assigned_to_foreign` (`customer_feedback_recovery_assigned_to`),
  KEY `orders_technician_local_payment_user_id_foreign` (`technician_local_payment_user_id`),
  KEY `orders_tenant_type_idx` (`tenant_id`,`order_type`),
  CONSTRAINT `orders_budget_follow_up_paused_by_foreign` FOREIGN KEY (`budget_follow_up_paused_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  CONSTRAINT `orders_customer_feedback_recovery_assigned_to_foreign` FOREIGN KEY (`customer_feedback_recovery_assigned_to`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  CONSTRAINT `orders_customer_id_foreign` FOREIGN KEY (`customer_id`) REFERENCES `customers` (`id`) ON DELETE CASCADE,
  CONSTRAINT `orders_equipment_id_foreign` FOREIGN KEY (`equipment_id`) REFERENCES `equipment` (`id`) ON DELETE CASCADE,
  CONSTRAINT `orders_fiscal_registered_by_foreign` FOREIGN KEY (`fiscal_registered_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  CONSTRAINT `orders_payment_follow_up_paused_by_foreign` FOREIGN KEY (`payment_follow_up_paused_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  CONSTRAINT `orders_technician_local_payment_user_id_foreign` FOREIGN KEY (`technician_local_payment_user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  CONSTRAINT `orders_tenant_id_foreign` FOREIGN KEY (`tenant_id`) REFERENCES `tenants` (`id`) ON DELETE CASCADE,
  CONSTRAINT `orders_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  CONSTRAINT `orders_warranty_source_order_id_foreign` FOREIGN KEY (`warranty_source_order_id`) REFERENCES `orders` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `others`
--

DROP TABLE IF EXISTS `others`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `others` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `tenant_id` bigint(20) unsigned DEFAULT NULL,
  `navigation` tinyint(1) NOT NULL DEFAULT 0,
  `records_per_page` smallint(5) unsigned NOT NULL DEFAULT 20,
  `enableparts` tinyint(1) NOT NULL DEFAULT 0,
  `enablesales` tinyint(1) NOT NULL DEFAULT 0,
  `enable_finance` tinyint(1) NOT NULL DEFAULT 0,
  `show_follow_ups_menu` tinyint(1) NOT NULL DEFAULT 0,
  `show_tasks_menu` tinyint(1) NOT NULL DEFAULT 0,
  `show_commercial_performance_menu` tinyint(1) NOT NULL DEFAULT 0,
  `show_quality_menu` tinyint(1) NOT NULL DEFAULT 0,
  `print_label_button_after_order_create` tinyint(1) NOT NULL DEFAULT 0,
  `warranty_return_alert_threshold` decimal(5,2) DEFAULT NULL,
  `communication_follow_up_cooldown_days` smallint(5) unsigned DEFAULT NULL,
  `automatic_follow_ups_enabled` tinyint(1) NOT NULL DEFAULT 0,
  `enable_technician_schedule_notifications` tinyint(1) NOT NULL DEFAULT 0,
  `customer_feedback_request_delay_days` int(10) unsigned DEFAULT NULL,
  `budget_conversion_target` decimal(5,2) DEFAULT NULL,
  `payment_recovery_target` decimal(5,2) DEFAULT NULL,
  `mail_mailer` varchar(30) DEFAULT NULL,
  `mail_host` varchar(255) DEFAULT NULL,
  `mail_port` smallint(5) unsigned DEFAULT NULL,
  `mail_username` varchar(255) DEFAULT NULL,
  `mail_password` text DEFAULT NULL,
  `mail_encryption` varchar(20) DEFAULT NULL,
  `mail_from_address` varchar(255) DEFAULT NULL,
  `mail_from_name` varchar(255) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `public_order_access_key_required` tinyint(1) NOT NULL DEFAULT 0,
  PRIMARY KEY (`id`),
  KEY `others_tenant_id_foreign` (`tenant_id`),
  CONSTRAINT `others_tenant_id_foreign` FOREIGN KEY (`tenant_id`) REFERENCES `tenants` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `part_movements`
--

DROP TABLE IF EXISTS `part_movements`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `part_movements` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `tenant_id` bigint(20) unsigned DEFAULT NULL,
  `part_id` bigint(20) unsigned NOT NULL,
  `order_id` bigint(20) unsigned DEFAULT NULL,
  `user_id` bigint(20) unsigned NOT NULL,
  `movement_type` varchar(30) NOT NULL,
  `quantity` int(11) NOT NULL,
  `reason` varchar(255) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `part_movements_part_id_foreign` (`part_id`),
  KEY `part_movements_user_id_foreign` (`user_id`),
  KEY `part_movements_tenant_part_created_idx` (`tenant_id`,`part_id`,`created_at`),
  KEY `part_movements_order_created_idx` (`order_id`,`created_at`),
  CONSTRAINT `part_movements_order_id_foreign` FOREIGN KEY (`order_id`) REFERENCES `orders` (`id`) ON DELETE SET NULL,
  CONSTRAINT `part_movements_part_id_foreign` FOREIGN KEY (`part_id`) REFERENCES `parts` (`id`) ON DELETE CASCADE,
  CONSTRAINT `part_movements_tenant_id_foreign` FOREIGN KEY (`tenant_id`) REFERENCES `tenants` (`id`) ON DELETE CASCADE,
  CONSTRAINT `part_movements_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=8 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `parts`
--

DROP TABLE IF EXISTS `parts`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `parts` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `tenant_id` bigint(20) unsigned DEFAULT NULL,
  `type` varchar(50) DEFAULT NULL,
  `is_sellable` tinyint(1) DEFAULT NULL,
  `category` varchar(255) NOT NULL,
  `part_number` varchar(255) NOT NULL,
  `reference_number` varchar(255) NOT NULL,
  `name` varchar(255) NOT NULL,
  `description` text DEFAULT NULL,
  `image` varchar(255) DEFAULT NULL,
  `ncm` varchar(8) DEFAULT NULL,
  `cfop` varchar(4) DEFAULT NULL,
  `manufacturer` varchar(255) DEFAULT NULL,
  `model_compatibility` text DEFAULT NULL,
  `cost_price` decimal(10,2) NOT NULL,
  `sale_price` decimal(10,2) NOT NULL,
  `quantity` int(10) unsigned NOT NULL DEFAULT 0,
  `minimum_stock_level` int(10) unsigned NOT NULL DEFAULT 0,
  `location` varchar(255) DEFAULT NULL,
  `status` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `parts_tenant_part_number_unique` (`tenant_id`,`part_number`),
  UNIQUE KEY `parts_tenant_reference_number_unique` (`tenant_id`,`reference_number`),
  KEY `parts_tenant_type_created_idx` (`tenant_id`,`type`,`created_at`),
  KEY `parts_tenant_sellable_idx` (`tenant_id`,`is_sellable`),
  CONSTRAINT `parts_tenant_id_foreign` FOREIGN KEY (`tenant_id`) REFERENCES `tenants` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `password_reset_tokens`
--

DROP TABLE IF EXISTS `password_reset_tokens`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `password_reset_tokens` (
  `email` varchar(255) NOT NULL,
  `token` varchar(255) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `payments`
--

DROP TABLE IF EXISTS `payments`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `payments` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `tenant_id` bigint(20) unsigned NOT NULL,
  `gateway` varchar(255) NOT NULL DEFAULT 'mercadopago',
  `payment_id` varchar(255) NOT NULL,
  `amount` decimal(10,2) NOT NULL,
  `status` varchar(255) NOT NULL,
  `idempotency_key` varchar(255) NOT NULL,
  `expires_at` timestamp NULL DEFAULT NULL,
  `raw_response` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`raw_response`)),
  `admin_fiscal_document_id` bigint(20) unsigned DEFAULT NULL,
  `invoice_email` varchar(255) DEFAULT NULL,
  `invoice_email_sent_at` timestamp NULL DEFAULT NULL,
  `invoice_email_error` text DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `payments_payment_id_unique` (`payment_id`),
  UNIQUE KEY `payments_idempotency_key_unique` (`idempotency_key`),
  KEY `payments_tenant_status_created_idx` (`tenant_id`,`status`,`created_at`),
  KEY `payments_gateway_status_idx` (`gateway`,`status`),
  KEY `payments_expires_at_idx` (`expires_at`),
  KEY `payments_admin_fiscal_document_id_foreign` (`admin_fiscal_document_id`),
  CONSTRAINT `payments_admin_fiscal_document_id_foreign` FOREIGN KEY (`admin_fiscal_document_id`) REFERENCES `admin_fiscal_documents` (`id`) ON DELETE SET NULL,
  CONSTRAINT `payments_tenant_id_foreign` FOREIGN KEY (`tenant_id`) REFERENCES `tenants` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `periods`
--

DROP TABLE IF EXISTS `periods`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `periods` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `plan_id` bigint(20) unsigned DEFAULT NULL,
  `name` varchar(255) NOT NULL,
  `interval` varchar(255) NOT NULL,
  `interval_count` int(11) NOT NULL,
  `price` decimal(8,2) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `periods_plan_id_foreign` (`plan_id`),
  CONSTRAINT `periods_plan_id_foreign` FOREIGN KEY (`plan_id`) REFERENCES `plans` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `personal_access_tokens`
--

DROP TABLE IF EXISTS `personal_access_tokens`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `personal_access_tokens` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `tokenable_type` varchar(255) NOT NULL,
  `tokenable_id` bigint(20) unsigned NOT NULL,
  `name` varchar(255) NOT NULL,
  `token` varchar(64) NOT NULL,
  `abilities` text DEFAULT NULL,
  `last_used_at` timestamp NULL DEFAULT NULL,
  `expires_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `personal_access_tokens_token_unique` (`token`),
  KEY `personal_access_tokens_tokenable_type_tokenable_id_index` (`tokenable_type`,`tokenable_id`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `plan_leads`
--

DROP TABLE IF EXISTS `plan_leads`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `plan_leads` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(120) NOT NULL,
  `whatsapp` varchar(20) NOT NULL,
  `email` varchar(255) NOT NULL,
  `source` varchar(30) NOT NULL DEFAULT 'landing',
  `status` varchar(30) NOT NULL DEFAULT 'new',
  `notes` text DEFAULT NULL,
  `last_contact_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `plans`
--

DROP TABLE IF EXISTS `plans`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `plans` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `slug` varchar(255) NOT NULL,
  `value` decimal(8,2) NOT NULL,
  `billing_months` tinyint(3) unsigned DEFAULT NULL,
  `description` text NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `plans_slug_idx` (`slug`)
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `receipts`
--

DROP TABLE IF EXISTS `receipts`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `receipts` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `tenant_id` bigint(20) unsigned DEFAULT NULL,
  `receivingequipment` text DEFAULT NULL,
  `equipmentdelivery` text DEFAULT NULL,
  `budgetissuance` text DEFAULT NULL,
  `maintenance_contract_template` text DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `receipts_tenant_id_foreign` (`tenant_id`),
  CONSTRAINT `receipts_tenant_id_foreign` FOREIGN KEY (`tenant_id`) REFERENCES `tenants` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `sale_items`
--

DROP TABLE IF EXISTS `sale_items`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `sale_items` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `sale_id` bigint(20) unsigned NOT NULL,
  `part_id` bigint(20) unsigned NOT NULL,
  `quantity` int(11) NOT NULL,
  `unit_price` decimal(10,2) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `sale_items_part_id_foreign` (`part_id`),
  KEY `sale_items_sale_part_idx` (`sale_id`,`part_id`),
  CONSTRAINT `sale_items_part_id_foreign` FOREIGN KEY (`part_id`) REFERENCES `parts` (`id`) ON DELETE CASCADE,
  CONSTRAINT `sale_items_sale_id_foreign` FOREIGN KEY (`sale_id`) REFERENCES `sales` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `sale_logs`
--

DROP TABLE IF EXISTS `sale_logs`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `sale_logs` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `sale_id` bigint(20) unsigned NOT NULL,
  `user_id` bigint(20) unsigned DEFAULT NULL,
  `action` varchar(80) NOT NULL,
  `data` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`data`)),
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `sale_logs_user_id_foreign` (`user_id`),
  KEY `sale_logs_sale_id_created_at_index` (`sale_id`,`created_at`),
  CONSTRAINT `sale_logs_sale_id_foreign` FOREIGN KEY (`sale_id`) REFERENCES `sales` (`id`) ON DELETE CASCADE,
  CONSTRAINT `sale_logs_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `sales`
--

DROP TABLE IF EXISTS `sales`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `sales` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `sales_number` int(11) NOT NULL,
  `tenant_id` bigint(20) unsigned NOT NULL,
  `customer_id` bigint(20) unsigned DEFAULT NULL,
  `cash_session_id` bigint(20) unsigned DEFAULT NULL,
  `total_amount` decimal(10,2) NOT NULL,
  `paid_amount` decimal(10,2) NOT NULL DEFAULT 0.00,
  `financial_status` varchar(20) NOT NULL DEFAULT 'pending',
  `payment_method` varchar(30) NOT NULL DEFAULT 'pix',
  `status` enum('completed','cancelled') NOT NULL DEFAULT 'completed',
  `cancelled_at` timestamp NULL DEFAULT NULL,
  `cancelled_by` bigint(20) unsigned DEFAULT NULL,
  `cancel_reason` text DEFAULT NULL,
  `fiscal_document_number` varchar(120) DEFAULT NULL,
  `fiscal_document_key` varchar(120) DEFAULT NULL,
  `fiscal_document_url` varchar(500) DEFAULT NULL,
  `fiscal_issued_at` timestamp NULL DEFAULT NULL,
  `fiscal_registered_by` bigint(20) unsigned DEFAULT NULL,
  `fiscal_notes` text DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `sales_customer_id_foreign` (`customer_id`),
  KEY `sales_tenant_number_idx` (`tenant_id`,`sales_number`),
  KEY `sales_tenant_status_created_idx` (`tenant_id`,`status`,`created_at`),
  KEY `sales_cash_session_id_foreign` (`cash_session_id`),
  KEY `sales_tenant_id_cash_session_id_index` (`tenant_id`,`cash_session_id`),
  KEY `sales_tenant_id_payment_method_index` (`tenant_id`,`payment_method`),
  KEY `sales_cancelled_by_foreign` (`cancelled_by`),
  KEY `sales_tenant_id_status_cancelled_at_index` (`tenant_id`,`status`,`cancelled_at`),
  KEY `sales_tenant_id_financial_status_index` (`tenant_id`,`financial_status`),
  KEY `sales_fiscal_registered_by_foreign` (`fiscal_registered_by`),
  CONSTRAINT `sales_cancelled_by_foreign` FOREIGN KEY (`cancelled_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  CONSTRAINT `sales_cash_session_id_foreign` FOREIGN KEY (`cash_session_id`) REFERENCES `cash_sessions` (`id`) ON DELETE SET NULL,
  CONSTRAINT `sales_customer_id_foreign` FOREIGN KEY (`customer_id`) REFERENCES `customers` (`id`) ON DELETE CASCADE,
  CONSTRAINT `sales_fiscal_registered_by_foreign` FOREIGN KEY (`fiscal_registered_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  CONSTRAINT `sales_tenant_id_foreign` FOREIGN KEY (`tenant_id`) REFERENCES `tenants` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `schedule_images`
--

DROP TABLE IF EXISTS `schedule_images`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `schedule_images` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `tenant_id` bigint(20) unsigned DEFAULT NULL,
  `schedule_id` bigint(20) unsigned NOT NULL,
  `user_id` bigint(20) unsigned DEFAULT NULL,
  `filename` varchar(255) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `schedule_images_tenant_id_foreign` (`tenant_id`),
  KEY `schedule_images_schedule_id_foreign` (`schedule_id`),
  KEY `schedule_images_user_id_foreign` (`user_id`),
  CONSTRAINT `schedule_images_schedule_id_foreign` FOREIGN KEY (`schedule_id`) REFERENCES `schedules` (`id`) ON DELETE CASCADE,
  CONSTRAINT `schedule_images_tenant_id_foreign` FOREIGN KEY (`tenant_id`) REFERENCES `tenants` (`id`) ON DELETE CASCADE,
  CONSTRAINT `schedule_images_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `schedules`
--

DROP TABLE IF EXISTS `schedules`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `schedules` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `tenant_id` bigint(20) unsigned DEFAULT NULL,
  `customer_id` bigint(20) unsigned DEFAULT NULL,
  `order_id` bigint(20) unsigned DEFAULT NULL,
  `user_id` bigint(20) unsigned DEFAULT NULL,
  `schedules_number` int(11) NOT NULL,
  `schedules` datetime NOT NULL,
  `service` varchar(500) DEFAULT NULL,
  `details` text NOT NULL,
  `material_checklist` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`material_checklist`)),
  `technician_checklist` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`technician_checklist`)),
  `technician_checklist_items` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`technician_checklist_items`)),
  `technician_checklist_completed_at` timestamp NULL DEFAULT NULL,
  `technician_diagnosis` text DEFAULT NULL,
  `technician_solution` text DEFAULT NULL,
  `technician_observations` text DEFAULT NULL,
  `technician_report_updated_at` timestamp NULL DEFAULT NULL,
  `service_closure_status` varchar(20) DEFAULT NULL,
  `service_closure_requested_at` timestamp NULL DEFAULT NULL,
  `service_closure_requested_by` bigint(20) unsigned DEFAULT NULL,
  `service_closure_amount` decimal(10,2) DEFAULT NULL,
  `service_closure_priced_at` timestamp NULL DEFAULT NULL,
  `service_closure_priced_by` bigint(20) unsigned DEFAULT NULL,
  `status` tinyint(4) NOT NULL DEFAULT 1,
  `observations` text DEFAULT NULL,
  `responsible_technician` varchar(50) DEFAULT NULL,
  `send_to_technician` tinyint(1) NOT NULL DEFAULT 0,
  `check_in_at` timestamp NULL DEFAULT NULL,
  `check_in_latitude` decimal(10,7) DEFAULT NULL,
  `check_in_longitude` decimal(10,7) DEFAULT NULL,
  `check_in_observations` text DEFAULT NULL,
  `check_out_at` timestamp NULL DEFAULT NULL,
  `check_out_latitude` decimal(10,7) DEFAULT NULL,
  `check_out_longitude` decimal(10,7) DEFAULT NULL,
  `check_out_observations` text DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `local_payment_received` tinyint(1) NOT NULL DEFAULT 0,
  `local_payment_amount` decimal(10,2) DEFAULT NULL,
  `local_payment_received_at` timestamp NULL DEFAULT NULL,
  `local_payment_user_id` bigint(20) unsigned DEFAULT NULL,
  `local_payment_cash_session_id` bigint(20) unsigned DEFAULT NULL,
  `local_payment_cash_registered_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `schedules_customer_id_foreign` (`customer_id`),
  KEY `schedules_user_id_foreign` (`user_id`),
  KEY `schedules_tenant_status_id_idx` (`tenant_id`,`status`,`id`),
  KEY `schedules_tenant_datetime_idx` (`tenant_id`,`schedules`),
  KEY `schedules_order_id_foreign` (`order_id`),
  KEY `schedules_local_payment_user_id_foreign` (`local_payment_user_id`),
  KEY `schedules_local_payment_cash_session_id_foreign` (`local_payment_cash_session_id`),
  KEY `schedules_service_closure_requested_by_foreign` (`service_closure_requested_by`),
  KEY `schedules_service_closure_priced_by_foreign` (`service_closure_priced_by`),
  CONSTRAINT `schedules_customer_id_foreign` FOREIGN KEY (`customer_id`) REFERENCES `customers` (`id`) ON DELETE CASCADE,
  CONSTRAINT `schedules_local_payment_cash_session_id_foreign` FOREIGN KEY (`local_payment_cash_session_id`) REFERENCES `cash_sessions` (`id`) ON DELETE SET NULL,
  CONSTRAINT `schedules_local_payment_user_id_foreign` FOREIGN KEY (`local_payment_user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  CONSTRAINT `schedules_order_id_foreign` FOREIGN KEY (`order_id`) REFERENCES `orders` (`id`) ON DELETE SET NULL,
  CONSTRAINT `schedules_service_closure_priced_by_foreign` FOREIGN KEY (`service_closure_priced_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  CONSTRAINT `schedules_service_closure_requested_by_foreign` FOREIGN KEY (`service_closure_requested_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  CONSTRAINT `schedules_tenant_id_foreign` FOREIGN KEY (`tenant_id`) REFERENCES `tenants` (`id`) ON DELETE CASCADE,
  CONSTRAINT `schedules_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `sessions`
--

DROP TABLE IF EXISTS `sessions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `sessions` (
  `id` varchar(255) NOT NULL,
  `user_id` bigint(20) unsigned DEFAULT NULL,
  `ip_address` varchar(45) DEFAULT NULL,
  `user_agent` text DEFAULT NULL,
  `payload` longtext NOT NULL,
  `last_activity` int(11) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `sessions_user_id_index` (`user_id`),
  KEY `sessions_last_activity_index` (`last_activity`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `settings`
--

DROP TABLE IF EXISTS `settings`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `settings` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `tenant_id` bigint(20) unsigned DEFAULT NULL,
  `name` varchar(100) DEFAULT NULL,
  `logo` varchar(100) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `settings_tenant_id_foreign` (`tenant_id`),
  CONSTRAINT `settings_tenant_id_foreign` FOREIGN KEY (`tenant_id`) REFERENCES `tenants` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `technician_push_tokens`
--

DROP TABLE IF EXISTS `technician_push_tokens`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `technician_push_tokens` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `tenant_id` bigint(20) unsigned DEFAULT NULL,
  `user_id` bigint(20) unsigned NOT NULL,
  `expo_push_token` varchar(255) NOT NULL,
  `platform` varchar(30) DEFAULT NULL,
  `device_name` varchar(255) DEFAULT NULL,
  `last_used_at` timestamp NULL DEFAULT NULL,
  `disabled_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `technician_push_tokens_expo_push_token_unique` (`expo_push_token`),
  KEY `technician_push_tokens_user_id_foreign` (`user_id`),
  KEY `technician_push_tokens_tenant_id_user_id_index` (`tenant_id`,`user_id`),
  KEY `technician_push_tokens_disabled_at_index` (`disabled_at`),
  CONSTRAINT `technician_push_tokens_tenant_id_foreign` FOREIGN KEY (`tenant_id`) REFERENCES `tenants` (`id`) ON DELETE CASCADE,
  CONSTRAINT `technician_push_tokens_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `technician_schedule_status_logs`
--

DROP TABLE IF EXISTS `technician_schedule_status_logs`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `technician_schedule_status_logs` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `tenant_id` bigint(20) unsigned DEFAULT NULL,
  `schedule_id` bigint(20) unsigned NOT NULL,
  `order_id` bigint(20) unsigned DEFAULT NULL,
  `user_id` bigint(20) unsigned DEFAULT NULL,
  `status` tinyint(3) unsigned NOT NULL,
  `technician_status` varchar(40) DEFAULT NULL,
  `status_label` varchar(80) NOT NULL,
  `latitude` decimal(10,7) DEFAULT NULL,
  `longitude` decimal(10,7) DEFAULT NULL,
  `observations` text DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `technician_schedule_status_logs_order_id_foreign` (`order_id`),
  KEY `technician_schedule_status_logs_user_id_foreign` (`user_id`),
  KEY `tech_schedule_status_logs_schedule_created_idx` (`schedule_id`,`created_at`),
  KEY `tech_schedule_status_logs_tenant_user_created_idx` (`tenant_id`,`user_id`,`created_at`),
  CONSTRAINT `technician_schedule_status_logs_order_id_foreign` FOREIGN KEY (`order_id`) REFERENCES `orders` (`id`) ON DELETE SET NULL,
  CONSTRAINT `technician_schedule_status_logs_schedule_id_foreign` FOREIGN KEY (`schedule_id`) REFERENCES `schedules` (`id`) ON DELETE CASCADE,
  CONSTRAINT `technician_schedule_status_logs_tenant_id_foreign` FOREIGN KEY (`tenant_id`) REFERENCES `tenants` (`id`) ON DELETE CASCADE,
  CONSTRAINT `technician_schedule_status_logs_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `tenant_feedbacks`
--

DROP TABLE IF EXISTS `tenant_feedbacks`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `tenant_feedbacks` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `tenant_id` bigint(20) unsigned NOT NULL,
  `feedback_token` char(36) NOT NULL,
  `feedback_source` varchar(40) NOT NULL,
  `feedback_status` varchar(20) NOT NULL DEFAULT 'pending',
  `feedback_rating` tinyint(3) unsigned DEFAULT NULL,
  `feedback_comment` text DEFAULT NULL,
  `feedback_sent_at` timestamp NULL DEFAULT NULL,
  `feedback_opened_at` timestamp NULL DEFAULT NULL,
  `feedback_submitted_at` timestamp NULL DEFAULT NULL,
  `feedback_expires_at` timestamp NULL DEFAULT NULL,
  `feedback_recovery_assigned_to` bigint(20) unsigned DEFAULT NULL,
  `feedback_recovery_status` varchar(30) DEFAULT NULL,
  `feedback_recovery_notes` text DEFAULT NULL,
  `feedback_recovery_updated_at` timestamp NULL DEFAULT NULL,
  `testimonial_consent_at` timestamp NULL DEFAULT NULL,
  `testimonial_status` varchar(30) DEFAULT NULL,
  `testimonial_public_name` varchar(120) DEFAULT NULL,
  `testimonial_public_role` varchar(120) DEFAULT NULL,
  `testimonial_excerpt` text DEFAULT NULL,
  `testimonial_published_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `tenant_feedbacks_feedback_token_unique` (`feedback_token`),
  KEY `tenant_feedbacks_feedback_recovery_assigned_to_foreign` (`feedback_recovery_assigned_to`),
  KEY `tenant_feedbacks_tenant_id_feedback_status_index` (`tenant_id`,`feedback_status`),
  KEY `tenant_feedbacks_feedback_source_feedback_status_index` (`feedback_source`,`feedback_status`),
  KEY `tenant_feedbacks_feedback_submitted_at_index` (`feedback_submitted_at`),
  KEY `tenant_feedbacks_feedback_recovery_status_index` (`feedback_recovery_status`),
  KEY `tenant_feedbacks_testimonial_status_index` (`testimonial_status`),
  CONSTRAINT `tenant_feedbacks_feedback_recovery_assigned_to_foreign` FOREIGN KEY (`feedback_recovery_assigned_to`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  CONSTRAINT `tenant_feedbacks_tenant_id_foreign` FOREIGN KEY (`tenant_id`) REFERENCES `tenants` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `tenant_improvement_requests`
--

DROP TABLE IF EXISTS `tenant_improvement_requests`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `tenant_improvement_requests` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `tenant_id` bigint(20) unsigned NOT NULL,
  `user_id` bigint(20) unsigned DEFAULT NULL,
  `request_type` varchar(30) NOT NULL DEFAULT 'improvement',
  `status` varchar(30) NOT NULL DEFAULT 'new',
  `title` varchar(160) NOT NULL,
  `description` text NOT NULL,
  `admin_notes` text DEFAULT NULL,
  `reviewed_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `tenant_improvement_requests_user_id_foreign` (`user_id`),
  KEY `tenant_improvement_requests_tenant_id_status_index` (`tenant_id`,`status`),
  KEY `tenant_improvement_requests_request_type_status_index` (`request_type`,`status`),
  CONSTRAINT `tenant_improvement_requests_tenant_id_foreign` FOREIGN KEY (`tenant_id`) REFERENCES `tenants` (`id`) ON DELETE CASCADE,
  CONSTRAINT `tenant_improvement_requests_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `tenants`
--

DROP TABLE IF EXISTS `tenants`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `tenants` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `plan_id` bigint(20) unsigned DEFAULT NULL,
  `period_id` bigint(20) unsigned DEFAULT NULL,
  `name` varchar(255) NOT NULL,
  `company` varchar(255) DEFAULT NULL,
  `cnpj` varchar(255) NOT NULL,
  `email` varchar(255) NOT NULL,
  `phone` varchar(255) DEFAULT NULL,
  `whatsapp` varchar(255) DEFAULT NULL,
  `zip_code` varchar(50) DEFAULT NULL,
  `state` varchar(50) DEFAULT NULL,
  `city` varchar(50) DEFAULT NULL,
  `district` varchar(50) DEFAULT NULL,
  `street` varchar(50) DEFAULT NULL,
  `complement` varchar(50) DEFAULT NULL,
  `number` varchar(50) DEFAULT NULL,
  `status` tinyint(4) NOT NULL DEFAULT 1,
  `subscription_status` varchar(255) NOT NULL DEFAULT 'active',
  `automatic_fiscal_emission_enabled` tinyint(1) NOT NULL DEFAULT 0,
  `observations` text DEFAULT NULL,
  `expires_at` timestamp NULL DEFAULT NULL,
  `last_payment_id` varchar(100) DEFAULT NULL,
  `last_subscription_notice_key` varchar(100) DEFAULT NULL,
  `last_subscription_notice_sent_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `tenants_plan_id_foreign` (`plan_id`),
  KEY `tenants_subscription_expires_idx` (`subscription_status`,`expires_at`),
  KEY `tenants_period_id_foreign` (`period_id`),
  CONSTRAINT `tenants_period_id_foreign` FOREIGN KEY (`period_id`) REFERENCES `periods` (`id`) ON DELETE SET NULL,
  CONSTRAINT `tenants_plan_id_foreign` FOREIGN KEY (`plan_id`) REFERENCES `plans` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `users`
--

DROP TABLE IF EXISTS `users`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `users` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `tenant_id` bigint(20) unsigned DEFAULT NULL,
  `user_number` int(11) DEFAULT NULL,
  `name` varchar(255) NOT NULL,
  `email` varchar(255) NOT NULL,
  `telephone` varchar(255) DEFAULT NULL,
  `whatsapp` varchar(255) DEFAULT NULL,
  `avatar` varchar(255) DEFAULT NULL,
  `password` varchar(255) NOT NULL,
  `roles` tinyint(4) DEFAULT NULL,
  `commission_percentage` decimal(5,2) DEFAULT NULL,
  `can_view_all_orders` tinyint(1) NOT NULL DEFAULT 0,
  `status` tinyint(4) DEFAULT NULL,
  `email_verified_at` timestamp NULL DEFAULT NULL,
  `last_login_at` timestamp NULL DEFAULT NULL,
  `remember_token` varchar(100) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `users_email_unique` (`email`),
  KEY `users_tenant_roles_status_idx` (`tenant_id`,`roles`,`status`),
  KEY `users_last_login_at_index` (`last_login_at`),
  CONSTRAINT `users_tenant_id_foreign` FOREIGN KEY (`tenant_id`) REFERENCES `tenants` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `whatsapp_messages`
--

DROP TABLE IF EXISTS `whatsapp_messages`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `whatsapp_messages` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `tenant_id` bigint(20) unsigned DEFAULT NULL,
  `generatedbudget` text DEFAULT NULL,
  `servicecompleted` text DEFAULT NULL,
  `feedback` text DEFAULT NULL,
  `defaultmessage` text DEFAULT NULL,
  `budgetfollowup` text DEFAULT NULL,
  `pendingpayment` text DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `whatsapp_messages_tenant_id_foreign` (`tenant_id`),
  CONSTRAINT `whatsapp_messages_tenant_id_foreign` FOREIGN KEY (`tenant_id`) REFERENCES `tenants` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping routines for database 'saasos'
--
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed on 2026-09-15  8:26:30
