<?php
include 'config.php';

$data = json_decode(file_get_contents('php://input'), true);
$is_voting_active = $data['is_voting_active'];

$query = "UPDATE voting_status SET is_voting_active = ? WHERE id = 1";
$stmt = $conn->prepare($query);
$stmt->bind_param('i', $is_voting_active);
$success = $stmt->execute();

echo json_encode(['success' => $success]);
