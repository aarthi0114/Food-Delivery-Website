<?php

require_once "config/db.php";


// Get search keyword
$search = isset($_GET["search"])
    ? trim($_GET["search"])
    : "";


// Search food
$foods = null;

if ($search != "") {

    $keyword = "%" . $search . "%";

    $stmt = $conn->prepare(
        "SELECT
            f.food_id,
            f.food_name,
            f.description,
            f.price,
            f.image,
            r.restaurant_id,
            r.restaurant_name,
            c.category_name
         FROM food_items f

         JOIN restaurants r
            ON f.restaurant_id = r.restaurant_id

         LEFT JOIN categories c
            ON f.category_id = c.category_id

         WHERE f.availability = 1
         AND (
            f.food_name LIKE ?
            OR f.description LIKE ?
            OR r.restaurant_name LIKE ?
            OR c.category_name LIKE ?
         )

         ORDER BY f.food_id DESC"
    );

    $stmt->bind_param(
        "ssss",
        $keyword,
        $keyword,
        $keyword,
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

    <meta name="viewport"
          content="width=device-width, initial-scale=1.0">

    <title>Search Food - Foodie</title>

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
            <a href="cart.php">
                🛒 Cart
            </a>
        </li>


        <li>
            <a href="orders.php">
                My Orders
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



<!-- ================= SEARCH ================= -->

<section class="menu-page">


    <h1>
        🔎 Search Food
    </h1>


    <form
        action="search.php"
        method="GET"
        style="
            max-width:600px;
            margin:20px auto;
            display:flex;
            gap:10px;
        "
    >

        <input
            type="text"
            name="search"
            value="<?php echo htmlspecialchars($search); ?>"
            placeholder="Search food, restaurant or category..."
            required
            style="
                flex:1;
                padding:12px;
                border:1px solid #ccc;
                border-radius:8px;
            "
        >


        <button
            type="submit"
            class="checkout-btn"
        >
            Search 🔎
        </button>

    </form>



    <?php if ($search != "") { ?>


        <h2 style="text-align:center; margin:30px 0;">

            Search Results for:

            "<?php
            echo htmlspecialchars($search);
            ?>"

        </h2>



        <?php if ($foods && $foods->num_rows > 0) { ?>


            <div class="menu-grid">


                <?php while ($food = $foods->fetch_assoc()) { ?>


                    <div class="food-card">


                        <!-- FOOD IMAGE -->

                        <div class="food-image">

                            🍽️

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


                            <p class="food-category">

                                <?php

                                echo htmlspecialchars(
                                    $food["category_name"]
                                    ?? "Food"
                                );

                                ?>

                            </p>


                            <p>

                                🍽️

                                <?php

                                echo htmlspecialchars(
                                    $food["restaurant_name"]
                                );

                                ?>

                            </p>


                            <p>

                                <?php

                                echo htmlspecialchars(
                                    $food["description"]
                                );

                                ?>

                            </p>


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


                        </div>


                    </div>


                <?php } ?>


            </div>


        <?php } else { ?>


            <div style="text-align:center; margin-top:40px;">

                <h2>
                    😔 No food found
                </h2>

                <p>
                    Try searching for another food,
                    restaurant or category.
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


    <?php } else { ?>


        <div style="text-align:center; margin-top:50px;">

            <h2>
                🍽️ What are you craving?
            </h2>

            <p>
                Search for your favourite food.
            </p>

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