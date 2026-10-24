<?php

session_start();

require_once "../config/db.php";


// Admin check
if (!isset($_SESSION["user_id"]) || $_SESSION["role"] !== "admin") {
    header("Location: ../login.php");
    exit();
}


// Update order status
if (isset($_POST["update_status"])) {

    $order_id = (int)$_POST["order_id"];
    $status = $_POST["status"];

    $allowed_statuses = [
        "Pending",
        "Accepted",
        "Preparing",
        "Out for Delivery",
        "Delivered",
        "Cancelled"
    ];

    if (in_array($status, $allowed_statuses)) {

        $stmt = $conn->prepare(
            "UPDATE orders
             SET status = ?
             WHERE order_id = ?"
        );

        $stmt->bind_param(
            "si",
            $status,
            $order_id
        );

        $stmt->execute();
    }

    header("Location: orders.php");
    exit();
}


// Get all orders
$result = $conn->query(
    "SELECT
        o.order_id,
        o.total_amount,
        o.delivery_address,
        o.phone,
        o.status,
        o.order_date,
        u.name,
        u.email
     FROM orders o
     JOIN users u
        ON o.user_id = u.user_id
     ORDER BY o.order_date DESC"
);

?>

<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="UTF-8">

    <meta name="viewport"
          content="width=device-width, initial-scale=1.0">

    <title>Manage Orders - Foodie</title>

    <link rel="stylesheet" href="../css/style.css">
    <style>

    .status-badge {
        display: inline-block;
        padding: 8px 14px;
        border-radius: 20px;
        font-weight: bold;
        font-size: 14px;
    }

    .status-pending {
        background: #fff3cd;
        color: #856404;
    }

    .status-accepted {
        background: #cfe2ff;
        color: #084298;
    }

    .status-preparing {
        background: #ffe5d0;
        color: #9a3412;
    }

    .status-delivery {
        background: #e2d9f3;
        color: #59359a;
    }

    .status-delivered {
        background: #d1e7dd;
        color: #0f5132;
    }

    .status-cancelled {
        background: #f8d7da;
        color: #842029;
    }

</style>

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


<!-- ORDERS PAGE -->

<section class="manage-page">

    <h1>📦 Manage Orders</h1>

    <p class="orders-subtitle">
        View customer orders and update order status.
    </p>


    <?php if ($result->num_rows > 0) { ?>

        <div class="admin-orders-container">

            <?php while ($order = $result->fetch_assoc()) { ?>

                <div class="admin-order-card">


                    <!-- ORDER HEADER -->

                    <div class="admin-order-header">

                        <div>

                            <h2>
                                Order #<?php
                                echo $order["order_id"];
                                ?>
                            </h2>

                            <p>
                                👤
                                <?php
                                echo htmlspecialchars(
                                    $order["name"]
                                );
                                ?>
                            </p>

                            <p>
                                ✉️
                                <?php
                                echo htmlspecialchars(
                                    $order["email"]
                                );
                                ?>
                            </p>

                        </div>


                        <div class="admin-order-status">

    <?php
    $status_class = "";

    switch ($order["status"]) {
        case "Pending":
            $status_class = "status-pending";
            break;

        case "Accepted":
            $status_class = "status-accepted";
            break;

        case "Preparing":
            $status_class = "status-preparing";
            break;

        case "Out for Delivery":
            $status_class = "status-delivery";
            break;

        case "Delivered":
            $status_class = "status-delivered";
            break;

        case "Cancelled":
            $status_class = "status-cancelled";
            break;
    }
    ?>

    <span class="status-badge <?php echo $status_class; ?>">
        <?php echo htmlspecialchars($order["status"]); ?>
    </span>

</div>

                    </div>
                    <!-- ORDER ITEMS -->

<div class="admin-order-items">

    <h3>🍽️ Ordered Food</h3>

    <?php

    $item_stmt = $conn->prepare(
        "SELECT
            oi.quantity,
            oi.price,
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

    $item_result = $item_stmt->get_result();

    ?>


    <?php while (
        $item = $item_result->fetch_assoc()
    ) { ?>

        <p>

            🍕
            <strong>
                <?php
                echo htmlspecialchars(
                    $item["food_name"]
                );
                ?>
            </strong>

            ×
            <?php
            echo $item["quantity"];
            ?>

            — ₹<?php
            echo number_format(
                $item["price"],
                2
            );
            ?>

        </p>

    <?php } ?>

</div>


                    <!-- ORDER DETAILS -->

                    <div class="admin-order-details">


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
                            <strong>Date:</strong>
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


                    <!-- STATUS UPDATE -->

                    <div class="status-update">

                        <form method="POST">

                            <input
                                type="hidden"
                                name="order_id"
                                value="<?php
                                echo $order["order_id"];
                                ?>"
                            >

                            <select name="status">

                                <option
                                    value="Pending"
                                    <?php
                                    if ($order["status"] == "Pending")
                                        echo "selected";
                                    ?>
                                >
                                    Pending
                                </option>

                                <option
                                    value="Accepted"
                                    <?php
                                    if ($order["status"] == "Accepted")
                                        echo "selected";
                                    ?>
                                >
                                    Accepted
                                </option>

                                <option
                                    value="Preparing"
                                    <?php
                                    if ($order["status"] == "Preparing")
                                        echo "selected";
                                    ?>
                                >
                                    Preparing
                                </option>

                                <option
                                    value="Out for Delivery"
                                    <?php
                                    if ($order["status"] == "Out for Delivery")
                                        echo "selected";
                                    ?>
                                >
                                    Out for Delivery
                                </option>

                                <option
                                    value="Delivered"
                                    <?php
                                    if ($order["status"] == "Delivered")
                                        echo "selected";
                                    ?>
                                >
                                    Delivered
                                </option>

                                <option
                                    value="Cancelled"
                                    <?php
                                    if ($order["status"] == "Cancelled")
                                        echo "selected";
                                    ?>
                                >
                                    Cancelled
                                </option>

                            </select>


                            <button
                                type="submit"
                                name="update_status"
                            >
                                🔄 Update Status
                            </button>

                        </form>

                    </div>


                </div>

            <?php } ?>

        </div>

    <?php } else { ?>

        <div class="empty-orders">

            <h2>📦 No Orders Yet</h2>

            <p>
                No customer orders have been placed.
            </p>

        </div>

    <?php } ?>

</section>


<footer>

    <h3>🍔 Foodie Admin</h3>

    <p>
        Food Delivery Management System
    </p>

</footer>


</body>

</html>