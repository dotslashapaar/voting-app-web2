<?php
session_start();

// Ensure the user is authenticated
if (!isset($_SESSION['id'])) {
    http_response_code(401); // Unauthorized
    echo 'Unauthorized';
    exit;
}

// Database connection details
$servername = 'localhost';
$dbname   = 'voting';
$username = 'sathsa';
$password = 'voting@123';

// Create a new MySQLi connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check if the connection was successful
if ($conn->connect_error) {
    http_response_code(500); // Internal Server Error
    echo 'Database connection failed: ' . $conn->connect_error;
    exit;
}

// Perform the database extraction (example: export all tables)
$tables = array();
$result = $conn->query("SHOW TABLES");

if ($result) {
    while ($row = $result->fetch_row()) {
        $tables[] = $row[0];
    }

    // Assuming we want to extract table structures and data
    $sqlScript = "";
    foreach ($tables as $table) {
        // Get the table structure
        $result = $conn->query("SHOW CREATE TABLE $table");
        $row = $result->fetch_row();
        $sqlScript .= "\n\n" . $row[1] . ";\n\n";

        // Get the table data
        $result = $conn->query("SELECT * FROM $table");
        $columnCount = $result->field_count;

        for ($i = 0; $i < $columnCount; $i++) {
            while ($row = $result->fetch_row()) {
                $sqlScript .= "INSERT INTO $table VALUES(";
                for ($j = 0; $j < $columnCount; $j++) {
                    $row[$j] = $row[$j] ? addslashes($row[$j]) : 'NULL';
                    $row[$j] = str_replace("\n", "\\n", $row[$j]);
                    if (isset($row[$j])) {
                        $sqlScript .= '"' . $row[$j] . '"';
                    } else {
                        $sqlScript .= '""';
                    }
                    if ($j < ($columnCount - 1)) {
                        $sqlScript .= ', ';
                    }
                }
                $sqlScript .= ");\n";
            }
        }
        $sqlScript .= "\n";
    }

    // Save the SQL script to a file
    $backup_file_name = 'db-backup-' . time() . '.sql';
    $fileHandler = fopen($backup_file_name, 'w+');
    fwrite($fileHandler, $sqlScript);
    fclose($fileHandler);

    echo 'Database tables extracted successfully';
} else {
    http_response_code(500); // Internal Server Error
    echo 'Error extracting tables: ' . $conn->error;
}

$conn->close();
