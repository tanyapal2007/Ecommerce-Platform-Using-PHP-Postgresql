<?php

include  "database.php";

// ==========================================
// BACKUP FOLDER
// ==========================================

$backupDir = __DIR__ . "/backups";

if (!is_dir($backupDir)) {
    mkdir($backupDir, 0755, true);
}


// ==========================================
// DATABASE NAME
// ==========================================

// Agar database.php me $dbname available hai
// to uska use karein.

if (!isset($dbname) || empty($dbname)) {
    die("Database name not found.");
}


// ==========================================
// BACKUP FILE NAME
// ==========================================

$date = date("Y-m-d_H-i-s");

$backupFile = $backupDir . "/backup_" . $date . ".sql";


// ==========================================
// OPEN FILE
// ==========================================

$handle = fopen($backupFile, "w");

if (!$handle) {
    die("Backup file create nahi ho saki.");
}


// ==========================================
// HEADER
// ==========================================

fwrite($handle, "-- ========================================\n");
fwrite($handle, "-- MySQL Database Backup\n");
fwrite($handle, "-- Database: " . $dbname . "\n");
fwrite($handle, "-- Date: " . date("Y-m-d H:i:s") . "\n");
fwrite($handle, "-- ========================================\n\n");


// ==========================================
// GET ALL TABLES
// ==========================================

$tablesResult = mysqli_query($conn, "SHOW TABLES");

if (!$tablesResult) {
    fclose($handle);
    die("Tables fetch nahi ho paye.");
}


while ($tableRow = mysqli_fetch_row($tablesResult)) {

    $table = $tableRow[0];

    // ------------------------------------------
    // DROP TABLE
    // ------------------------------------------

    fwrite($handle, "DROP TABLE IF EXISTS `" . $table . "`;\n");


    // ------------------------------------------
    // CREATE TABLE STRUCTURE
    // ------------------------------------------

    $createResult = mysqli_query(
        $conn,
        "SHOW CREATE TABLE `" . $table . "`"
    );

    if ($createResult) {

        $createRow = mysqli_fetch_assoc($createResult);

        $createSql = $createRow['Create Table'];

        fwrite($handle, $createSql . ";\n\n");
    }


    // ------------------------------------------
    // TABLE DATA
    // ------------------------------------------

    $dataResult = mysqli_query(
        $conn,
        "SELECT * FROM `" . $table . "`"
    );

    if ($dataResult && mysqli_num_rows($dataResult) > 0) {

        $columnCount = mysqli_num_fields($dataResult);

        while ($row = mysqli_fetch_row($dataResult)) {

            $values = [];

            for ($i = 0; $i < $columnCount; $i++) {

                if ($row[$i] === null) {

                    $values[] = "NULL";
                } else {

                    $values[] = "'" .
                        mysqli_real_escape_string($conn, $row[$i]) .
                        "'";
                }
            }

            $insertSql =
                "INSERT INTO `" . $table . "` VALUES (" .
                implode(", ", $values) .
                ");\n";

            fwrite($handle, $insertSql);
        }

        fwrite($handle, "\n");
    }
}


// ==========================================
// CLOSE FILE
// ==========================================

fclose($handle);


// ==========================================
// SUCCESS
// ==========================================

echo "Database backup successfully created.<br>";
echo "File: " . basename($backupFile);
