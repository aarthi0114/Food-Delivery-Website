<?php

require_once "config/db.php";

$restaurant_id = isset($_GET['restaurant_id'])
    ? (int)$_GET['restaurant_id']
    : 0;


// Get restaurant details
$stmt = $conn->prepare(
    "SELECT *
     FROM restaurants
     WHERE restaurant_id = ?"
);

$stmt->bind_param("i", $restaurant_id);
$stmt->execute();

$restaurant_result = $stmt->get_result();

$restaurant = $restaurant_result->fetch_assoc();


// If restaurant not found
if (!$restaurant) {

    echo "<h2 style='text-align:center; margin-top:50px;'>
            Restaurant not found!
          </h2>";

    echo "<p style='text-align:center;'>
            <a href='restaurants.php'>
                ← Back to Restaurants
            </a>
          </p>";

    exit();

}


// Get food items
$stmt = $conn->prepare(
    "SELECT
        f.*,
        c.category_name
     FROM food_items f
     LEFT JOIN categories c
        ON f.category_id = c.category_id
     WHERE f.restaurant_id = ?
       AND f.availability = 1
     ORDER BY f.food_id DESC"
);

$stmt->bind_param("i", $restaurant_id);

$stmt->execute();

$foods = $stmt->get_result();

?>

<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="UTF-8">

    <meta name="viewport"
          content="width=device-width, initial-scale=1.0">

    <title>

        <?php
        echo htmlspecialchars(
            $restaurant["restaurant_name"]
        );
        ?>

        - Menu

    </title>

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


        <?php if (isset($_SESSION["user_id"])) { ?>

            <span>

                Welcome,

                <?php

                echo htmlspecialchars(
                    $_SESSION["name"]
                );

                ?>

            </span>


        <?php } else { ?>


            <a
                href="login.php"
                class="login-btn"
            >
                Login
            </a>


            <a
                href="register.php"
                class="register-btn"
            >
                Register
            </a>


        <?php } ?>


    </div>


</nav>



<!-- ================= MENU PAGE ================= -->

<section class="menu-page">


    <h1>

        🍽️

        <?php

        echo htmlspecialchars(
            $restaurant["restaurant_name"]
        );

        ?>

    </h1>


    <p class="restaurant-subtitle">

        Delicious food available at this restaurant

    </p>



    <div class="menu-grid">


        <?php

        if ($foods->num_rows > 0) {


            while ($food = $foods->fetch_assoc()) {


                // ==========================
                // GET REVIEWS
                // ==========================

                $review_stmt = $conn->prepare(
                    "SELECT
                        r.rating,
                        r.comment,
                        r.created_at,
                        u.name
                     FROM reviews r
                     JOIN users u
                        ON r.user_id = u.user_id
                     WHERE r.food_id = ?
                     ORDER BY r.created_at DESC"
                );


                $review_stmt->bind_param(
                    "i",
                    $food["food_id"]
                );


                $review_stmt->execute();


                $review_result =
                    $review_stmt->get_result();


                // ==========================
                // GET AVERAGE RATING
                // ==========================

                $rating_stmt = $conn->prepare(
                    "SELECT
                        AVG(rating) AS average_rating,
                        COUNT(*) AS review_count
                     FROM reviews
                     WHERE food_id = ?"
                );


                $rating_stmt->bind_param(
                    "i",
                    $food["food_id"]
                );


                $rating_stmt->execute();


                $rating_result =
                    $rating_stmt->get_result();


                $rating_data =
                    $rating_result->fetch_assoc();


                $average_rating =
                    $rating_data["average_rating"];


                $review_count =
                    $rating_data["review_count"];


        ?>


        <!-- ==========================
             FOOD CARD
        =========================== -->

        <div class="food-card">


            <!-- FOOD IMAGE -->

    <div class="food-image">
    <img
        src="images/<?php echo htmlspecialchars($food["image"] ?? "default.jpg"); ?>"
        alt="<?php echo htmlspecialchars($food["food_name"]); ?>"
    >
</div>



            <!-- FOOD DETAILS -->

            <div class="food-info">


                <h3>

                    <?php

                    echo htmlspecialchars(
                        $food["food_name"]
                    );

                    ?>

                </h3>



                <!-- CATEGORY -->

                <p class="food-category">

                    <?php

                    echo htmlspecialchars(
                        $food["category_name"]
                        ?? "Food"
                    );

                    ?>

                </p>



                <!-- DESCRIPTION -->

                <p>

                    <?php

                    echo htmlspecialchars(
                        $food["description"]
                    );

                    ?>

                </p>



                <!-- ==================
                     RATING
                =================== -->

                <div
                    style="
                        margin:10px 0;
                        font-size:15px;
                    "
                >

                    <?php if ($review_count > 0) { ?>


                        ⭐

                        <strong>

                            <?php

                            echo number_format(
                                $average_rating,
                                1
                            );

                            ?>

                            / 5

                        </strong>


                        <span>

                            (
                            <?php
                            echo $review_count;
                            ?>
                            reviews)

                        </span>


                    <?php } else { ?>


                        <span>
                            ⭐ No reviews yet
                        </span>


                    <?php } ?>

                </div>



                <!-- PRICE + CART -->

                <div class="food-bottom">


                    <span class="price">

                        ₹<?php

                        echo number_format(
                            $food["price"],
                            2
                        );

                        ?>

                    </span>


                    <a
                        href="add_to_cart.php?food_id=<?php echo $food["food_id"]; ?>"
                        class="add-cart-btn"
                    >

                        🛒 Add to Cart

                    </a>


                </div>



                <!-- ==================
                     CUSTOMER REVIEWS
                =================== -->

                <?php if ($review_count > 0) { ?>


                    <div
                        style="
                            margin-top:15px;
                            padding-top:10px;
                            border-top:1px solid #ddd;
                        "
                    >


                        <h4>
                            💬 Customer Reviews
                        </h4>


                        <?php

                        $shown_reviews = 0;


                        while (
                            $review =
                            $review_result->fetch_assoc()
                        ) {


                            if ($shown_reviews >= 3) {
                                break;
                            }


                        ?>


                            <div
                                style="
                                    margin-top:10px;
                                    padding:10px;
                                    background:#f8f8f8;
                                    border-radius:8px;
                                "
                            >


                                <strong>

                                    <?php

                                    echo htmlspecialchars(
                                        $review["name"]
                                    );

                                    ?>

                                </strong>


                                <div>

                                    <?php

                                    echo str_repeat(
                                        "⭐",
                                        (int)$review["rating"]
                                    );

                                    ?>

                                </div>


                                <?php if (
                                    !empty(
                                        $review["comment"]
                                    )
                                ) { ?>


                                    <p>

                                        <?php

                                        echo htmlspecialchars(
                                            $review["comment"]
                                        );

                                        ?>

                                    </p>


                                <?php } ?>


                                <small>

                                    <?php

                                    echo date(
                                        "d-m-Y",
                                        strtotime(
                                            $review["created_at"]
                                        )
                                    );

                                    ?>

                                </small>


                            </div>


                        <?php

                            $shown_reviews++;

                        }


                        ?>


                    </div>


                <?php } ?>


            </div>


        </div>


        <?php

            }

        } else {


            echo "

                <p>
                    No food items available.
                </p>

            ";

        }

        ?>


    </div>


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