<?php

session_start();

require_once "../config/db.php";


// Admin check
if (!isset($_SESSION["user_id"]) || $_SESSION["role"] !== "admin") {
    header("Location: ../login.php");
    exit();
}


// Get customers
$result = $conn->query(
    "SELECT user_id, name, email, phone
     FROM users
     WHERE role = 'customer'
     ORDER BY user_id DESC"
);

?>

<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="UTF-8">

    <meta name="viewport"
          content="width=device-width, initial-scale=1.0">

    <title>Customers - Foodie Admin</title>

    <link rel="stylesheet" href="../css/style.css">

</head>

<body>


<!-- NAVBAR -->

<nav class="admin-navbar">

    <div class="admin-logo">
        🍔 Foodie Admin
    </div>

    <div>

        <a href="dashboard.php" class="admin-home">
            Dashboard
        </a>

        <a href="../logout.php" class="admin-logout">
            Logout
        </a>

    </div>

</nav>


<!-- CUSTOMERS -->

<section class="manage-page">

    <h1>👥 Customers</h1>

    <p class="orders-subtitle">
        Registered Foodie customers
    </p>


    <div class="customers-container">

        <?php if ($result->num_rows > 0) { ?>

            <?php while ($user = $result->fetch_assoc()) { ?>

                <div class="customer-card">

                    <div class="customer-icon">
                        👤
                    </div>

                    <div class="customer-info">

                        <h3>
                            <?php
                            echo htmlspecialchars(
                                $user["name"]
                            );
                            ?>
                        </h3>

                        <p>
                            🆔 Customer ID:
                            <?php
                            echo $user["user_id"];
                            ?>
                        </p>

                        <p>
                            ✉️
                            <?php
                            echo htmlspecialchars(
                                $user["email"]
                            );
                            ?>
                        </p>

                        <p>
                            📞
                            <?php
                            echo htmlspecialchars(
                                $user["phone"]
                            );
                            ?>
                        </p>
                        <a
    href="delete_user.php?id=<?php echo $user["user_id"]; ?>"
    class="delete-btn"
    onclick="return confirm('Are you sure you want to delete this customer?');"
>
    🗑️ Delete
</a>

                    </div>

                </div>

            <?php } ?>

        <?php } else { ?>

            <div class="empty-orders">

                <h2>👥 No Customers</h2>

                <p>
                    No customers have registered yet.
                </p>

            </div>

        <?php } ?>

    </div>

</section>


<footer>

    <h3>🍔 Foodie Admin</h3>

    <p>
        Food Delivery Management System
    </p>

</footer>


</body>

</html>