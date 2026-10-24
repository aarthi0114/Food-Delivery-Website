<?php

session_start();

require_once "config/db.php";


// Login check
if (!isset($_SESSION["user_id"])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION["user_id"];


// Get form data
$delivery_address = trim($_POST["delivery_address"] ?? "");
$phone = trim($_POST["phone"] ?? "");


// Validation
if ($delivery_address == "" || $phone == "") {

    die("Delivery address and phone number are required.");

}

if (!preg_match("/^[0-9]{10}$/", $phone)) {

    die("Please enter a valid 10-digit phone number.");

}


// Get cart items
$stmt = $conn->prepare(
    "SELECT
        c.cart_id,
        c.food_id,
        c.quantity,
        f.food_name,
        f.price
     FROM cart c
     JOIN food_items f
        ON c.food_id = f.food_id
     WHERE c.user_id = ?"
);

$stmt->bind_param("i", $user_id);

$stmt->execute();

$cart_result = $stmt->get_result();


if ($cart_result->num_rows == 0) {

    die("Your cart is empty.");

}


// Store cart items in array
$cart_items = [];

$total = 0;


while ($item = $cart_result->fetch_assoc()) {

    $cart_items[] = $item;

    $subtotal =
        $item["price"] * $item["quantity"];

    $total += $subtotal;
}


// Start transaction
$conn->begin_transaction();


try {

    // =========================
    // INSERT ORDER
    // =========================

    $order_stmt = $conn->prepare(
        "INSERT INTO orders
        (user_id, total_amount, delivery_address, phone, status)
        VALUES (?, ?, ?, ?, 'Pending')"
    );


    $order_stmt->bind_param(
        "idss",
        $user_id,
        $total,
        $delivery_address,
        $phone
    );


    $order_stmt->execute();


    // Get newly created order ID
    $order_id = $conn->insert_id;


    // =========================
    // INSERT ORDER ITEMS
    // =========================

    $item_stmt = $conn->prepare(
        "INSERT INTO order_items
        (order_id, food_id, quantity, price)
        VALUES (?, ?, ?, ?)"
    );


    foreach ($cart_items as $item) {

        $food_id = $item["food_id"];
        $quantity = $item["quantity"];
        $price = $item["price"];


        $item_stmt->bind_param(
            "iiid",
            $order_id,
            $food_id,
            $quantity,
            $price
        );


        $item_stmt->execute();
    }


    // =========================
    // CLEAR CART
    // =========================

    $delete_cart = $conn->prepare(
        "DELETE FROM cart
         WHERE user_id = ?"
    );


    $delete_cart->bind_param(
        "i",
        $user_id
    );


    $delete_cart->execute();


    // =========================
    // COMMIT
    // =========================

    $conn->commit();


} catch (Exception $e) {

    // Rollback if something fails
    $conn->rollback();

    die(
        "Order placement failed: "
        . $e->getMessage()
    );

}

?>

<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="UTF-8">

    <meta name="viewport"
          content="width=device-width, initial-scale=1.0">

    <title>Order Successful - Foodie</title>

    <link rel="stylesheet"
          href="css/style.css">

</head>

<body>


<!-- NAVBAR -->

<nav class="navbar">

    <div class="logo">
        🍔 Foodie
    </div>


    <ul class="nav-links">

        <li>
            <a href="index.php">
                Home
            </a>
        </li>


        <li>
            <a href="restaurants.php">
                Restaurants
            </a>
        </li>


        <li>
            <a href="orders.php">
                My Orders
            </a>
        </li>


        <li>
            <a href="cart.php">
                🛒 Cart
            </a>
        </li>


        <li>
            <a href="logout.php">
                Logout
            </a>
        </li>

    </ul>


    <div class="nav-buttons">

        Welcome,
        <?php

        echo htmlspecialchars(
            $_SESSION["name"]
        );

        ?>

    </div>

</nav>


<!-- SUCCESS -->

<section class="orders-page">

    <div class="order-card"
         style="text-align:center;">


        <h1>
            🎉 Order Placed Successfully!
        </h1>


        <p>

            Thank you for ordering with Foodie.

        </p>


        <h2>

            Order ID:

            #<?php echo $order_id; ?>

        </h2>


        <h3>

            Total:

            ₹<?php

            echo number_format(
                $total,
                2
            );

            ?>

        </h3>


        <br>


        <a
            href="orders.php"
            class="checkout-btn"
        >
            📦 View My Orders
        </a>


        <a
            href="restaurants.php"
            class="menu-btn"
        >
            🍽️ Order More Food
        </a>


    </div>

</section>


<!-- FOOTER -->

<footer>

    <h3>
        🍔 Foodie
    </h3>


    <p>
        Delicious food delivered to your doorstep.
    </p>


    <p>
        © 2026 Foodie. All Rights Reserved.
    </p>

</footer>


</body>

</html>