<?php

require_once "config/db.php";

$sql = "SELECT * FROM restaurants";

$result = $conn->query($sql);

?>

<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="UTF-8">

    <meta name="viewport"
          content="width=device-width, initial-scale=1.0">

    <title>Restaurants - Foodie</title>

    <link rel="stylesheet" href="css/style.css">

</head>

<body>

<!-- Navbar -->

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
            <a href="#">Menu</a>
        </li>

        <li>
            <a href="#">About</a>
        </li>

        <li>
            <a href="#">Contact</a>
        </li>

    </ul>

    <div class="nav-buttons">

        <a href="login.php" class="login-btn">
            Login
        </a>

        <a href="register.php" class="register-btn">
            Register
        </a>

    </div>

</nav>


<!-- Restaurant Section -->

<section class="restaurants-page">

    <h1>🍽️ Explore Restaurants</h1>

    <p class="restaurant-subtitle">
        Choose your favourite restaurant and order delicious food.
    </p>


    <div class="restaurant-container">

        <?php

        if ($result->num_rows > 0) {

            while ($restaurant = $result->fetch_assoc()) {

        ?>

        <div class="restaurant-card">

           <div class="restaurant-image">

    <img
        src="images/<?php echo htmlspecialchars($restaurant["image"] ?? "default.jpg"); ?>"
        alt="<?php echo htmlspecialchars($restaurant["restaurant_name"]); ?>"
    >

</div>

            <div class="restaurant-info">

                <h3>
                    <?php
                    echo htmlspecialchars(
                        $restaurant["restaurant_name"]
                    );
                    ?>
                </h3>

                <p>
                    📍
                    <?php
                    echo htmlspecialchars(
                        $restaurant["address"]
                    );
                    ?>
                </p>

                <p>
                    📞
                    <?php
                    echo htmlspecialchars(
                        $restaurant["phone"]
                    );
                    ?>
                </p>

                <div class="restaurant-bottom">

                    <span>
                        ⭐ 4.5
                    </span>

                    <a
                        href="menu.php?restaurant_id=<?php echo $restaurant['restaurant_id']; ?>"
                        class="menu-btn"
                    >
                        View Menu
                    </a>

                </div>

            </div>

        </div>

        <?php

            }

        } else {

            echo "<p>No restaurants available.</p>";

        }

        ?>

    </div>

</section>


<!-- Footer -->

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