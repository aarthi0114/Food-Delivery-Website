<?php

session_start();

require_once "config/db.php";


// Get restaurants
$restaurant_stmt = $conn->prepare(
    "SELECT restaurant_id, restaurant_name, address, image
     FROM restaurants
     ORDER BY restaurant_id DESC
     LIMIT 6"
);

$restaurant_stmt->execute();

$restaurant_result =
    $restaurant_stmt->get_result();

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Foodie - Food Delivery</title>

    <link rel="stylesheet" href="css/style.css">
</head>

<body>

    <!-- Navigation Bar -->
    <nav class="navbar">

        <div class="logo">
            🍔 Foodie
        </div>

        <ul class="nav-links">
            <li><a href="index.php">Home</a></li>
            <li><a href="restaurants.php">Restaurants</a></li>
            <li><a href="restaurants.php">Menu</a></li>
            <li><a href="#about">About</a></li>
            <li><a href="contact.php">Contact</a></li>
        </ul>

        <div class="nav-buttons">
            <?php if (isset($_SESSION["user_id"])): ?>

    <span>Welcome, <?php echo htmlspecialchars($_SESSION["name"]); ?></span>
    <a href="orders.php">My Orders</a>
    <a href="cart.php">Cart</a>
    <a href="logout.php">Logout</a>

<?php else: ?>

    <a href="login.php">Login</a>
    <a href="register.php">Register</a>

<?php endif; ?>           
        </div>

    </nav>


    <!-- Hero Section -->
    <section class="hero">

        <div class="hero-content">

            <h1>
                Delicious Food,
                <span>Delivered Fast!</span>
            </h1>

            <p>
                Order your favourite food from the best restaurants
                near you.
            </p>

            <form
    action="search.php"
    method="GET"
    class="search-box"
>

    <input
        type="text"
        name="search"
        placeholder="Search for food..."
        required
    >

    <button type="submit">
        Search 🔎
    </button>

</form>
        </div>

    </section>


    <!-- Categories -->
    <section class="categories">

        <h2>Explore Categories</h2>

        <div class="category-container">

            <a href="category.php?category=Pizza" class="category-card">
    <div class="category-icon">🍕</div>
    <h3>Pizza</h3>
</a>
<a href="category.php?category=Burger" class="category-card">
    <div class="category-icon">🍔</div>
    <h3>Burger</h3>
</a>

<a href="category.php?category=Biryani" class="category-card">
    <div class="category-icon">🍗</div>
    <h3>Biryani</h3>
</a>
<a href="category.php?category=South%20Indian" class="category-card">
    <div class="category-icon">🥘</div>
    <h3>South Indian</h3>
</a>
<a href="category.php?category=Chinese" class="category-card">
    <div class="category-icon">🍜</div>
    <h3>Chinese</h3>
</a>
        <a href="category.php?category=Desserts" class="category-card">
    <div class="category-icon">🍰</div>
    <h3>Desserts</h3>
</a>
<a href="category.php?category=Snacks" class="category-card">
    <div class="category-icon">🍟</div>
    <h3>Snacks</h3>
</a>
        </div>

    </section>


    <!-- Popular Restaurants -->
   <!-- Popular Restaurants -->

<section class="restaurants">

    <h2>Popular Restaurants</h2>

    <div class="restaurant-container">

        <?php if ($restaurant_result->num_rows > 0) { ?>

            <?php while ($restaurant = $restaurant_result->fetch_assoc()) { ?>

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

                        <div class="restaurant-bottom">

                            <span>
                                🍽️ Food
                            </span>

                            <a
                                href="menu.php?restaurant_id=<?php echo $restaurant["restaurant_id"]; ?>"
                                class="menu-btn"
                            >
                                View Menu
                            </a>

                        </div>

                    </div>

                </div>

            <?php } ?>

        <?php } else { ?>

            <p>
                No restaurants available.
            </p>

        <?php } ?>

    </div>

</section>
<!-- About Section -->
<!-- About Section -->
<section id="about" style="
    padding: 60px 10%;
    text-align: center;
    background: #fff8f0;
">

    <h2 style="
        font-size: 36px;
        margin-bottom: 25px;
        color: #ff5722;
    ">
        About Us
    </h2>

    <p style="
        max-width: 850px;
        margin: 0 auto 18px;
        font-size: 17px;
        line-height: 1.8;
        color: #555;
    ">
        Welcome to our Food Delivery System!
        We provide a simple and convenient way to discover restaurants,
        explore delicious food, add items to your cart, and place orders online.
    </p>

    <p style="
        max-width: 850px;
        margin: 0 auto;
        font-size: 17px;
        line-height: 1.8;
        color: #555;
    ">
        Our goal is to make food ordering easy, fast, and user-friendly.
        Customers can browse restaurants, search for food, manage their cart,
        place orders, and share reviews.
    </p>

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