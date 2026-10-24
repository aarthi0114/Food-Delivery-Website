<?php

session_start();

require_once "config/db.php";


// User login check
if (!isset($_SESSION["user_id"])) {

    header("Location: login.php");
    exit();

}


// Get food ID
$food_id = isset($_GET["food_id"])
    ? (int)$_GET["food_id"]
    : 0;

$user_id = $_SESSION["user_id"];


// Check food exists
$stmt = $conn->prepare(
    "SELECT food_id, restaurant_id
     FROM food_items
     WHERE food_id = ?"
);

$stmt->bind_param("i", $food_id);
$stmt->execute();

$result = $stmt->get_result();

$food = $result->fetch_assoc();


if (!$food) {

    die("Food item not found!");

}


// Check whether item already exists in cart
$stmt = $conn->prepare(
    "SELECT cart_id, quantity
     FROM cart
     WHERE user_id = ?
     AND food_id = ?"
);

$stmt->bind_param("ii", $user_id, $food_id);
$stmt->execute();

$cart_result = $stmt->get_result();

$cart_item = $cart_result->fetch_assoc();


if ($cart_item) {

    // Increase quantity
    $new_quantity = $cart_item["quantity"] + 1;

    $stmt = $conn->prepare(
        "UPDATE cart
         SET quantity = ?
         WHERE cart_id = ?"
    );

    $stmt->bind_param(
        "ii",
        $new_quantity,
        $cart_item["cart_id"]
    );

    $stmt->execute();

} else {

    // Add new item
    $quantity = 1;

    $stmt = $conn->prepare(
        "INSERT INTO cart
         (user_id, food_id, quantity)
         VALUES (?, ?, ?)"
    );

    $stmt->bind_param(
        "iii",
        $user_id,
        $food_id,
        $quantity
    );

    $stmt->execute();

}


// Go to cart
header("Location: cart.php");
exit();

?>