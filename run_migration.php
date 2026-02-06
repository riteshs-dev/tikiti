<?php

/**
 * Run database migration to create api_tokens table
 * Usage: php run_migration.php
 */

require_once __DIR__ . '/bootstrap.php';
\Config::load();

use App\Database\ConnectionPool;

try {
    echo "Running migration: Creating api_tokens table...\n\n";
    
    $pool = ConnectionPool::getInstance();
    
    // Read the migration file
    $migrationFile = __DIR__ . '/database/migrations/002_create_api_tokens_table.sql';
    if (!file_exists($migrationFile)) {
        throw new Exception("Migration file not found: $migrationFile");
    }
    
    $sql = file_get_contents($migrationFile);
    
    // Execute the migration
    $pool->execute(function($db) use ($sql) {
        // Split by semicolon and execute each statement
        $statements = array_filter(
            array_map('trim', explode(';', $sql)),
            function($stmt) {
                return !empty($stmt) && !preg_match('/^\s*--/', $stmt);
            }
        );
        
        foreach ($statements as $statement) {
            if (!empty(trim($statement))) {
                try {
                    $db->exec($statement);
                    echo "✓ Executed statement\n";
                } catch (PDOException $e) {
                    // Ignore "already exists" errors
                    if (strpos($e->getMessage(), 'already exists') === false && 
                        strpos($e->getMessage(), 'duplicate') === false) {
                        throw $e;
                    }
                    echo "ℹ Statement already executed (skipped)\n";
                }
            }
        }
    });
    
    // Verify the table was created
    $pool->execute(function($db) {
        $stmt = $db->query("
            SELECT column_name, data_type 
            FROM information_schema.columns 
            WHERE table_name = 'api_tokens'
            ORDER BY ordinal_position
        ");
        $columns = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        if (empty($columns)) {
            throw new Exception("Table api_tokens was not created!");
        }
        
        echo "\n✓ Migration completed successfully!\n";
        echo "\nTable 'api_tokens' created with columns:\n";
        foreach ($columns as $col) {
            echo "  - {$col['column_name']} ({$col['data_type']})\n";
        }
    });
    
} catch (Exception $e) {
    echo "\n✗ Migration failed!\n";
    echo "Error: " . $e->getMessage() . "\n";
    exit(1);
}
