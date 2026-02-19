<?php

/**
 * Database Consolidation Tool
 * Merges legacy 'user' table into 'users'
 * Merges legacy 'agents' table into 'associates'
 * Updates Foreign Keys to point to new tables
 * Drops legacy tables
 */

// Configuration
$host = 'localhost';
$dbname = 'apsdreamhome';
$username = 'root';
$password = '';

// Connect to DB
try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    echo "Connected to database.\n";
} catch (PDOException $e) {
    die("Database connection failed: " . $e->getMessage() . "\n");
}

function tableExists($pdo, $table)
{
    $stmt = $pdo->prepare("SHOW TABLES LIKE ?");
    $stmt->execute([$table]);
    return $stmt->rowCount() > 0;
}

function columnExists($pdo, $table, $column)
{
    $stmt = $pdo->prepare("SHOW COLUMNS FROM `$table` LIKE ?");
    $stmt->execute([$column]);
    return $stmt->rowCount() > 0;
}

function getForeignKeyConstraints($pdo, $table)
{
    $sql = "SELECT 
                CONSTRAINT_NAME, 
                COLUMN_NAME, 
                REFERENCED_TABLE_NAME, 
                REFERENCED_COLUMN_NAME 
            FROM information_schema.KEY_COLUMN_USAGE 
            WHERE TABLE_SCHEMA = DATABASE() 
            AND TABLE_NAME = ? 
            AND REFERENCED_TABLE_NAME IS NOT NULL";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$table]);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

// 1. Migrate 'user' to 'users'
echo "\n--- 1. User Consolidation (user -> users) ---\n";
if (tableExists($pdo, 'user') && tableExists($pdo, 'users')) {
    $legacyUsers = $pdo->query("SELECT * FROM user")->fetchAll(PDO::FETCH_ASSOC);
    echo "Found " . count($legacyUsers) . " legacy users.\n";

    foreach ($legacyUsers as $lUser) {
        $email = $lUser['uemail'];
        $phone = $lUser['uphone'];

        // Check if exists in users
        $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ? OR phone = ?");
        $stmt->execute([$email, $phone]);
        $exists = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$exists) {
            echo "Migrating user: $email\n";
            $role = ($lUser['utype'] == 1) ? 'admin' : (($lUser['role'] ?? 'user') == 'associate' ? 'associate' : 'user');

            $sql = "INSERT INTO users (name, email, phone, password, role, address, city, state, pincode, status, created_at) 
                    VALUES (:name, :email, :phone, :password, :role, :address, :city, :state, :pincode, :status, :created_at)";

            try {
                $stmt = $pdo->prepare($sql);
                $stmt->execute([
                    ':name' => $lUser['uname'],
                    ':email' => $lUser['uemail'],
                    ':phone' => $lUser['uphone'],
                    ':password' => $lUser['upass'],
                    ':role' => $role,
                    ':address' => $lUser['address'],
                    ':city' => $lUser['city'],
                    ':state' => $lUser['state'],
                    ':pincode' => $lUser['pincode'],
                    ':status' => $lUser['status'] ?? 'active',
                    ':created_at' => $lUser['join_date'] ?? date('Y-m-d H:i:s')
                ]);
            } catch (Exception $e) {
                echo "Failed to migrate $email: " . $e->getMessage() . "\n";
            }
        } else {
            // Update mapping for FKs later if needed?
            // For now, we assume email/phone uniqueness is enough to identify
        }
    }
} else {
    echo "Skipping user migration (tables missing).\n";
}

// 2. Migrate 'agents' to 'users' + 'associates'
echo "\n--- 2. Agent Consolidation (agents -> users + associates) ---\n";
if (tableExists($pdo, 'agents') && tableExists($pdo, 'users') && tableExists($pdo, 'associates')) {
    $agents = $pdo->query("SELECT * FROM agents")->fetchAll(PDO::FETCH_ASSOC);
    echo "Found " . count($agents) . " legacy agents.\n";

    foreach ($agents as $agent) {
        $email = $agent['email'];
        $phone = $agent['mobile'];

        // Check if exists in users
        $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ? OR phone = ?");
        $stmt->execute([$email, $phone]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        $userId = null;

        if (!$user) {
            echo "Creating user for agent: $email\n";
            $sql = "INSERT INTO users (name, email, phone, password, role, city, state, status, created_at) 
                    VALUES (:name, :email, :phone, :password, 'associate', :city, :state, :status, :created_at)";
            try {
                $stmt = $pdo->prepare($sql);
                $stmt->execute([
                    ':name' => $agent['full_name'],
                    ':email' => $agent['email'],
                    ':phone' => $agent['mobile'],
                    ':password' => $agent['password'],
                    ':city' => $agent['city'],
                    ':state' => $agent['state'],
                    ':status' => $agent['status'],
                    ':created_at' => $agent['registration_date']
                ]);
                $userId = $pdo->lastInsertId();
            } catch (Exception $e) {
                echo "Failed to create user for agent $email: " . $e->getMessage() . "\n";
                continue;
            }
        } else {
            $userId = $user['id'];
        }

        // Check if exists in associates
        $stmt = $pdo->prepare("SELECT id FROM associates WHERE user_id = ?");
        $stmt->execute([$userId]);
        $associate = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$associate && $userId) {
            echo "Creating associate profile for user ID: $userId\n";
            $sql = "INSERT INTO associates (user_id, associate_code, company_name, status, created_at) 
                    VALUES (:user_id, :associate_code, :company_name, :status, :created_at)";
            try {
                $stmt = $pdo->prepare($sql);
                $stmt->execute([
                    ':user_id' => $userId,
                    ':associate_code' => $agent['agent_code'],
                    ':company_name' => $agent['company_name'],
                    ':status' => $agent['status'],
                    ':created_at' => $agent['registration_date']
                ]);
            } catch (Exception $e) {
                echo "Failed to create associate profile for $email: " . $e->getMessage() . "\n";
            }
        }
    }
} else {
    echo "Skipping agent migration (tables missing).\n";
}

