<?php
// send_poke.php

session_start();

if (!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true) {
    header("location: login.php");
    exit;
}

require_once 'db.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $sender_id = $_SESSION["id"];
    $receiver_id = $_POST["receiver_id"];

    $sql = "INSERT INTO pokes (sender_id, receiver_id) VALUES (?, ?)";
    if ($stmt = $conn->prepare($sql)) {
        $stmt->bind_param("ii", $sender_id, $receiver_id);

        if ($stmt->execute()) {
            header("location: dashboard.php");
        } else {
            echo "Something went wrong. Please try again later.";
        }
        $stmt->close();
    }
}

$conn->close();
?>