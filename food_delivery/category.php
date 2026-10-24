<?php

session_start();

require_once "config/db.php";

$category = isset($_GET["category"])
    ? trim($_GET["category"])
    : "";

$foods = null;

if ($category != "") {

    $keyword = "%" . $category . "%";

    $stmt = $conn->prepare(
        "SELECT
            f.food_id,
            f.image,
            f.food_name,
            f.description,
            f.price,
            r.restaurant_id,
            r.restaurant_name,
            c.category_name
         FROM food_items f
         JOIN restaurants r
            ON f.restaurant_id = r.restaurant_id
         LEFT JOIN categories c
            ON f.category_id = c.category_id
         WHERE f.availability = 1
         AND c.category_name LIKE ?
         ORDER BY f.food_id DESC"
    );

    $stmt->bind_param(
        "s",
        $keyword
    );

    $stmt->execute();

    $foods = $stmt->get_result();
}

?>

<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="UTF-8">

    <meta
        name="viewport"
        content="width=device-width, initial-scale=1.0"
    >

    <title>
        <?php echo htmlspecialchars($category); ?> - Foodie
    </title>

    <link
        rel="stylesheet"
        href="css/style.css"
    >

    <style>

        /* Category Page */

        .restaurant-link {
            color: #ff5722;
            text-decoration: none;
            font-weight: 600;
        }

        .restaurant-link:hover {
            text-decoration: underline;
        }

        .food-image {
            width: 100%;
            height: 220px;
            overflow: hidden;
        }

        .food-image img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            display: block;
        }

    </style>

</head>


<body>


<!-- =========================
     NAVIGATION BAR
========================= -->

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
            <a href="restaurants.php">
                Menu
            </a>
        </li>

        <li>
            <a href="index.php#about">
                About
            </a>
        </li>

        <li>
            <a href="contact.php">
                Contact
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

            <a href="orders.php">
                My Orders
            </a>

            <a href="cart.php">
                Cart
            </a>

            <a href="logout.php">
                Logout
            </a>

        <?php } else { ?>

            <a href="login.php">
                Login
            </a>

            <a href="register.php">
                Register
            </a>

        <?php } ?>

    </div>

</nav>


<!-- =========================
     CATEGORY SECTION
========================= -->

<section class="menu-page">


    <h1>

        🍽️

        <?php
        echo htmlspecialchars($category);
        ?>

    </h1>


    <p class="restaurant-subtitle">

        Food available in this category

    </p>


    <?php if ($foods && $foods->num_rows > 0) { ?>


        <div class="menu-grid">


            <?php while ($food = $foods->fetch_assoc()) { ?>


                <!-- FOOD CARD -->

                <div class="food-card">


                    <!-- FOOD IMAGE -->

                    <div class="food-image">

                        <img
                            src="images/<?php echo htmlspecialchars(
                                $food["image"] ?? "default.jpg"
                            ); ?>"
                            alt="<?php echo htmlspecialchars(
                                $food["food_name"]
                            ); ?>"
                        >

                    </div>


                    <!-- FOOD INFORMATION -->

                    <div class="food-info">


                        <!-- Food Name -->

                        <h3>

                            <?php
                            echo htmlspecialchars(
                                $food["food_name"]
                            );
                            ?>

                        </h3>


                        <!-- Category -->

                        <p class="food-category">

                            <?php
                            echo htmlspecialchars(
                                $food["category_name"]
                            );
                            ?>

                        </p>


                        <!-- Restaurant -->

                        <p>

                            🍽️

                            <a
                                href="menu.php?restaurant_id=<?php echo $food["restaurant_id"]; ?>"
                                class="restaurant-link"
                            >

                                <?php
                                echo htmlspecialchars(
                                    $food["restaurant_name"]
                                );
                                ?>

                            </a>

                        </p>


                        <!-- Description -->

                        <p>

                            <?php
                            echo htmlspecialchars(
                                $food["description"]
                            );
                            ?>

                        </p>


                        <!-- Bottom -->

                        <div class="food-bottom">


                            <!-- Price -->

                            <span class="price">

                                ₹<?php

                                echo number_format(
                                    $food["price"],
                                    2
                                );

                                ?>

                            </span>


                            <!-- Add To Cart -->

                            <a
                                href="add_to_cart.php?food_id=<?php echo $food["food_id"]; ?>"
                                class="add-cart-btn"
                            >

                                🛒 Add to Cart

                            </a>


                        </div>


                    </div>


                </div>


            <?php } ?>


        </div>


    <?php } else { ?>


        <!-- NO FOOD -->

        <div
            style="
                text-align:center;
                margin-top:50px;
            "
        >

            <h2>
                😔 No Food Found
            </h2>

            <p>
                No food items are available
                in this category.
            </p>

            <br>

            <a
                href="restaurants.php"
                class="menu-btn"
            >
                Browse Restaurants
            </a>

        </div>


    <?php } ?>


</section>


<!-- =========================
     FOOTER
========================= -->

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