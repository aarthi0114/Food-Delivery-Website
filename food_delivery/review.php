<?php

session_start();

require_once "config/db.php";


// Login check
if (!isset($_SESSION["user_id"])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION["user_id"];


// Get food_id
$food_id = isset($_GET["food_id"])
    ? (int)$_GET["food_id"]
    : 0;

if ($food_id <= 0) {
    header("Location: orders.php");
    exit();
}


// Check whether user ordered this food
$stmt = $conn->prepare(
    "SELECT
        f.food_id,
        f.food_name
     FROM order_items oi
     JOIN orders o
        ON oi.order_id = o.order_id
     JOIN food_items f
        ON oi.food_id = f.food_id
     WHERE o.user_id = ?
       AND oi.food_id = ?
       AND o.status = 'Delivered'
     LIMIT 1"
);

$stmt->bind_param(
    "ii",
    $user_id,
    $food_id
);

$stmt->execute();

$result = $stmt->get_result();

$food = $result->fetch_assoc();


// Food not eligible for review
if (!$food) {

    echo "<h2 style='text-align:center; margin-top:50px;'>You can review only delivered food.</h2>";

    echo "<p style='text-align:center;'>
            <a href='orders.php'>← Back to My Orders</a>
          </p>";

    exit();
}


// Submit review
$message = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    $rating = isset($_POST["rating"])
        ? (int)$_POST["rating"]
        : 0;

    $comment = trim($_POST["comment"] ?? "");


    // Validate rating
    if ($rating < 1 || $rating > 5) {

        $message = "Please select a rating between 1 and 5.";

    } else {

        // Check existing review
        $check = $conn->prepare(
            "SELECT review_id
             FROM reviews
             WHERE user_id = ?
               AND food_id = ?"
        );

        $check->bind_param(
            "ii",
            $user_id,
            $food_id
        );

        $check->execute();

        $existing = $check->get_result();


        if ($existing->num_rows > 0) {

            // Update existing review
            $update = $conn->prepare(
                "UPDATE reviews
                 SET rating = ?,
                     comment = ?
                 WHERE user_id = ?
                   AND food_id = ?"
            );

            $update->bind_param(
                "isii",
                $rating,
                $comment,
                $user_id,
                $food_id
            );

            $update->execute();

            $message = "Review updated successfully! ⭐";

        } else {

            // Insert new review
            $insert = $conn->prepare(
                "INSERT INTO reviews
                (user_id, food_id, rating, comment)
                VALUES (?, ?, ?, ?)"
            );

            $insert->bind_param(
                "iiis",
                $user_id,
                $food_id,
                $rating,
                $comment
            );

            $insert->execute();

            $message = "Review submitted successfully! ⭐";
        }
    }
}

?>

<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="UTF-8">

    <meta name="viewport"
          content="width=device-width, initial-scale=1.0">

    <title>Write Review - Foodie</title>

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


<!-- REVIEW -->

<section class="orders-page">

    <div class="order-card">

        <h1>
            ⭐ Write a Review
        </h1>


        <h2>

            <?php

            echo htmlspecialchars(
                $food["food_name"]
            );

            ?>

        </h2>


        <?php if ($message != "") { ?>

            <p style="margin:15px 0; font-weight:bold;">
                <?php echo htmlspecialchars($message); ?>
            </p>

        <?php } ?>


        <form method="POST">


            <label>
                Rating
            </label>


            <br><br>


            <select
                name="rating"
                required
            >

                <option value="">
                    Select Rating
                </option>

                <option value="5">
                    ⭐⭐⭐⭐⭐ 5
                </option>

                <option value="4">
                    ⭐⭐⭐⭐ 4
                </option>

                <option value="3">
                    ⭐⭐⭐ 3
                </option>

                <option value="2">
                    ⭐⭐ 2
                </option>

                <option value="1">
                    ⭐ 1
                </option>

            </select>


            <br><br>


            <label>
                Comment
            </label>


            <br><br>


            <textarea
                name="comment"
                rows="5"
                placeholder="Write your feedback..."
                style="width:100%; max-width:600px;"
            ></textarea>


            <br><br>


            <button
                type="submit"
                class="checkout-btn"
            >
                Submit Review ⭐
            </button>


        </form>

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