<?php

session_start();

require_once "config/db.php";


// Login check
if (!isset($_SESSION["user_id"])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION["user_id"];


// Get cart items
$stmt = $conn->prepare(
    "SELECT
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


// Calculate total
$total = 0;

while ($item = $cart_result->fetch_assoc()) {

    $total += $item["price"] * $item["quantity"];

}


// Check empty cart
if ($total <= 0) {

    header("Location: cart.php");
    exit();

}

?>

<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="UTF-8">

    <meta name="viewport"
          content="width=device-width, initial-scale=1.0">

    <title>Checkout - Foodie</title>

    <link rel="stylesheet" href="css/style.css">

</head>

<body>


<!-- NAVBAR -->

<nav class="navbar">

    <div class="logo">🍔 Foodie</div>

    <ul class="nav-links">

        <li>
            <a href="index.php">Home</a>
        </li>

        <li>
            <a href="restaurants.php">Restaurants</a>
        </li>

        <li>
            <a href="cart.php">🛒 Cart</a>
        </li>

    </ul>

    <div class="nav-buttons">

        <span>
            Welcome,
            <?php echo htmlspecialchars($_SESSION["name"]); ?>
        </span>

    </div>

</nav>


<!-- CHECKOUT -->

<section class="checkout-page">

    <div class="checkout-box">

        <h1>📦 Checkout</h1>

        <p>
            Enter your delivery details
        </p>


        <form action="place_order.php" method="POST">


            <!-- ADDRESS -->

            <label>
                Delivery Address
            </label>

            <textarea
                name="delivery_address"
                placeholder="Enter your complete delivery address"
                required
            ></textarea>


            <!-- PHONE -->

            <label>
                Phone Number
            </label>

            <input
                type="tel"
                name="phone"
                placeholder="Enter your phone number"
                pattern="[0-9]{10}"
                maxlength="10"
                required
            >


            <!-- TOTAL -->

            <div class="checkout-total">

                <span>
                    Order Total
                </span>

                <strong>
                    ₹<?php echo number_format($total, 2); ?>
                </strong>

            </div>


            <button
                type="submit"
                class="place-order-btn"
            >
                🛍️ Place Order
            </button>

        </form>

    </div>

</section>


<footer>

    <h3>🍔 Foodie</h3>

    <p>
        Delicious food delivered to your doorstep.
    </p>

    <p>
        © 2026 Foodie. All Rights Reserved.
    </p>

</footer>


</body>

</html>