<?php
session_start();

// Connect to the database
$servername = 'localhost';
$dbname   = 'voting';
$username = 'sathsa';
$password = 'voting@123';

// Create a new MySQLi connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check if the connection was successful
if ($conn->connect_error) {
    // Display an error message and exit
    die("Connection failed: " . $conn->connect_error);
}

// Get the user input
$username = $_POST['username'];
$password = $_POST['password'];

// Validate the user input
if (!filter_var($username, FILTER_VALIDATE_REGEXP, array("options" => array("regexp" => "/^[a-zA-Z0-9_\.]+$/")))) {
    // Username is invalid, redirect to the login page
    echo '<script>
    alert("Invalid credentials");
    window.location="adminlogin.html";
    </script>';
    exit;
}

// Prepare the SQL query
$stmt = $conn->prepare("SELECT * FROM `admin` WHERE username=? AND password=?");
$stmt->bind_param("ss", $username, $password);

// Execute the query
$stmt->execute();

// Bind the result variables
$stmt->bind_result($id, $status, $data);

// Check if the user credentials are valid
if ($stmt->fetch()) {
    // Credentials are valid, set session variables and redirect to the dashboard page
    $_SESSION['id'] = $id;
    $_SESSION['status'] = $status;
    $_SESSION['data'] = $data;
    header("Location: admindashboard.html");
    exit;
} else {
    // Credentials are invalid, display an error message and redirect to the login page
    echo '<script>
    alert("Invalid credentials");
    window.location="adminlogin.html";
    </script>';
    exit;
}

// Close the statement and the connection
$stmt->close();
$conn->close();
