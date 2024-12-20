<?php

session_start();
//check if the user logged in
if (!isset($_SESSION['id']) || $_SESSION['id'] == '') {
    // User is not logged in, redirect to the login page
    header("Location: loginpage.html");
    exit;
}
$data = $_SESSION['data'];
if ($_SESSION['status'] == 1) {
    $status = '<span class="text-success">Voted</span>';
} else {
    $status = '<span class="text-danger">Not Voted</span>';
}

// Connect to the database
$servername = 'localhost';
$dbname   = 'voting';
$username = 'sathsa';
$password = 'voting@123';

include 'config.php';

// Fetch the voting status
$query = "SELECT is_voting_active FROM voting_status WHERE id = 1";
$result = $conn->query($query);
$row = $result->fetch_assoc();
$is_voting_active = $row['is_voting_active'];

// Determine the voting status message
if ($is_voting_active === null) {
    $voting_status_message = "Voting period has not started";
} elseif ($is_voting_active) {
    $voting_status_message = "Voting period has started";
} else {
    $voting_status_message = "Voting period has ended";
}


// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);
if (!$conn) {
    die("Connection failed: " . mysqli_connect_error());
}

// Retrieve all candidates from the database
$stmt = $conn->prepare("SELECT candidate_id, full_name FROM candidates");
$stmt->execute();
$result = $stmt->get_result();

$candidates = [];
while ($row = $result->fetch_assoc()) {
    $candidates[] = $row;
}
$stmt->close();
$conn->close();

?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Voter Dashboard</title>
    <link rel="stylesheet" href="voters_dashboard.css">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@10"></script>
</head>

<body>

    <div class="container">
        <div class="header">
            <div class="heading-box">
                <h1 class="heading">Voter Dashboard</h1>
            </div>
        </div>

        <div class="voter-details">
            <h2>Voter Details</h2>

            <table class="voter-table">
                <tr>
                    <th>Name</th>
                    <td id="voterName"><?php echo $data['username']; ?></td>

                </tr>
                <tr>
                    <th>Phone</th>
                    <td id="voterPhone"> <?php echo $data['phone_number']; ?></td>
                </tr>
                <tr>
                    <th>Date of Birth</th>
                    <td id="voterDOB"><?php echo $data['dob']; ?></td>
                </tr>
                <tr>
                    <th>Status</th>
                    <td id="voterStatus"><?php echo $status; ?></td>
                </tr>
                <tr>
                    <th>Voting Period</th>
                    <td id="is_voting_active"><?php echo $voting_status_message; ?></td>
                </tr>
            </table>
        </div>

        <div class="candidate-details">
            <h2>Candidates</h2>
            <form action="voting.php" method="post">
                <table class="candidate-table">
                    <tr>
                        <th></th>
                        <th>Candidate Name</th>
                    </tr>
                    <?php foreach ($candidates as $candidate) : ?>
                        <tr>
                            <td><input type="radio" name="candidate" value="<?php echo $candidate['candidate_id']; ?>"></td>
                            <td><?php echo $candidate['full_name']; ?></td>
                        </tr>
                    <?php endforeach; ?>
                </table>
            </form>
            <div class="actions">
                <a href="count_results.php"><button class="green-button" id="view-results-button">View Results</button></a>
                <a href="loginpage.html"><button class="green-button">Back</button></a>
                <a href="index.html"><button class="green-button">Logout</button></a>
            </div>
        </div>

    </div>

    <script src="voters_dashboard.js"></script>

    <script>
        // Store the dashboard type in local storage
        localStorage.setItem('dashboardType', 'voter');
    </script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const votingStatus = "<?php echo $voting_status_message; ?>";
            const viewResultsButton = document.getElementById('view-results-button');

            if (votingStatus !== "Voting period has ended") {
                viewResultsButton.disabled = true;
                viewResultsButton.style.opacity = 0.5;
            }
        });
    </script>


</body>

</html>