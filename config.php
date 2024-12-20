
<?php
$servername = 'localhost';
$dbname = 'voting';
$username = 'sathsa';
$password = 'voting@123';

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);
// config.php
define('SECRET_KEY', 'votingkey');
// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
?>

