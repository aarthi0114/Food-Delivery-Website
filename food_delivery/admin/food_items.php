<?php

session_start();

require_once "../config/db.php";


// Admin check
if (!isset($_SESSION["user_id"]) || $_SESSION["role"] !== "admin") {
    header("Location: ../login.php");
    exit();
}


// =========================
// ADD FOOD ITEM
// =========================

if (isset($_POST["add_food"])) {

    $restaurant_id = (int)$_POST["restaurant_id"];
    $category_id = (int)$_POST["category_id"];
    $food_name = trim($_POST["food_name"]);
    $description = trim($_POST["description"]);
    $price = (float)$_POST["price"];

    $image_name = "";


    // =========================
    // IMAGE UPLOAD
    // =========================

    if (isset($_FILES["food_image"]) &&
        $_FILES["food_image"]["error"] === 0) {

        $file_name = $_FILES["food_image"]["name"];
        $file_tmp = $_FILES["food_image"]["tmp_name"];
        $file_size = $_FILES["food_image"]["size"];

        $extension = strtolower(
            pathinfo($file_name, PATHINFO_EXTENSION)
        );


        // Allowed image types

        $allowed_extensions = [
            "jpg",
            "jpeg",
            "png",
            "webp"
        ];


        if (!in_array($extension, $allowed_extensions)) {

            die("Only JPG, JPEG, PNG and WEBP images are allowed.");

        }


        // Maximum size = 5 MB

        if ($file_size > 5 * 1024 * 1024) {

            die("Image size must be less than 5 MB.");

        }


        // Create unique filename

        $image_name =
            time() . "_" .
            preg_replace(
                "/[^a-zA-Z0-9._-]/",
                "_",
                $file_name
            );


        // Upload location

        $upload_path =
            "../images/" . $image_name;


        if (!move_uploaded_file(
            $file_tmp,
            $upload_path
        )) {

            die("Image upload failed.");

        }

    }


    // =========================
    // INSERT FOOD
    // =========================

    $stmt = $conn->prepare(
        "INSERT INTO food_items
        (
            restaurant_id,
            category_id,
            food_name,
            description,
            price,
            image,
            availability
        )
        VALUES (?, ?, ?, ?, ?, ?, 1)"
    );


    $stmt->bind_param(
        "iissds",
        $restaurant_id,
        $category_id,
        $food_name,
        $description,
        $price,
        $image_name
    );


    $stmt->execute();


    header("Location: food_items.php");
    exit();
}


// =========================
// DELETE FOOD ITEM
// =========================

if (isset($_GET["delete"])) {

    $food_id = (int)$_GET["delete"];


    // Get image filename before deleting

    $stmt = $conn->prepare(
        "SELECT image
         FROM food_items
         WHERE food_id = ?"
    );

    $stmt->bind_param(
        "i",
        $food_id
    );

    $stmt->execute();

    $result = $stmt->get_result();

    $food = $result->fetch_assoc();


    // Delete database record

    $stmt = $conn->prepare(
        "DELETE FROM food_items
         WHERE food_id = ?"
    );

    $stmt->bind_param(
        "i",
        $food_id
    );

    $stmt->execute();


    // Delete image file

    if (!empty($food["image"])) {

        $image_path =
            "../images/" . $food["image"];

        if (file_exists($image_path)) {

            unlink($image_path);

        }

    }


    header("Location: food_items.php");
    exit();
}


// =========================
// GET RESTAURANTS
// =========================

$restaurants = $conn->query(
    "SELECT *
     FROM restaurants
     ORDER BY restaurant_name"
);


// =========================
// GET CATEGORIES
// =========================

$categories = $conn->query(
    "SELECT *
     FROM categories
     ORDER BY category_name"
);


// =========================
// GET FOOD ITEMS
// =========================

$foods = $conn->query(
    "SELECT
        f.*,
        r.restaurant_name,
        c.category_name
     FROM food_items f

     JOIN restaurants r
        ON f.restaurant_id = r.restaurant_id

     LEFT JOIN categories c
        ON f.category_id = c.category_id

     ORDER BY f.food_id DESC"
);

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
        Manage Food Items - Foodie
    </title>

    <link
        rel="stylesheet"
        href="../css/style.css"
    >

    <style>

        /* Food Image Preview */

        .admin-food-image {

            width: 120px;
            height: 90px;

            object-fit: cover;

            border-radius: 10px;

            margin-bottom: 10px;

        }

        .image-upload-label {

            display: block;

            margin-top: 10px;

            margin-bottom: 6px;

            font-weight: 600;

        }

        .manage-item {

            display: flex;

            justify-content: space-between;

            align-items: center;

            gap: 20px;

        }

    </style>

</head>


<body>


<!-- =========================
     NAVBAR
========================= -->

