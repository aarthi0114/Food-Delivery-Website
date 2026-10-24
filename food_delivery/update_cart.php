<?php

session_start();
require_once "config/db.php";

if (!isset($_SESSION["user_id"])) {
    header("Location: login.php");
    exit();
}

$cart_id = isset($_POST["cart_id"]) ? (int)$_POST["cart_id"] : 0;
$quantity = isset($_POST["quantity"]) ? (int)$_POST["quantity"] : 1;

if ($cart_id <= 0) {
    header("Location: cart.php");
    exit();
}

if ($quantity <= 0) {

    $stmt = $conn->prepare(
        "DELETE FROM cart WHERE cart_id = ? AND user_id = ?"
    );

    $stmt->bind_param(
        "ii",
        $cart_id,
        $_SESSION["user_id"]
    );

    $stmt->execute();

} else {

    $stmt = $conn->prepare(
        "UPDATE cart SET quantity = ? 
         WHERE cart_id = ? AND user_id = ?"
    );

    $stmt->bind_param(
        "iii",
        $quantity,
        $cart_id,
        $_SESSION["user_id"]
    );

    $stmt->execute();
}

header("Location: cart.php");
exit();

?>