<?php
require_once(__DIR__ . "/framework/framework.php");

header("Content-Type: text/plain");

if (!$db) {
    die("Database connection failed. Check your config in framework.php");
}

echo "Starting database repair...\n";

try {

    $st = $db->query("SHOW COLUMNS FROM users LIKE 'discord_id'");
    $column = $st->fetch();

    if ($column) {
        echo "Found 'discord_id' column. Removing unique constraint...\n";
        

        $st = $db->query("SHOW INDEX FROM users WHERE Column_name = 'discord_id'");
        $indices = $st->fetchAll();
        
        foreach ($indices as $index) {
            if ($index['Non_unique'] == 0 && $index['Key_name'] !== 'PRIMARY') {
                $index_name = $index['Key_name'];
                echo "Dropping unique index: $index_name\n";
                try {
                    $db->exec("ALTER TABLE users DROP INDEX `$index_name`") ;
                } catch (Exception $e) {
                    echo "Error dropping index: " . $e->getMessage() . "\n";
                }
            }
        }
        

        echo "Updating column definition...\n";
        $db->exec("ALTER TABLE users MODIFY discord_id VARCHAR(64) NOT NULL DEFAULT ''");
    } else {
        echo "'discord_id' column not found. Adding it now...\n";
        $db->exec("ALTER TABLE users ADD COLUMN discord_id VARCHAR(64) NOT NULL DEFAULT '' AFTER roblox_id");
    }


    $required_cols = [
        "referral_code" => "VARCHAR(12) NOT NULL DEFAULT ''",
        "referred_by"   => "VARCHAR(12) NOT NULL DEFAULT ''",
        "referral_uses" => "INT UNSIGNED NOT NULL DEFAULT 0"
    ];

    foreach ($required_cols as $col => $def) {
        $st = $db->query("SHOW COLUMNS FROM users LIKE '$col'");
        if (!$st->fetch()) {
            echo "Adding missing column: $col\n";
            $db->exec("ALTER TABLE users ADD COLUMN $col $def");
        }
    }

    echo "\nRepair complete! You can now delete this file and try creating an account again.\n";

} catch (Exception $e) {
    die("\nFATAL ERROR DURING REPAIR: " . $e->getMessage());
}
?>
