<?php
// Generate SQL to add high-value schema enhancements safely
// Usage: php database/tools/generate_schema_enhancements.php

declare(strict_types=1);

error_reporting(E_ALL);
ini_set('display_errors', '1');

$projectRoot = dirname(__DIR__, 2);
require_once $projectRoot . '/includes/config/config.php';
require_once $projectRoot . '/includes/config/constants.php';

if (!isset($con) || !$con instanceof mysqli) {
    fwrite(STDERR, "[ERROR] Database connection (mysqli) not available.\n");
    exit(1);
}

$dbName = DB_NAME ?? null;
if (!$dbName) { fwrite(STDERR, "[ERROR] DB_NAME not defined.\n"); exit(1); }

function fetchOneAssoc(mysqli $con, string $sql, array $params = []): ?array {
    $stmt = $con->prepare($sql);
    if (!$stmt) return null;
    if ($params) { $types = str_repeat('s', count($params)); $stmt->bind_param($types, ...$params); }
    if (!$stmt->execute()) return null;
    $res = $stmt->get_result();
    $row = $res ? $res->fetch_assoc() : null;
    $stmt->close();
    return $row ?: null;
}

function tableExists(mysqli $con, string $db, string $table): bool {
    $r = fetchOneAssoc($con, 'SELECT 1 AS ok FROM information_schema.TABLES WHERE TABLE_SCHEMA=? AND TABLE_NAME=?', [$db, $table]);
    return (bool)$r;
}

function columnInfo(mysqli $con, string $db, string $table, string $column): ?array {
    return fetchOneAssoc($con, 'SELECT COLUMN_NAME, DATA_TYPE, IS_NULLABLE, COLUMN_TYPE FROM information_schema.COLUMNS WHERE TABLE_SCHEMA=? AND TABLE_NAME=? AND COLUMN_NAME=?', [$db, $table, $column]);
}

function primaryKeyFor(mysqli $con, string $db, string $table): ?string {
    $pk = fetchOneAssoc($con, "SELECT k.COLUMN_NAME FROM information_schema.TABLE_CONSTRAINTS tc JOIN information_schema.KEY_COLUMN_USAGE k ON tc.CONSTRAINT_NAME=k.CONSTRAINT_NAME AND tc.TABLE_SCHEMA=k.TABLE_SCHEMA AND tc.TABLE_NAME=k.TABLE_NAME WHERE tc.TABLE_SCHEMA=? AND tc.TABLE_NAME=? AND tc.CONSTRAINT_TYPE='PRIMARY KEY' LIMIT 1", [$db, $table]);
    return $pk['COLUMN_NAME'] ?? null;
}

function referencedColumnType(mysqli $con, string $db, string $table, string $column): string {
    $col = columnInfo($con, $db, $table, $column);
    // Fallback to BIGINT UNSIGNED for ids
    return $col && !empty($col['COLUMN_TYPE']) ? $col['COLUMN_TYPE'] : 'bigint(20) unsigned';
}

function isNullable(?array $col): bool { return ($col && strtoupper((string)$col['IS_NULLABLE']) === 'YES'); }

function alignColumnType(array &$sqlOut, mysqli $con, string $db, string $table, string $column, string $refTable, string $refColumn): void {
    $col = columnInfo($con, $db, $table, $column);
    $refType = referencedColumnType($con, $db, $refTable, $refColumn);
    if ($col && isset($col['COLUMN_TYPE']) && strtolower((string)$col['COLUMN_TYPE']) !== strtolower((string)$refType)) {
        $nullSpec = isNullable($col) ? 'NULL' : 'NOT NULL';
        $sqlOut[] = "ALTER TABLE `{$table}` MODIFY `{$column}` {$refType} {$nullSpec};";
    }
}

function emitCreateIfMissing(array &$sqlOut, mysqli $con, string $db, string $tableName, string $createSql): void {
    if (!tableExists($con, $db, $tableName)) {
        $sqlOut[] = $createSql;
    } else {
        $sqlOut[] = "-- SKIP: `$tableName` already exists";
    }
}

