<?php

session_start();

require_once "config/db.php";


// Login check
if (!isset($_SESSION["user_id"])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION["user_id"];


// Get user's orders
$stmt = $conn->prepare(
    "SELECT *
     FROM orders
     WHERE user_id = ?
     ORDER BY order_date DESC"
);

$stmt->bind_param("i", $user_id);
$stmt->execute();

$result = $stmt->get_result();

?>

<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="UTF-8">

    <meta name="viewport"
          content="width=device-width, initial-scale=1.0">

    <title>My Orders - Foodie</title>

    <link rel="stylesheet"
          href="css/style.css">

</head>

<body>


<!-- ================= NAVBAR ================= -->

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
            <a href="orders.php">My Orders</a>
        </li>

        <li>
            <a href="cart.php">🛒 Cart</a>
        </li>

        <li>
            <a href="logout.php">Logout</a>
        </li>

    </ul>


    <div class="nav-buttons">

        Welcome,
        <?php echo htmlspecialchars($_SESSION["name"]); ?>

    </div>

</nav>


<!-- ================= ORDERS ================= -->

<section class="orders-page">

    <h1>
        📦 My Orders
    </h1>


    <p class="orders-subtitle">
        View your order history and current order status.
    </p>


    <?php if ($result->num_rows > 0) { ?>


        <div class="orders-container">


            <?php while ($order = $result->fetch_assoc()) { ?>


                <div class="order-card">


                    <!-- ORDER HEADER -->

                    <div class="order-header">

                        <h3>
                            Order #<?php echo $order["order_id"]; ?>
                        </h3>


                        <span class="order-status">

                            <?php
                            echo htmlspecialchars(
                                $order["status"]
                            );
                            ?>

                        </span>

                    </div>


                    <!-- ORDER DETAILS -->

                    <div class="order-details">

                        <p>
                            💰
                            <strong>Total:</strong>

                            ₹<?php
                            echo number_format(
                                $order["total_amount"],
                                2
                            );
                            ?>
                        </p>


                        <p>
                            📍
                            <strong>Address:</strong>

                            <?php
                            echo htmlspecialchars(
                                $order["delivery_address"]
                            );
                            ?>
                        </p>


                        <p>
                            📞
                            <strong>Phone:</strong>

                            <?php
                            echo htmlspecialchars(
                                $order["phone"]
                            );
                            ?>
                        </p>


                        <p>
                            📅
                            <strong>Order Date:</strong>

                            <?php
                            echo date(
                                "d-m-Y h:i A",
                                strtotime(
                                    $order["order_date"]
                                )
                            );
                            ?>
                        </p>

                    </div>


                    <!-- ================= ORDER ITEMS ================= -->

                    <div class="order-items">

                        <h4>
                            🍽️ Ordered Items
                        </h4>


                        <?php

                        $item_stmt = $conn->prepare(
                            "SELECT
                                oi.quantity,
                                oi.price,
                                f.food_id,
                                f.food_name
                             FROM order_items oi
                             JOIN food_items f
                                ON oi.food_id = f.food_id
                             WHERE oi.order_id = ?"
                        );

                        $item_stmt->bind_param(
                            "i",
                            $order["order_id"]
                        );

                        $item_stmt->execute();

                        $item_result =
                            $item_stmt->get_result();

                        ?>


                        <?php if ($item_result->num_rows > 0) { ?>


                            <?php while ($item = $item_result->fetch_assoc()) { ?>


                                <div class="order-item"
                                     style="margin-bottom:20px; padding:10px 0;">


                                    <!-- FOOD NAME -->

                                    <div>

                                        🍴

                                        <strong>

                                            <?php
                                            echo htmlspecialchars(
                                                $item["food_name"]
                                            );
                                            ?>

                                        </strong>

                                    </div>


                                    <!-- QUANTITY -->

                                    <div>

                                        Qty:

                                        <?php
                                        echo $item["quantity"];
                                        ?>

                                    </div>


                                    <!-- PRICE -->

                                    <div>

                                        Price:

                                        ₹<?php
                                        echo number_format(
                                            $item["price"],
                                            2
                                        );
                                        ?>

                                    </div>


                                    <!-- SUBTOTAL -->

                                    <div>

                                        Subtotal:

                                        ₹<?php
                                        echo number_format(
                                            $item["price"] *
                                            $item["quantity"],
                                            2
                                        );
                                        ?>

                                    </div>


                                    <!-- REVIEW BUTTON -->

                                    <?php if ($order["status"] == "Delivered") { ?>

                                        <div style="margin-top:15px;">

                                            <a
                                                href="review.php?food_id=<?php echo $item["food_id"]; ?>"
                                                class="checkout-btn"
                                            >
                                                ⭐ Review
                                            </a>

                                        </div>

                                    <?php } ?>


                                </div>


                            <?php } ?>


                        <?php } else { ?>


                            <p>
                                No item details available.
                            </p>


                        <?php } ?>


                    </div>


                </div>


            <?php } ?>


        </div>


    <?php } else { ?>


        <!-- EMPTY ORDERS -->

        <div class="empty-orders">

            <h2>
                📦 No Orders Yet
            </h2>


            <p>
                You haven't placed any orders yet.
            </p>


            <a
                href="restaurants.php"
                class="menu-btn"
            >
                🍽️ Browse Restaurants
            </a>

        </div>


    <?php } ?>


</section>


<!-- ================= FOOTER ================= -->

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