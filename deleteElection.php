<?php
// deleteElection.php

// Database connection parameters
$servername = 'localhost';
$dbname   = 'voting';
$username = 'sathsa';
$password = 'voting@123';

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Fetch all table names in the database
$result = $conn->query("SHOW TABLES");
if ($result) {
    while ($row = $result->fetch_array()) {
        $table = $row[0];
        // Skip truncating the voting_status and admin tables
        if ($table == 'voting_status' || $table == 'admin') {
            continue;
        }
        // Truncate each table except voting_status and admin
        $truncateSql = "TRUNCATE TABLE $table";
        if ($conn->query($truncateSql) !== TRUE) {
            echo "Error truncating table $table: " . $conn->error . "<br>";
        }
    }
}

// Set the value of is_voting_active in voting_status table back to NULL
$updateSql = "UPDATE voting_status SET is_voting_active = NULL WHERE id = 1";
if ($conn->query($updateSql) !== TRUE) {
    echo "Error updating voting_status: " . $conn->error . "<br>";
}

echo "All tables except voting_status and admin truncated successfully, and voting_status reset.";

$conn->close();
