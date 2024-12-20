<?php
// Connect to the database
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

// Get data from html form
$username = $_POST['username'];
$password = $_POST['password'];
$confirm_password = $_POST['confirmPassword'];
$dob = $_POST['dob'];
$phone_number = $_POST['phone_num'];

// Validate passwords
if ($password !== $confirm_password) {
    echo '<script>
    alert("Passwords do not match.");
    window.location="signuppage.html";
    </script>';
    exit();
}

// Generate a new unique user ID
// You can use various methods to generate a unique ID, such as using a database sequence, UUID, or simply incrementing a counter
// For simplicity, let's assume we're incrementing a counter for the user ID
// First, fetch the highest existing user ID from the database
$sql_max_id = "SELECT MAX(id) AS max_id FROM register";
$result_max_id = $conn->query($sql_max_id);
$row_max_id = $result_max_id->fetch_assoc();
$new_user_id = $row_max_id['max_id'] + 1; // Increment the highest user ID by 1 to get a new unique ID

// Insert data into database with the new user ID
$stmt = $conn->prepare("INSERT INTO register (id, username, password, dob, phone_number) VALUES (?, ?, ?, ?, ?)");
$stmt->bind_param("issss", $new_user_id, $username, $password, $dob, $phone_number);

if ($stmt->execute()) {
    echo '<script>
    alert("Registration successful. You can now login.");
    window.location="loginpage.html";
    </script>';
} else {
    echo "Error: " . $stmt->error;
}

$stmt->close();
$conn->close();