function fkExists(mysqli $con, string $db, string $table, string $fkName): bool {
    $r = fetchOneAssoc($con, "SELECT 1 AS ok FROM information_schema.TABLE_CONSTRAINTS WHERE TABLE_SCHEMA=? AND TABLE_NAME=? AND CONSTRAINT_NAME=? AND CONSTRAINT_TYPE='FOREIGN KEY'", [$db, $table, $fkName]);
    return (bool)$r;
}

function indexExists(mysqli $con, string $db, string $table, string $indexName): bool {
    $r = fetchOneAssoc($con, 'SELECT 1 AS ok FROM information_schema.STATISTICS WHERE TABLE_SCHEMA=? AND TABLE_NAME=? AND INDEX_NAME=? LIMIT 1', [$db, $table, $indexName]);
    return (bool)$r;
}

$sqlOut = [];
$sqlOut[] = "-- 004_schema_enhancements.sql";
$sqlOut[] = "-- Generated: " . date('Y-m-d H:i:s');
$sqlOut[] = "-- Create high-value auxiliary tables and relationships (safe checks)";
$sqlOut[] = "";

// Resolve canonical id types for references
$userIdType = referencedColumnType($con, $dbName, 'users', 'id');
$propertyIdType = referencedColumnType($con, $dbName, 'properties', 'id');

// addresses
$sqlOut[] = "-- addresses";
$addressesCreate = <<<SQL
CREATE TABLE `addresses` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `user_id` {$userIdType} NULL,
  `property_id` {$propertyIdType} NULL,
  `line1` varchar(255) NOT NULL,
  `line2` varchar(255) NULL,
  `city` varchar(120) NOT NULL,
  `state` varchar(120) NULL,
  `country` varchar(120) NOT NULL,
  `postal_code` varchar(32) NOT NULL,
  `address_type` enum('home','office','billing','shipping','other') NOT NULL DEFAULT 'other',
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
SQL;
emitCreateIfMissing($sqlOut, $con, $dbName, 'addresses', $addressesCreate);
// Indexes & FKs (only if columns exist)
if (columnInfo($con, $dbName, 'addresses', 'user_id')) {
    alignColumnType($sqlOut, $con, $dbName, 'addresses', 'user_id', 'users', 'id');
    if (!indexExists($con, $dbName, 'addresses', 'ix_addresses_user_id')) { $sqlOut[] = "ALTER TABLE `addresses` ADD INDEX `ix_addresses_user_id` (`user_id`);"; }
    if (primaryKeyFor($con, $dbName, 'users') === 'id' && !fkExists($con, $dbName, 'addresses', 'fk_addresses_user_id')) {
        $sqlOut[] = "ALTER TABLE `addresses` ADD CONSTRAINT `fk_addresses_user_id` FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE SET NULL ON UPDATE CASCADE;";
    }
}
if (columnInfo($con, $dbName, 'addresses', 'property_id')) {
    alignColumnType($sqlOut, $con, $dbName, 'addresses', 'property_id', 'properties', 'id');
    if (!indexExists($con, $dbName, 'addresses', 'ix_addresses_property_id')) { $sqlOut[] = "ALTER TABLE `addresses` ADD INDEX `ix_addresses_property_id` (`property_id`);"; }
    if (primaryKeyFor($con, $dbName, 'properties') === 'id' && !fkExists($con, $dbName, 'addresses', 'fk_addresses_property_id')) {
        $sqlOut[] = "ALTER TABLE `addresses` ADD CONSTRAINT `fk_addresses_property_id` FOREIGN KEY (`property_id`) REFERENCES `properties`(`id`) ON DELETE SET NULL ON UPDATE CASCADE;";
    }
}
$sqlOut[] = "";

