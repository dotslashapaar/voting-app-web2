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
if (!filter_var($username, FILTER_VALIDATE_REGEXP, array("options" => array("regexp" => "/^[a-zA-Z0-9_\. ]+$/")))) {
    // Username is invalid, redirect to the login page
    echo '<script>
    alert("Invalid credentials");
    window.location="loginpage.html";
    </script>';
    exit;
}

// Prepare the SQL query
$stmt = $conn->prepare("SELECT * FROM  register WHERE username=? AND password=?");
$stmt->bind_param("ss", $username, $password);

// Execute the query
$stmt->execute();

// Get the result
$result = $stmt->get_result();

// Check if the user credentials are valid
if (mysqli_num_rows($result) == 1) {
    // Credentials are valid, setsession variables and redirect to the dashboard page
    $data = mysqli_fetch_assoc($result);
    $_SESSION['id'] = $data['id'];
    $_SESSION['status'] = $data['status'];
    $_SESSION['data'] = $data;

    // Check if the username exists in the candidates table
    $stmt = $conn->prepare("SELECT * FROM candidates WHERE full_name = ?");
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $candidate_result = $stmt->get_result();

    if (mysqli_num_rows($candidate_result) == 1) {
        // Redirect to the candidate dashboard page
        header("Location: candidate_dashboard.php");
        exit;
    } else {
        // Redirect to the voter dashboard page
        header("Location: voterdashboard.php");
        exit;
    }
} else {
    // Credentials are invalid, display an error message and redirect to the login page
    echo '<script>
    alert("Invalid credentials");
    window.location="loginpage.html";
    </script>';
    exit;
}
