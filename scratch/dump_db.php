<?php

$dbFile = 'batu_printing_system';
$outFile = 'batu_printing_system.sql';

if (!file_exists($dbFile)) {
    die("Error: File $dbFile not found.\n");
}

try {
    $db = new PDO("sqlite:$dbFile");
    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    $output = "-- Database Dump (MySQL Compatible)\n";
    $output .= "SET NAMES utf8mb4;\n";
    $output .= "SET FOREIGN_KEY_CHECKS=0;\n";
    $output .= "SET SQL_MODE = 'NO_AUTO_VALUE_ON_ZERO';\n";
    $output .= "START TRANSACTION;\n\n";

    // Get all tables
    $tables = $db->query("SELECT name FROM sqlite_master WHERE type='table' AND name NOT LIKE 'sqlite_%'")->fetchAll(PDO::FETCH_COLUMN);

    foreach ($tables as $table) {
        // Get create statement and convert to MySQL compatible
        $createStmt = $db->query("SELECT sql FROM sqlite_master WHERE type='table' AND name='$table'")->fetchColumn();
        
        // Basic conversions to MySQL compatible syntax
        $createStmt = str_replace('"', '`', $createStmt);
        
        // Fix primary key autoincrement
        $createStmt = preg_replace('/integer primary key autoincrement/i', 'INT AUTO_INCREMENT PRIMARY KEY', $createStmt);
        $createStmt = preg_replace('/primary key autoincrement/i', 'AUTO_INCREMENT PRIMARY KEY', $createStmt);
        
        // Fix varchar without length
        $createStmt = preg_replace('/varchar(?!\s*\()/i', 'VARCHAR(255)', $createStmt);
        
        // Fix other common type differences
        $createStmt = preg_replace('/datetime/i', 'DATETIME', $createStmt);
        $createStmt = preg_replace('/boolean/i', 'TINYINT(1)', $createStmt);
        
        $output .= "DROP TABLE IF EXISTS `$table`;\n";
        $output .= "$createStmt ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;\n\n";

        // Get data
        $rows = $db->query("SELECT * FROM `$table`")->fetchAll(PDO::FETCH_ASSOC);
        foreach ($rows as $row) {
            $keys = array_keys($row);
            $values = array_map(function($val) use ($db) {
                if ($val === null) return 'NULL';
                return $db->quote($val);
            }, array_values($row));
            
            $output .= "INSERT INTO `$table` (`" . implode('`, `', $keys) . "`) VALUES (" . implode(', ', $values) . ");\n";
        }
        $output .= "\n";
    }

    $output .= "COMMIT;\n";
    $output .= "SET FOREIGN_KEY_CHECKS=1;\n";
    file_put_contents($outFile, $output);
    echo "Successfully dumped $dbFile to $outFile\n";

} catch (PDOException $e) {
    die("PDO Error: " . $e->getMessage() . "\n");
}