// sessions
$sqlOut[] = "-- sessions";
$sessionsCreate = <<<SQL
CREATE TABLE `sessions` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `user_id` {$userIdType} NOT NULL,
  `session_token` varchar(128) NOT NULL,
  `user_agent` varchar(255) NULL,
  `ip_address` varchar(45) NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `last_seen_at` timestamp NULL DEFAULT NULL,
  `expires_at` datetime NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_sessions_token` (`session_token`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
SQL;
emitCreateIfMissing($sqlOut, $con, $dbName, 'sessions', $sessionsCreate);
if (columnInfo($con, $dbName, 'sessions', 'user_id')) {
    alignColumnType($sqlOut, $con, $dbName, 'sessions', 'user_id', 'users', 'id');
    if (!indexExists($con, $dbName, 'sessions', 'ix_sessions_user_id')) { $sqlOut[] = "ALTER TABLE `sessions` ADD INDEX `ix_sessions_user_id` (`user_id`);"; }
    if (primaryKeyFor($con, $dbName, 'users') === 'id' && !fkExists($con, $dbName, 'sessions', 'fk_sessions_user_id')) {
        $sqlOut[] = "ALTER TABLE `sessions` ADD CONSTRAINT `fk_sessions_user_id` FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE ON UPDATE CASCADE;";
    }
}
$sqlOut[] = "";

// password_resets
$sqlOut[] = "-- password_resets";
$resetsCreate = <<<SQL
CREATE TABLE `password_resets` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `user_id` {$userIdType} NOT NULL,
  `token` varchar(128) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `expires_at` datetime NOT NULL,
  `used_at` datetime NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_password_resets_token` (`token`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
SQL;
emitCreateIfMissing($sqlOut, $con, $dbName, 'password_resets', $resetsCreate);
if (columnInfo($con, $dbName, 'password_resets', 'user_id')) {
    alignColumnType($sqlOut, $con, $dbName, 'password_resets', 'user_id', 'users', 'id');
    if (!indexExists($con, $dbName, 'password_resets', 'ix_password_resets_user_id')) { $sqlOut[] = "ALTER TABLE `password_resets` ADD INDEX `ix_password_resets_user_id` (`user_id`);"; }
    if (primaryKeyFor($con, $dbName, 'users') === 'id' && !fkExists($con, $dbName, 'password_resets', 'fk_password_resets_user_id')) {
        $sqlOut[] = "ALTER TABLE `password_resets` ADD CONSTRAINT `fk_password_resets_user_id` FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE ON UPDATE CASCADE;";
    }
} else {
    $sqlOut[] = "-- SKIP: `password_resets`.`user_id` missing";
}
$sqlOut[] = "";

// favorites (wishlist)
$sqlOut[] = "-- favorites";
$favoritesCreate = <<<SQL
CREATE TABLE `favorites` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `user_id` {$userIdType} NOT NULL,
  `property_id` {$propertyIdType} NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_favorites_user_property` (`user_id`,`property_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
SQL;
emitCreateIfMissing($sqlOut, $con, $dbName, 'favorites', $favoritesCreate);
if (!indexExists($con, $dbName, 'favorites', 'ix_favorites_user_id')) { $sqlOut[] = "ALTER TABLE `favorites` ADD INDEX `ix_favorites_user_id` (`user_id`);"; }
if (!indexExists($con, $dbName, 'favorites', 'ix_favorites_property_id')) { $sqlOut[] = "ALTER TABLE `favorites` ADD INDEX `ix_favorites_property_id` (`property_id`);"; }
if (primaryKeyFor($con, $dbName, 'users') === 'id' && !fkExists($con, $dbName, 'favorites', 'fk_favorites_user_id')) {
    $sqlOut[] = "ALTER TABLE `favorites` ADD CONSTRAINT `fk_favorites_user_id` FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE ON UPDATE CASCADE;";
}
if (primaryKeyFor($con, $dbName, 'properties') === 'id' && !fkExists($con, $dbName, 'favorites', 'fk_favorites_property_id')) {
    $sqlOut[] = "ALTER TABLE `favorites` ADD CONSTRAINT `fk_favorites_property_id` FOREIGN KEY (`property_id`) REFERENCES `properties`(`id`) ON DELETE CASCADE ON UPDATE CASCADE;";
}
$sqlOut[] = "";

// saved_searches
$sqlOut[] = "-- saved_searches";
$savedCreate = <<<SQL
CREATE TABLE `saved_searches` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `user_id` {$userIdType} NOT NULL,
  `name` varchar(100) NOT NULL,
  `query_json` text NOT NULL,
  `notification_frequency` enum('none','daily','weekly','monthly') NOT NULL DEFAULT 'none',
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
SQL;
emitCreateIfMissing($sqlOut, $con, $dbName, 'saved_searches', $savedCreate);
if (columnInfo($con, $dbName, 'saved_searches', 'user_id')) {
    alignColumnType($sqlOut, $con, $dbName, 'saved_searches', 'user_id', 'users', 'id');
    if (!indexExists($con, $dbName, 'saved_searches', 'ix_saved_searches_user_id')) { $sqlOut[] = "ALTER TABLE `saved_searches` ADD INDEX `ix_saved_searches_user_id` (`user_id`);"; }
    if (primaryKeyFor($con, $dbName, 'users') === 'id' && !fkExists($con, $dbName, 'saved_searches', 'fk_saved_searches_user_id')) {
        $sqlOut[] = "ALTER TABLE `saved_searches` ADD CONSTRAINT `fk_saved_searches_user_id` FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE ON UPDATE CASCADE;";
    }
} else {
    $sqlOut[] = "-- SKIP: `saved_searches`.`user_id` missing";
}
$sqlOut[] = "";

// notifications
$sqlOut[] = "-- notifications";
$notificationsCreate = <<<SQL
CREATE TABLE `notifications` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `user_id` {$userIdType} NULL,
  `type` varchar(100) NOT NULL,
  `title` varchar(200) NOT NULL,
  `body` text NOT NULL,
  `read_at` datetime NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
SQL;
emitCreateIfMissing($sqlOut, $con, $dbName, 'notifications', $notificationsCreate);
if (columnInfo($con, $dbName, 'notifications', 'user_id')) {
    alignColumnType($sqlOut, $con, $dbName, 'notifications', 'user_id', 'users', 'id');
    if (!indexExists($con, $dbName, 'notifications', 'ix_notifications_user_id')) { $sqlOut[] = "ALTER TABLE `notifications` ADD INDEX `ix_notifications_user_id` (`user_id`);"; }
}
if (columnInfo($con, $dbName, 'notifications', 'type') && !indexExists($con, $dbName, 'notifications', 'ix_notifications_type')) { $sqlOut[] = "ALTER TABLE `notifications` ADD INDEX `ix_notifications_type` (`type`);"; }
if (columnInfo($con, $dbName, 'notifications', 'user_id') && primaryKeyFor($con, $dbName, 'users') === 'id' && !fkExists($con, $dbName, 'notifications', 'fk_notifications_user_id')) {
    $sqlOut[] = "ALTER TABLE `notifications` ADD CONSTRAINT `fk_notifications_user_id` FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE SET NULL ON UPDATE CASCADE;";
}
$sqlOut[] = "";

// documents
$sqlOut[] = "-- documents";
$documentsCreate = <<<SQL
CREATE TABLE `documents` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `owner_user_id` {$userIdType} NULL,
  `property_id` {$propertyIdType} NULL,
  `file_path` varchar(255) NOT NULL,
  `mime_type` varchar(100) NULL,
  `status` enum('active','archived') NOT NULL DEFAULT 'active',
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
SQL;
emitCreateIfMissing($sqlOut, $con, $dbName, 'documents', $documentsCreate);
if (columnInfo($con, $dbName, 'documents', 'owner_user_id')) {
    alignColumnType($sqlOut, $con, $dbName, 'documents', 'owner_user_id', 'users', 'id');
    if (!indexExists($con, $dbName, 'documents', 'ix_documents_owner_user')) { $sqlOut[] = "ALTER TABLE `documents` ADD INDEX `ix_documents_owner_user` (`owner_user_id`);"; }
}
if (columnInfo($con, $dbName, 'documents', 'property_id')) {
    alignColumnType($sqlOut, $con, $dbName, 'documents', 'property_id', 'properties', 'id');
    if (!indexExists($con, $dbName, 'documents', 'ix_documents_property_id')) { $sqlOut[] = "ALTER TABLE `documents` ADD INDEX `ix_documents_property_id` (`property_id`);"; }
}
if (columnInfo($con, $dbName, 'documents', 'owner_user_id') && primaryKeyFor($con, $dbName, 'users') === 'id' && !fkExists($con, $dbName, 'documents', 'fk_documents_owner_user')) {
    $sqlOut[] = "ALTER TABLE `documents` ADD CONSTRAINT `fk_documents_owner_user` FOREIGN KEY (`owner_user_id`) REFERENCES `users`(`id`) ON DELETE SET NULL ON UPDATE CASCADE;";
}
if (columnInfo($con, $dbName, 'documents', 'property_id') && primaryKeyFor($con, $dbName, 'properties') === 'id' && !fkExists($con, $dbName, 'documents', 'fk_documents_property_id')) {
    $sqlOut[] = "ALTER TABLE `documents` ADD CONSTRAINT `fk_documents_property_id` FOREIGN KEY (`property_id`) REFERENCES `properties`(`id`) ON DELETE SET NULL ON UPDATE CASCADE;";
}
$sqlOut[] = "";

// property_features and map
$sqlOut[] = "-- property_features & property_feature_map";
$featuresCreate = <<<SQL
CREATE TABLE `property_features` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(120) NOT NULL,
  `slug` varchar(140) NOT NULL,
  `enabled` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_property_features_name` (`name`),
  UNIQUE KEY `uq_property_features_slug` (`slug`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
SQL;
emitCreateIfMissing($sqlOut, $con, $dbName, 'property_features', $featuresCreate);

$mapCreate = <<<SQL
CREATE TABLE `property_feature_map` (
  `property_id` {$propertyIdType} NOT NULL,
  `feature_id` bigint(20) unsigned NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`property_id`,`feature_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
SQL;
emitCreateIfMissing($sqlOut, $con, $dbName, 'property_feature_map', $mapCreate);

if (columnInfo($con, $dbName, 'property_feature_map', 'property_id') && !fkExists($con, $dbName, 'property_feature_map', 'fk_property_feature_map_property')) {
    $sqlOut[] = "ALTER TABLE `property_feature_map` ADD CONSTRAINT `fk_property_feature_map_property` FOREIGN KEY (`property_id`) REFERENCES `properties`(`id`) ON DELETE CASCADE ON UPDATE CASCADE;";
}
if (columnInfo($con, $dbName, 'property_feature_map', 'feature_id') && !fkExists($con, $dbName, 'property_feature_map', 'fk_property_feature_map_feature')) {
    $sqlOut[] = "ALTER TABLE `property_feature_map` ADD CONSTRAINT `fk_property_feature_map_feature` FOREIGN KEY (`feature_id`) REFERENCES `property_features`(`id`) ON DELETE CASCADE ON UPDATE CASCADE;";
}
$sqlOut[] = "";

// Write migration file
$migrationsDir = dirname(__DIR__) . '/migrations';
if (!is_dir($migrationsDir)) { mkdir($migrationsDir, 0777, true); }
$targetFile = $migrationsDir . '/004_schema_enhancements.sql';
file_put_contents($targetFile, implode("\n", $sqlOut));
echo "Schema enhancements migration written to: $targetFile\n";

?>
