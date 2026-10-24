<?php

session_start();

require_once "../config/db.php";

// Admin check
if (!isset($_SESSION["user_id"]) || $_SESSION["role"] !== "admin") {
    header("Location: ../login.php");
    exit();
}

// Get all reviews
$result = $conn->query(
    "SELECT
        r.review_id,
        r.rating,
        r.comment,
        r.created_at,
        u.name AS user_name,
        u.email,
        f.food_name
     FROM reviews r
     JOIN users u
        ON r.user_id = u.user_id
     JOIN food_items f
        ON r.food_id = f.food_id
     ORDER BY r.created_at DESC"
);

?>

<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="UTF-8">

    <meta name="viewport"
          content="width=device-width, initial-scale=1.0">

    <title>Reviews - Foodie Admin</title>

    <link rel="stylesheet" href="../css/style.css">

    <style>

        .reviews-container {
            max-width: 1000px;
            margin: 30px auto;
        }

        .review-card {
            background: white;
            padding: 25px;
            margin-bottom: 20px;
            border-radius: 12px;
            box-shadow: 0 4px 12px rgba(0,0,0,0.08);
        }

        .review-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 15px;
        }

        .review-user h3 {
            margin: 0 0 5px;
        }

        .review-user p {
            margin: 4px 0;
            color: #666;
        }

        .review-rating {
            font-size: 20px;
        }

        .review-food {
            color: #ff5722;
            font-weight: bold;
            margin-bottom: 10px;
        }

        .review-comment {
            color: #444;
            line-height: 1.6;
        }

        .review-date {
            color: #888;
            font-size: 14px;
            margin-top: 12px;
        }

    </style>

</head>

<body>

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


<section class="manage-page">

    <h1>⭐ Customer Reviews</h1>

    <p class="orders-subtitle">
        View ratings and reviews submitted by customers.
    </p>


    <div class="reviews-container">

        <?php if ($result->num_rows > 0) { ?>

            <?php while ($review = $result->fetch_assoc()) { ?>

                <div class="review-card">

                    <div class="review-header">

                        <div class="review-user">

                            <h3>
                                👤
                                <?php
                                echo htmlspecialchars(
                                    $review["user_name"]
                                );
                                ?>
                            </h3>

                            <p>
                                ✉️
                                <?php
                                echo htmlspecialchars(
                                    $review["email"]
                                );
                                ?>
                            </p>

                        </div>

                        <div class="review-rating">

                            <?php
                            echo str_repeat(
                                "⭐",
                                (int)$review["rating"]
                            );
                            ?>

                        </div>

                    </div>


                    <div class="review-food">

                        🍽️
                        <?php
                        echo htmlspecialchars(
                            $review["food_name"]
                        );
                        ?>

                    </div>


                    <div class="review-comment">

                        <?php
                        if (!empty($review["comment"])) {

                            echo htmlspecialchars(
                                $review["comment"]
                            );

                        } else {

                            echo "No comment provided.";

                        }
                        ?>

                    </div>


                    <div class="review-date">

                        📅
                        <?php
                        echo date(
                            "d-m-Y h:i A",
                            strtotime($review["created_at"])
                        );
                        ?>

                    </div>

                </div>

            <?php } ?>

        <?php } else { ?>

            <div class="empty-orders">

                <h2>⭐ No Reviews Yet</h2>

                <p>
                    No customer reviews have been submitted.
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