// 3. Update Foreign Keys
echo "\n--- 3. Updating Foreign Keys ---\n";

// List of tables that might reference 'user' or 'agents'
$potentialTables = $pdo->query("SHOW TABLES")->fetchAll(PDO::FETCH_COLUMN);

foreach ($potentialTables as $table) {
    if ($table == 'user' || $table == 'agents') continue;

    $fks = getForeignKeyConstraints($pdo, $table);
    foreach ($fks as $fk) {
        if ($fk['REFERENCED_TABLE_NAME'] == 'user') {
            echo "Found FK in table '$table' column '{$fk['COLUMN_NAME']}' referencing 'user'.\n";

            // Drop FK
            try {
                $pdo->exec("ALTER TABLE `$table` DROP FOREIGN KEY `{$fk['CONSTRAINT_NAME']}`");
                echo "Dropped FK `{$fk['CONSTRAINT_NAME']}`.\n";
            } catch (Exception $e) {
                echo "Failed to drop FK: " . $e->getMessage() . "\n";
                continue;
            }

            // Modify Column Type to match users.id (bigint unsigned)
            try {
                $pdo->exec("ALTER TABLE `$table` MODIFY COLUMN `{$fk['COLUMN_NAME']}` BIGINT(20) UNSIGNED NULL");
                echo "Modified column `{$fk['COLUMN_NAME']}` to BIGINT(20) UNSIGNED.\n";
            } catch (Exception $e) {
                echo "Failed to modify column: " . $e->getMessage() . "\n";
            }

            // Add new FK to users
            try {
                // First ensure all values exist in users
                // For this, we need to map old UIDs to new IDs.
                // Since we migrated by email/phone, we can try to update.
                // But if the IDs changed, we have a problem.
                // Assuming IDs might be different if 'users' existed before.
                // If 'users' was empty, maybe IDs matched? Unlikely.

                // CRITICAL: We need to update the values in $table.{$fk['COLUMN_NAME']} 
                // from old user.uid to new users.id

                // Fetch mapping
                echo "Updating values in $table...\n";
                $mapping = $pdo->query("SELECT u.uid as old_id, new_u.id as new_id 
                                        FROM user u 
                                        JOIN users new_u ON (u.uemail = new_u.email OR u.uphone = new_u.phone)")->fetchAll(PDO::FETCH_ASSOC);

                $pdo->beginTransaction();
                foreach ($mapping as $map) {
                    $updateSql = "UPDATE `$table` SET `{$fk['COLUMN_NAME']}` = :new_id WHERE `{$fk['COLUMN_NAME']}` = :old_id";
                    $stmt = $pdo->prepare($updateSql);
                    $stmt->execute([':new_id' => $map['new_id'], ':old_id' => $map['old_id']]);
                }
                $pdo->commit();

                // Add FK
                $newFkName = "fk_{$table}_{$fk['COLUMN_NAME']}_users";
                $pdo->exec("ALTER TABLE `$table` ADD CONSTRAINT `$newFkName` FOREIGN KEY (`{$fk['COLUMN_NAME']}`) REFERENCES `users`(`id`) ON DELETE SET NULL");
                echo "Added new FK to 'users'.\n";
            } catch (Exception $e) {
                if ($pdo->inTransaction()) $pdo->rollBack();
                echo "Failed to update/add FK: " . $e->getMessage() . "\n";
            }
        }
    }
}

// 4. Drop Legacy Tables
echo "\n--- 4. Dropping Legacy Tables ---\n";

if (tableExists($pdo, 'user')) {
    // Check if any FKs still reference it
    $stillReferenced = false;
    // (We should have removed them above)

    if (!$stillReferenced) {
        try {
            $pdo->exec("DROP TABLE user");
            echo "Dropped 'user' table.\n";
        } catch (Exception $e) {
            echo "Failed to drop 'user': " . $e->getMessage() . "\n";
        }
    }
}

if (tableExists($pdo, 'agents')) {
    try {
        $pdo->exec("DROP TABLE agents");
        echo "Dropped 'agents' table.\n";
    } catch (Exception $e) {
        echo "Failed to drop 'agents': " . $e->getMessage() . "\n";
    }
}

echo "\nConsolidation Complete.\n";
