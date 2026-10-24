<?php

session_start();

require_once "../config/db.php";


// Admin check
if (!isset($_SESSION["user_id"]) || $_SESSION["role"] !== "admin") {
    header("Location: ../login.php");
    exit();
}


// Add category
if (isset($_POST["add_category"])) {

    $category_name = trim($_POST["category_name"]);

    if ($category_name != "") {

        $stmt = $conn->prepare(
            "INSERT INTO categories (category_name)
             VALUES (?)"
        );

        $stmt->bind_param("s", $category_name);

        $stmt->execute();
    }

    header("Location: categories.php");
    exit();
}


// Delete category
if (isset($_GET["delete"])) {

    $category_id = (int)$_GET["delete"];

    $stmt = $conn->prepare(
        "DELETE FROM categories
         WHERE category_id = ?"
    );

    $stmt->bind_param("i", $category_id);

    $stmt->execute();

    header("Location: categories.php");
    exit();
}


// Get categories
$result = $conn->query(
    "SELECT * FROM categories
     ORDER BY category_id DESC"
);

?>

<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="UTF-8">

    <meta name="viewport"
          content="width=device-width, initial-scale=1.0">

    <title>Manage Categories - Foodie</title>

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


<!-- PAGE -->

<section class="manage-page">

    <h1>📂 Manage Categories</h1>


    <!-- ADD CATEGORY -->

    <div class="manage-form">

        <h2>Add New Category</h2>

        <form method="POST">

            <input
                type="text"
                name="category_name"
                placeholder="Enter category name"
                required
            >

            <button
                type="submit"
                name="add_category"
            >
                ➕ Add Category
            </button>

        </form>

    </div>


    <!-- CATEGORY LIST -->

    <div class="manage-list">

        <h2>Existing Categories</h2>

        <?php if ($result->num_rows > 0) { ?>

            <?php while ($category = $result->fetch_assoc()) { ?>

                <div class="manage-item">

                    <div>

                        <h3>
                            📂
                            <?php
                            echo htmlspecialchars(
                                $category["category_name"]
                            );
                            ?>
                        </h3>

                    </div>
                    <a
    href="edit_category.php?id=<?php echo $category['category_id']; ?>"
    class="menu-btn"
>
    ✏️ Edit
</a>


                    <a
                        href="categories.php?delete=<?php echo $category['category_id']; ?>"
                        class="delete-btn"
                        onclick="return confirm('Delete this category?');"
                    >
                        🗑️ Delete
                    </a>

                </div>

            <?php } ?>

        <?php } else { ?>

            <p>No categories found.</p>

        <?php } ?>

    </div>

</section>


<footer>

    <h3>🍔 Foodie Admin</h3>

    <p>Food Delivery Management System</p>

</footer>


</body>
</html>