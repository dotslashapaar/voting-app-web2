<?php
// Replace with your actual database name
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

// Get data from the AJAX request
$candidateFullName = $_POST['candidateFullName'];
$candidateDob = $_POST['candidateDob'];
$candidateMob = $_POST['candidateMob'];

// Insert the candidate into the database
$sql = "INSERT INTO candidates (full_name, date_of_birth, mobile_number) VALUES (?, ?, ?)";
$stmt = $conn->prepare($sql);
$stmt->bind_param("sss", $candidateFullName, $candidateDob, $candidateMob);

if ($stmt->execute()) {
    echo "Candidate added successfully!";
} else {
    echo "Error: " . $stmt->error;
}

$stmt->close();
$conn->close();
