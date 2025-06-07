<?php
require_once '../config/db_connect.php';

try {
    // Create database if not exists
    $pdo->exec("CREATE DATABASE IF NOT EXISTS shs_grade_view");
    $pdo->exec("USE shs_grade_view");

    // Check if schema.sql exists
    $schemaFile = '../database/schema.sql';
    if (!file_exists($schemaFile)) {
        throw new Exception("Schema file not found: $schemaFile");
    }

    // Read and execute schema.sql
    $sql = file_get_contents($schemaFile);
    $pdo->exec($sql);

    echo "Database initialized successfully!\n";
} catch (PDOException $e) {
    die("Database initialization failed: " . $e->getMessage());
} catch (Exception $e) {
    die($e->getMessage());
}
?>
