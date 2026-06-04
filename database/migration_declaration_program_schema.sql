-- Schéma : programme Déclaration + structures BDD (grilles CMS)
--
-- Équivalent SQL des migrations CodeIgniter :
--   2026-05-28-180000_CreateDeclarationItemsTable
--   2026-05-28-210000_AddDeclarationItemBodyBlocks
--   2026-05-28-131500_AddStructureUnitMediaId
--   2026-05-28-132000_AddSectorMediaId
--   2026-05-29-120000_CreateStructureUnitsTable
--   2026-05-29-123000_AddStructureUnitRoleAndSubtitles
--
-- Prérequis : MySQL/MariaDB, base existante (cms_pages, sectors déjà présents).
-- Usage : mysql -u USER -p BASE < database/migration_declaration_program_schema.sql
--
-- Configuration (.env) : rien à ajouter pour l’instant (routes /declaration sur site principal).
-- Sous-domaine futur : voir commentaires dans env.example et migration_declaration_program_seed.sql.
--
-- Note : les ALTER sur `sectors` peuvent renvoyer #1060 si les colonnes existent déjà — ignorer.

SET NAMES utf8mb4;
SET FOREIGN_KEY_CHECKS = 0;

-- ---------------------------------------------------------------------------
-- declaration_items
-- ---------------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `declaration_items` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `slug` varchar(160) NOT NULL,
  `locale` varchar(5) NOT NULL DEFAULT 'fr',
  `translation_group` varchar(80) DEFAULT NULL,
  `title` varchar(255) NOT NULL,
  `summary` text DEFAULT NULL,
  `body` mediumtext DEFAULT NULL,
  `body_content_mode` varchar(16) NOT NULL DEFAULT 'blocks',
  `body_blocks` mediumtext DEFAULT NULL,
  `kind` varchar(32) NOT NULL DEFAULT 'official',
  `list_section` varchar(32) NOT NULL DEFAULT 'declarations',
  `meta_line` varchar(160) NOT NULL DEFAULT '',
  `band_label` varchar(120) NOT NULL DEFAULT '',
  `badge_label` varchar(80) NOT NULL DEFAULT '',
  `cta_label` varchar(120) NOT NULL DEFAULT '',
  `cta_href` varchar(255) NOT NULL DEFAULT '',
  `sort_order` int NOT NULL DEFAULT 0,
  `publication_state` varchar(32) NOT NULL DEFAULT 'draft',
  `published_at` datetime DEFAULT NULL,
  `created_at` datetime DEFAULT NULL,
  `updated_at` datetime DEFAULT NULL,
  `deleted_at` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `slug_locale` (`slug`,`locale`),
  KEY `publication_state_locale_list_section_sort_order` (`publication_state`,`locale`,`list_section`,`sort_order`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Colonnes corps détail (si table créée avant 210000)
SET @db := DATABASE();

SET @sql := IF(
  (SELECT COUNT(*) FROM information_schema.COLUMNS
   WHERE TABLE_SCHEMA = @db AND TABLE_NAME = 'declaration_items' AND COLUMN_NAME = 'body') = 0,
  'ALTER TABLE `declaration_items` ADD COLUMN `body` mediumtext NULL AFTER `summary`',
  'SELECT 1'
);
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

SET @sql := IF(
  (SELECT COUNT(*) FROM information_schema.COLUMNS
   WHERE TABLE_SCHEMA = @db AND TABLE_NAME = 'declaration_items' AND COLUMN_NAME = 'body_content_mode') = 0,
  'ALTER TABLE `declaration_items` ADD COLUMN `body_content_mode` varchar(16) NOT NULL DEFAULT ''blocks'' AFTER `body`',
  'SELECT 1'
);
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

SET @sql := IF(
  (SELECT COUNT(*) FROM information_schema.COLUMNS
   WHERE TABLE_SCHEMA = @db AND TABLE_NAME = 'declaration_items' AND COLUMN_NAME = 'body_blocks') = 0,
  'ALTER TABLE `declaration_items` ADD COLUMN `body_blocks` mediumtext NULL AFTER `body_content_mode`',
  'SELECT 1'
);
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

-- ---------------------------------------------------------------------------
-- structure_units
-- ---------------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `structure_units` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `code` varchar(32) NOT NULL,
  `unit_role` varchar(16) NOT NULL DEFAULT 'function',
  `title_fr` varchar(255) NOT NULL,
  `title_en` varchar(255) NOT NULL,
  `subtitle_fr` text DEFAULT NULL,
  `subtitle_en` text DEFAULT NULL,
  `description_fr` text DEFAULT NULL,
  `description_en` text DEFAULT NULL,
  `contact_email` varchar(190) NOT NULL,
  `media_id` int unsigned DEFAULT NULL,
  `media_alt` varchar(255) DEFAULT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `sort_order` int NOT NULL DEFAULT 0,
  `created_at` datetime DEFAULT NULL,
  `updated_at` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `code` (`code`),
  KEY `is_active_sort_order` (`is_active`,`sort_order`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

SET @sql := IF(
  (SELECT COUNT(*) FROM information_schema.COLUMNS
   WHERE TABLE_SCHEMA = @db AND TABLE_NAME = 'structure_units' AND COLUMN_NAME = 'unit_role') = 0,
  'ALTER TABLE `structure_units` ADD COLUMN `unit_role` varchar(16) NOT NULL DEFAULT ''function'' AFTER `code`',
  'SELECT 1'
);
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

SET @sql := IF(
  (SELECT COUNT(*) FROM information_schema.COLUMNS
   WHERE TABLE_SCHEMA = @db AND TABLE_NAME = 'structure_units' AND COLUMN_NAME = 'subtitle_fr') = 0,
  'ALTER TABLE `structure_units` ADD COLUMN `subtitle_fr` text NULL AFTER `title_en`',
  'SELECT 1'
);
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

SET @sql := IF(
  (SELECT COUNT(*) FROM information_schema.COLUMNS
   WHERE TABLE_SCHEMA = @db AND TABLE_NAME = 'structure_units' AND COLUMN_NAME = 'subtitle_en') = 0,
  'ALTER TABLE `structure_units` ADD COLUMN `subtitle_en` text NULL AFTER `subtitle_fr`',
  'SELECT 1'
);
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

SET @sql := IF(
  (SELECT COUNT(*) FROM information_schema.COLUMNS
   WHERE TABLE_SCHEMA = @db AND TABLE_NAME = 'structure_units' AND COLUMN_NAME = 'media_id') = 0,
  'ALTER TABLE `structure_units` ADD COLUMN `media_id` int unsigned NULL AFTER `contact_email`, ADD COLUMN `media_alt` varchar(255) NULL AFTER `media_id`',
  'SELECT 1'
);
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

-- ---------------------------------------------------------------------------
-- sectors — médias optionnels (grille secteurs)
-- ---------------------------------------------------------------------------
SET @sql := IF(
  (SELECT COUNT(*) FROM information_schema.TABLES
   WHERE TABLE_SCHEMA = @db AND TABLE_NAME = 'sectors') = 0,
  'SELECT 1',
  IF(
    (SELECT COUNT(*) FROM information_schema.COLUMNS
     WHERE TABLE_SCHEMA = @db AND TABLE_NAME = 'sectors' AND COLUMN_NAME = 'media_id') = 0,
    'ALTER TABLE `sectors` ADD COLUMN `media_id` int unsigned NULL AFTER `contact_email`, ADD COLUMN `media_alt` varchar(255) NULL AFTER `media_id`',
    'SELECT 1'
  )
);
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

SET FOREIGN_KEY_CHECKS = 1;
