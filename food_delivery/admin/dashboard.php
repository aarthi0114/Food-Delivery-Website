<?php

session_start();

require_once "../config/db.php";


// Admin check
if (!isset($_SESSION["user_id"]) || $_SESSION["role"] !== "admin") {
    header("Location: ../login.php");
    exit();
}


// Count users
$user_result = $conn->query(
    "SELECT COUNT(*) AS total FROM users WHERE role = 'customer'"
);
$total_users = $user_result->fetch_assoc()["total"];


// Count restaurants
$restaurant_result = $conn->query(
    "SELECT COUNT(*) AS total FROM restaurants"
);
$total_restaurants = $restaurant_result->fetch_assoc()["total"];


// Count food items
$food_result = $conn->query(
    "SELECT COUNT(*) AS total FROM food_items"
);
$total_food = $food_result->fetch_assoc()["total"];

// Count categories
$category_result = $conn->query(
    "SELECT COUNT(*) AS total FROM categories"
);

$total_categories =
    $category_result->fetch_assoc()["total"];
// Count orders
$order_result = $conn->query(
    "SELECT COUNT(*) AS total FROM orders"
);
$total_orders = $order_result->fetch_assoc()["total"];
// Count reviews
$review_result = $conn->query(
    "SELECT COUNT(*) AS total FROM reviews"
);

$total_reviews = $review_result->fetch_assoc()["total"];
?>

<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="UTF-8">

    <meta name="viewport"
          content="width=device-width, initial-scale=1.0">

    <title>Admin Dashboard - Foodie</title>

    <link rel="stylesheet" href="../css/style.css">

</head>

<body>


<!-- ADMIN NAVBAR -->

<nav class="admin-navbar">

    <div class="admin-logo">
        🍔 Foodie Admin
    </div>

    <div>

        <span class="admin-welcome">
            Welcome, <?php echo htmlspecialchars($_SESSION["name"]); ?>
        </span>

        <a href="../index.php" class="admin-home">
            View Website
        </a>

        <a href="../logout.php" class="admin-logout">
            Logout
        </a>

    </div>

</nav>


<!-- DASHBOARD -->

<section class="admin-dashboard">

    <h1>Admin Dashboard</h1>

    <p class="dashboard-subtitle">
        Manage your Foodie application
    </p>


   <div class="dashboard-cards">


    <!-- USERS -->

    <a href="users.php" class="dashboard-card">

        <div class="dashboard-icon">
            👥
        </div>

        <h3>Customers</h3>

        <p>
            <?php echo $total_users; ?>
        </p>

    </a>



    <!-- RESTAURANTS -->

    <a href="restaurants.php" class="dashboard-card">

        <div class="dashboard-icon">
            🍽️
        </div>

        <h3>Restaurants</h3>

        <p>
            <?php echo $total_restaurants; ?>
        </p>

    </a>



    <!-- CATEGORIES -->

    <a href="categories.php" class="dashboard-card">

        <div class="dashboard-icon">
            📂
        </div>

        <h3>Categories</h3>

        <p>
            <?php echo $total_categories; ?>
        </p>

    </a>



    <!-- FOOD ITEMS -->

    <a href="food_items.php" class="dashboard-card">

        <div class="dashboard-icon">
            🍕
        </div>

        <h3>Food Items</h3>

        <p>
            <?php echo $total_food; ?>
        </p>

    </a>



    <!-- ORDERS -->

    <a href="orders.php" class="dashboard-card">

        <div class="dashboard-icon">
            📦
        </div>

        <h3>Orders</h3>

        <p>
            <?php echo $total_orders; ?>
        </p>

    </a>
<!-- REVIEWS -->

<a href="reviews.php" class="dashboard-card">

    <div class="dashboard-icon">
        ⭐
    </div>

    <h3>Reviews</h3>

    <p>
        <?php echo $total_reviews; ?>
    </p>

</a>

</div>



    <!-- ADMIN MENU -->

    <div class="admin-menu">

        <a href="restaurants.php">
            🍽️ Manage Restaurants
        </a>

        <a href="categories.php">
            📂 Manage Categories
        </a>

        <a href="food_items.php">
            🍕 Manage Food Items
        </a>

        <a href="orders.php">
            📦 Manage Orders
        </a>

        <a href="users.php">
            👥 View Customers
        </a>

    </div>

</section>


<footer>

    <h3>🍔 Foodie Admin</h3>

    <p>
        Food Delivery Management System
    </p>

    <p>
        © 2026 Foodie
    </p>

</footer>


</body>
</html>