<nav class="admin-navbar">

    <div class="admin-logo">

        🍔 Foodie Admin

    </div>


    <div>

        <a
            href="dashboard.php"
            class="admin-home"
        >
            Dashboard
        </a>


        <a
            href="../logout.php"
            class="admin-logout"
        >
            Logout
        </a>

    </div>

</nav>


<!-- =========================
     PAGE
========================= -->

<section class="manage-page">


    <h1>
        🍕 Manage Food Items
    </h1>


    <!-- =========================
         ADD FOOD
    ========================= -->

    <div class="manage-form">

        <h2>
            Add New Food Item
        </h2>


        <form
            method="POST"
            enctype="multipart/form-data"
        >


            <!-- RESTAURANT -->

            <select
                name="restaurant_id"
                required
            >

                <option value="">
                    Select Restaurant
                </option>


                <?php while (
                    $restaurant =
                    $restaurants->fetch_assoc()
                ) { ?>

                    <option
                        value="<?php echo $restaurant["restaurant_id"]; ?>"
                    >

                        <?php
                        echo htmlspecialchars(
                            $restaurant["restaurant_name"]
                        );
                        ?>

                    </option>

                <?php } ?>

            </select>


            <!-- CATEGORY -->

            <select
                name="category_id"
                required
            >

                <option value="">
                    Select Category
                </option>


                <?php while (
                    $category =
                    $categories->fetch_assoc()
                ) { ?>

                    <option
                        value="<?php echo $category["category_id"]; ?>"
                    >

                        <?php
                        echo htmlspecialchars(
                            $category["category_name"]
                        );
                        ?>

                    </option>

                <?php } ?>

            </select>


            <!-- FOOD NAME -->

            <input
                type="text"
                name="food_name"
                placeholder="Food Name"
                required
            >


            <!-- DESCRIPTION -->

            <textarea
                name="description"
                placeholder="Food Description"
                required
            ></textarea>


            <!-- PRICE -->

            <input
                type="number"
                name="price"
                placeholder="Price"
                min="1"
                step="0.01"
                required
            >


            <!-- IMAGE -->

            <label class="image-upload-label">
                Food Image
            </label>

            <input
                type="file"
                name="food_image"
                accept=".jpg,.jpeg,.png,.webp"
                required
            >


            <!-- BUTTON -->

            <button
                type="submit"
                name="add_food"
            >

                ➕ Add Food Item

            </button>


        </form>

    </div>


    <!-- =========================
         FOOD LIST
    ========================= -->

    <div class="manage-list">


        <h2>
            Existing Food Items
        </h2>


        <?php if ($foods->num_rows > 0) { ?>


            <?php while (
                $food =
                $foods->fetch_assoc()
            ) { ?>


                <div class="manage-item">


                    <div>


                        <!-- IMAGE -->

                        <?php if (
                            !empty($food["image"]) &&
                            file_exists(
                                "../images/" .
                                $food["image"]
                            )
                        ) { ?>

                            <img
                                src="../images/<?php echo htmlspecialchars(
                                    $food["image"]
                                ); ?>"
                                alt="<?php echo htmlspecialchars(
                                    $food["food_name"]
                                ); ?>"
                                class="admin-food-image"
                            >

                        <?php } else { ?>

                            <div>
                                🍽️ No Image
                            </div>

                        <?php } ?>


                        <!-- FOOD NAME -->

                        <h3>

                            🍽️

                            <?php
                            echo htmlspecialchars(
                                $food["food_name"]
                            );
                            ?>

                        </h3>


                        <!-- RESTAURANT -->

                        <p>

                            Restaurant:

                            <?php
                            echo htmlspecialchars(
                                $food["restaurant_name"]
                            );
                            ?>

                        </p>


                        <!-- CATEGORY -->

                        <p>

                            Category:

                            <?php
                            echo htmlspecialchars(
                                $food["category_name"]
                                ?? "No Category"
                            );
                            ?>

                        </p>


                        <!-- PRICE -->

                        <p>

                            Price:

                            ₹<?php
                            echo number_format(
                                $food["price"],
                                2
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


                    </div>
                    <a
    href="edit_food.php?id=<?php echo $food["food_id"]; ?>"
    class="menu-btn"
>
    ✏️ Edit
</a>
                    <!-- DELETE -->

                    <a
                        href="food_items.php?delete=<?php echo $food["food_id"]; ?>"
                        class="delete-btn"
                        onclick="return confirm('Delete this food item?');"
                    >

                        🗑️ Delete

                    </a>


                </div>


            <?php } ?>


        <?php } else { ?>


            <p>
                No food items found.
            </p>


        <?php } ?>


    </div>


</section>


<!-- =========================
     FOOTER
========================= -->

<footer>

    <h3>
        🍔 Foodie Admin
    </h3>

    <p>
        Food Delivery Management System
    </p>

</footer>


</body>

</html>