<?php
session_start();
require_once 'config.php';
$servername = 'localhost';
$dbname = 'voting';
$username = 'sathsa';
$password = 'voting@123';

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Define a secret key for encryption
$secret_key = SECRET_KEY;

// Get the user's ID from the session
$user_id = filter_var($_SESSION['id'], FILTER_SANITIZE_NUMBER_INT);

// Get the candidate the user voted for
$candidate = filter_var($_POST['candidate'], FILTER_SANITIZE_STRING);

// Encrypt the vote
function encryptText($text, $key)
{
    $iv = openssl_random_pseudo_bytes(openssl_cipher_iv_length('aes-256-cbc'));
    $encrypted = openssl_encrypt($text, 'aes-256-cbc', $key, 0, $iv);
    return base64_encode($iv . $encrypted);
}

// Initialize vote variables
$candidate_a = encryptText('0', $secret_key);
$candidate_b = encryptText('0', $secret_key);
$candidate_c = encryptText('0', $secret_key);
$candidate_d = encryptText('0', $secret_key);

if ($candidate == '1') {
    $candidate_a = encryptText('1', $secret_key);
} elseif ($candidate == '2') {
    $candidate_b = encryptText('1', $secret_key);
} elseif ($candidate == '3') {
    $candidate_c = encryptText('1', $secret_key);
} elseif ($candidate == '4') {
    $candidate_d = encryptText('1', $secret_key);
}

// Check if the user has already voted
$stmt = $conn->prepare("SELECT COUNT(*) AS vote_count FROM voter_results WHERE username =?");
$stmt->bind_param("s", $_SESSION['username']); // Assuming the username is stored in the session
$stmt->execute();
$result = $stmt->get_result();
$row = $result->fetch_assoc();
$vote_count = $row['vote_count'];
$stmt->close();

if ($vote_count > 0) {
    echo '<script>
        alert("You have already voted.");
        window.location="index.html";
    </script>';
    exit;
}
$query = "SELECT is_voting_active FROM voting_status WHERE id = 1";
$result = $conn->query($query);
$row = $result->fetch_assoc();
$is_voting_active = $row['is_voting_active'];

if ($is_voting_active === null) {
    echo '<script>
        alert("Voting has not started.");
        window.location="voterdashboard.php";
    </script>';
    exit;
} elseif (!$is_voting_active) {
    echo '<script>
        alert("The Election has already ended! You will be redirected to the Results Page.");
        window.location="count_results.php";
    </script>';
    exit;
}



// Prepare the statement to update the user's status
$stmt = $conn->prepare("UPDATE register SET status = 1 WHERE id =?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$stmt->close();

// Prepare the statement to insert a new row into the voter_results table
$stmt = $conn->prepare("INSERT INTO voter_results (username, candidate_a, candidate_b, candidate_c, candidate_d)
                        SELECT username,?,?,?,?
                        FROM register WHERE id =?");
$stmt->bind_param("ssssi", $candidate_a, $candidate_b, $candidate_c, $candidate_d, $user_id);
$stmt->execute();
$stmt->close();

// Close the database connection
$conn->close();

// Destroy the session
session_destroy();

// Redirect the user back to the dashboard
echo '<script>
    alert("Successfully voted!! You will be redirected to the home page!!");
    window.location="index.html";
</script>';

exit;
