<?php

session_start();

if (!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true) {
    header("location: login.php");
    exit;
}

require_once 'db.php';

$user_id = $_SESSION["id"];
$limit = 7;

$sql = "SELECT p.id, s.name, s.surname, p.poke_date
        FROM pokes p
        JOIN users s ON p.sender_id = s.id
        WHERE p.receiver_id = ?
        ORDER BY p.poke_date DESC
        LIMIT ?";

if ($stmt = $conn->prepare($sql)) {
    $stmt->bind_param("ii", $user_id, $limit);
    $stmt->execute();
    $result = $stmt->get_result();

    $notifications = [];
    while ($row = $result->fetch_assoc()) {
        $notifications[] = $row;
    }
    $stmt->close();
}

$response = [
    'notifications' => $notifications
];

echo json_encode($response);

$conn->close();
