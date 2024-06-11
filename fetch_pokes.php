<?php
session_start();

if (!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true) {
    header("location: login.php");
    exit;
}

require_once 'db.php';

$page = isset($_GET['page']) ? (int) $_GET['page'] : 1;
$search_name = isset($_GET['search_name']) ? $_GET['search_name'] : '';
$date_from = isset($_GET['date_from']) ? $_GET['date_from'] : '';
$date_to = isset($_GET['date_to']) ? $_GET['date_to'] : '';
$limit = 10;
$offset = ($page - 1) * $limit;

$sql = "SELECT p.id, s.nickname AS sender_nickname, s.name AS sender_name, s.surname AS sender_surname,
               r.nickname AS receiver_nickname, r.name AS receiver_name, r.surname AS receiver_surname,
               DATE_FORMAT(p.poke_date, '%Y-%m-%d') AS poke_date
        FROM pokes p
        JOIN users s ON p.sender_id = s.id
        JOIN users r ON p.receiver_id = r.id
        WHERE 1 = 1";

if (!empty($search_name)) {
    $sql .= " AND (s.nickname LIKE ? OR s.name LIKE ? OR s.surname LIKE ? OR
                   r.nickname LIKE ? OR r.name LIKE ? OR r.surname LIKE ?)";
}

if (!empty($date_from)) {
    $sql .= " AND p.poke_date >= ?";
}

if (!empty($date_to)) {
    $sql .= " AND p.poke_date <= ?";
}

$sql .= " ORDER BY p.poke_date DESC LIMIT ? OFFSET ?";

if ($stmt = $conn->prepare($sql)) {
    if (!empty($search_name) && !empty($date_from) && !empty($date_to)) {
        $stmt->bind_param("ssssssssii", $searchParam, $searchParam, $searchParam, $searchParam, $searchParam, $searchParam, $date_from, $date_to, $limit, $offset);
    } elseif (!empty($search_name) && !empty($date_from)) {
        $stmt->bind_param("ssssssii", $searchParam, $searchParam, $searchParam, $searchParam, $searchParam, $searchParam, $date_from, $limit, $offset);
    } elseif (!empty($search_name) && !empty($date_to)) {
        $stmt->bind_param("ssssssii", $searchParam, $searchParam, $searchParam, $searchParam, $searchParam, $searchParam, $date_to, $limit, $offset);
    } elseif (!empty($date_from) && !empty($date_to)) {
        $stmt->bind_param("ssii", $date_from, $date_to, $limit, $offset);
    } elseif (!empty($search_name)) {
        $stmt->bind_param("ssssssii", $searchParam, $searchParam, $searchParam, $searchParam, $searchParam, $searchParam, $limit, $offset);
    } elseif (!empty($date_from)) {
        $stmt->bind_param("ssii", $date_from, $limit, $offset);
    } elseif (!empty($date_to)) {
        $stmt->bind_param("ssii", $date_to, $limit, $offset);
    } else {
        $stmt->bind_param("ii", $limit, $offset);
    }

    $searchParam = '%' . $search_name . '%';

    $stmt->execute();
    $result = $stmt->get_result();

    $pokes = [];
    while ($row = $result->fetch_assoc()) {
        $pokes[] = $row;
    }

    $stmt->close();
}

$total_pages_sql = "SELECT COUNT(*) FROM pokes";
$total_pages_result = $conn->query($total_pages_sql);
$total_rows = $total_pages_result->fetch_row()[0];
$total_pages = ceil($total_rows / $limit);


$response = [
    'pokes' => $pokes,
    'total_pages' => $total_pages
];

echo json_encode($response);

$conn->close();
