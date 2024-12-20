<?php
include 'config.php';

$query = "SELECT is_voting_active FROM voting_status WHERE id = 1";
$result = $conn->query($query);
$row = $result->fetch_assoc();

echo json_encode(['is_voting_active' => $row['is_voting_active']]);
