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
        c.cart_id,
        c.quantity,
        f.food_name,
        f.price,
        r.restaurant_name
     FROM cart c
     JOIN food_items f
        ON c.food_id = f.food_id
     JOIN restaurants r
        ON f.restaurant_id = r.restaurant_id
     WHERE c.user_id = ?"
);

$stmt->bind_param("i", $user_id);
$stmt->execute();

$result = $stmt->get_result();

$total = 0;

?>

<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="UTF-8">

    <meta name="viewport"
          content="width=device-width, initial-scale=1.0">

    <title>My Cart - Foodie</title>

    <link rel="stylesheet" href="css/style.css">

</head>

<body>

<!-- NAVBAR -->

<nav class="navbar">

    <div class="logo">
        🍔 Foodie
    </div>

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

        <li>
            <a href="orders.php">My Orders</a>
        </li>

        <li>
            <a href="logout.php">Logout</a>
        </li>

    </ul>

    <div class="nav-buttons">

        <span>
            Welcome,
            <?php echo htmlspecialchars($_SESSION["name"]); ?>
        </span>

    </div>

</nav>


<!-- CART -->

<section class="cart-page">

    <h1>🛒 My Cart</h1>


    <?php if ($result->num_rows > 0) { ?>

        <div class="cart-container">


            <?php while ($item = $result->fetch_assoc()) {

                $subtotal =
                    $item["price"] * $item["quantity"];

                $total += $subtotal;

            ?>

                <div class="cart-item">


                    <!-- FOOD DETAILS -->

                    <div class="cart-item-info">

                        <h3>

                            <?php

                            echo htmlspecialchars(
                                $item["food_name"]
                            );

                            ?>

                        </h3>


                        <p>

                            🍽️

                            <?php

                            echo htmlspecialchars(
                                $item["restaurant_name"]
                            );

                            ?>

                        </p>


                        <p>

                            Price:

                            ₹<?php

                            echo number_format(
                                $item["price"],
                                2
                            );

                            ?>

                        </p>

                    </div>


                    <!-- QUANTITY -->

                    <div class="cart-quantity">

                        <p>
                            <strong>Quantity:</strong>
                        </p>


                        <form
                            action="update_cart.php"
                            method="POST"
                            style="display:flex; align-items:center; gap:10px;"
                        >

                            <input
                                type="hidden"
                                name="cart_id"
                                value="<?php echo $item["cart_id"]; ?>"
                            >


                            <button
                                type="submit"
                                name="quantity"
                                value="<?php echo $item["quantity"] - 1; ?>"
                            >
                                −
                            </button>


                            <strong>
                                <?php echo $item["quantity"]; ?>
                            </strong>


                            <button
                                type="submit"
                                name="quantity"
                                value="<?php echo $item["quantity"] + 1; ?>"
                            >
                                +
                            </button>

                        </form>


                        <!-- REMOVE -->

                        <form
                            action="update_cart.php"
                            method="POST"
                            style="margin-top:10px;"
                        >

                            <input
                                type="hidden"
                                name="cart_id"
                                value="<?php echo $item["cart_id"]; ?>"
                            >


                            <input
                                type="hidden"
                                name="quantity"
                                value="0"
                            >


                            <button type="submit">
                                🗑 Remove
                            </button>

                        </form>

                    </div>


                    <!-- SUBTOTAL -->

                    <div class="cart-subtotal">

                        <strong>Subtotal:</strong>

                        <br>

                        ₹<?php

                        echo number_format(
                            $subtotal,
                            2
                        );

                        ?>

                    </div>


                </div>

            <?php } ?>


            <!-- TOTAL -->

            <div class="cart-total">

                <h2>

                    Total:

                    ₹<?php

                    echo number_format(
                        $total,
                        2
                    );

                    ?>

                </h2>


                <a
                    href="checkout.php"
                    class="checkout-btn"
                >
                    Proceed to Checkout →
                </a>

            </div>


        </div>


    <?php } else { ?>


        <!-- EMPTY CART -->

        <div class="empty-cart">

            <h2>
                🛒 Your cart is empty
            </h2>


            <p>
                Add some delicious food to your cart!
            </p>


            <a
                href="restaurants.php"
                class="menu-btn"
            >
                Browse Restaurants
            </a>

        </div>


    <?php } ?>


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