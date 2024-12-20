<?php
$servername = 'localhost';
$dbname   = 'voting';
$username = 'sathsa';
$password = 'voting@123';

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$candidateId = $_POST['candidateId'];
$newCandidateName = $_POST['newCandidateName'];
$newCandidateDob = $_POST['newCandidateDob'];
$newCandidateMob = $_POST['newCandidateMob'];

$stmt = $conn->prepare("UPDATE candidates SET full_name = ?, date_of_birth = ?, mobile_number = ? WHERE candidate_id = ?");
$stmt->bind_param("sssi", $newCandidateName, $newCandidateDob, $newCandidateMob, $candidateId);

if ($stmt->execute()) {
    echo "Candidate details updated successfully!";
} else {
    echo "Error: " . $stmt->error;
}

$stmt->close();
$conn->close();
