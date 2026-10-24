<?php

session_start();

require_once "../config/db.php";

// Admin check
if (!isset($_SESSION["user_id"]) || $_SESSION["role"] !== "admin") {
    header("Location: ../login.php");
    exit();
}

// Get customer ID
$user_id = isset($_GET["id"]) ? (int)$_GET["id"] : 0;

if ($user_id <= 0) {
    header("Location: users.php");
    exit();
}

// Delete only customer
$stmt = $conn->prepare(
    "DELETE FROM users
     WHERE user_id = ?
     AND role = 'customer'"
);

$stmt->bind_param("i", $user_id);
$stmt->execute();

// Back to customers page
header("Location: users.php");
exit();

?>