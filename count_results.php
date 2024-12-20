<?php

session_start();
$servername = 'localhost';
$dbname = 'voting';
$username = 'sathsa';
$password = 'voting@123';

require_once 'config.php';
// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Define the secret key for decryption
$secret_key = SECRET_KEY;

// Retrieve the encrypted votes from the database
$stmt = $conn->prepare("SELECT candidate_a, candidate_b, IFNULL(candidate_c, '') as candidate_c, IFNULL(candidate_d, '') as candidate_d FROM voter_results");
$stmt->execute();
$result = $stmt->get_result();

$candidate_a_votes = 0;
$candidate_b_votes = 0;
$candidate_c_votes = 0;
$candidate_d_votes = 0;

while ($row = $result->fetch_assoc()) {
    $candidate_a_encrypted = $row['candidate_a'];
    $candidate_b_encrypted = $row['candidate_b'];
    $candidate_c_encrypted = $row['candidate_c'];
    $candidate_d_encrypted = $row['candidate_d'];

    $candidate_a_decrypted = decryptVote($candidate_a_encrypted, $secret_key);
    $candidate_b_decrypted = decryptVote($candidate_b_encrypted, $secret_key);
    $candidate_c_decrypted = decryptVote($candidate_c_encrypted, $secret_key);
    $candidate_d_decrypted = $candidate_d_encrypted ? decryptVote($candidate_d_encrypted, $secret_key) : 0;

    $candidate_a_votes += $candidate_a_decrypted;
    $candidate_b_votes += $candidate_b_decrypted;
    $candidate_c_votes += $candidate_c_decrypted;
    $candidate_d_votes += $candidate_d_decrypted;
}

function decryptVote($encryptedText, $secret_key)
{
    $data = base64_decode($encryptedText);
    $ivSize = openssl_cipher_iv_length('aes-256-cbc');
    $iv = substr($data, 0, $ivSize);
    $encrypted = substr($data, $ivSize);
    return openssl_decrypt($encrypted, 'aes-256-cbc', $secret_key, 0, $iv);
}

// Retrieve all candidates from the database
$stmt = $conn->prepare("SELECT candidate_id, full_name FROM candidates");
$stmt->execute();
$result = $stmt->get_result();

$candidates = [];
while ($row = $result->fetch_assoc()) {
    $candidates[$row['candidate_id']] = $row['full_name'];
}

// Count the number of candidates
$candidate_count = count($candidates);

$stmt = $conn->prepare("SELECT * FROM `voter_results`");
$stmt->execute();
$result = $stmt->get_result();

$voter_results = [];

while ($row = $result->fetch_assoc()) {
    $voter_results[] = $row;
}

// Close the database connection
$conn->close();
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Voting System Result</title>
    <link rel="stylesheet" href="Result.css">
    <style>
        .encrypted-table {
            width: 100%;
            table-layout: fixed;
        }

        .encrypted-table th,
        .encrypted-table td {
            overflow-wrap: break-word;
        }
    </style>
</head>

<body>

    <div class="header">
        <h1>Voting System Result</h1>
    </div>

    <div class="container">
        <table class="result-table">
            <caption>Results</caption>
            <tr>
                <th>Name</th>
                <th>Votes</th>
            </tr>
            <tr>
                <td><?php echo $candidates[1]; ?></td>
                <td><?php echo $candidate_a_votes; ?></td>
            </tr>
            <tr>
                <td><?php echo $candidates[2]; ?></td>
                <td><?php echo $candidate_b_votes; ?></td>
            </tr>
            <?php if ($candidate_count > 2) : ?>
                <tr>
                    <td><?php echo $candidates[3]; ?></td>
                    <td><?php echo $candidate_c_votes; ?></td>
                </tr>
            <?php endif; ?>
            <?php if ($candidate_count > 3) : ?>
                <tr>
                    <td><?php echo $candidates[4]; ?></td>
                    <td><?php echo $candidate_d_votes; ?></td>
                </tr>
            <?php endif; ?>
        </table>

        <div class="winner">
            <?php
            // Determine the winner among the candidates
            $vote_counts = [
                1 => $candidate_a_votes,
                2 => $candidate_b_votes,
                3 => $candidate_c_votes,
                4 => $candidate_d_votes,
            ];

            $max_votes = max($vote_counts);
            $winners = array_keys($vote_counts, $max_votes);

            if (count($winners) > 1) {
                // It's a tie
                $winner_names = array_map(function ($winner) use ($candidates) {
                    return $candidates[$winner];
                }, $winners);
                $winner = "It's a tie between: " . implode(", ", $winner_names);
            } else {
                // Single winner
                $winner = $candidates[$winners[0]];
            }
            ?>
            <h2>Winner: <?php echo $winner; ?></h2>
        </div>

        <br>
        <table class="result-table encrypted-table">
            <caption>Encrypted Results Table</caption>
            <tr>
                <th>id</th>
                <th>username</th>
                <th>candidate_a</th>
                <th>candidate_b</th>
                <?php if ($candidate_count > 2) : ?>
                    <th>candidate_c</th>
                <?php endif; ?>
                <?php if ($candidate_count == 4) : ?>
                    <th>candidate_d</th>
                <?php endif; ?>
            </tr>
            <?php foreach ($voter_results as $result) : ?>
                <tr>
                    <td><?php echo $result['id']; ?></td>
                    <td><?php echo $result['username']; ?></td>
                    <td><?php echo $result['candidate_a']; ?></td>
                    <td><?php echo $result['candidate_b']; ?></td>
                    <?php if ($candidate_count > 2) : ?>
                        <td><?php echo $result['candidate_c']; ?></td>
                    <?php endif; ?>
                    <?php if ($candidate_count == 4) : ?>
                        <td><?php echo $result['candidate_d']; ?></td>
                    <?php endif; ?>
                </tr>
            <?php endforeach; ?>
        </table>
    </div>

    <div class="footer">
        <p><a href="voterdashboard.php">Go Back</a></p>
    </div>

</body>

</html>