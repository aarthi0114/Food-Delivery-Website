<?php

session_start();

require_once "../config/db.php";


// =========================
// ADMIN CHECK
// =========================

if (!isset($_SESSION["user_id"]) || $_SESSION["role"] !== "admin") {

    header("Location: ../login.php");
    exit();
}


// =========================
// GET FOOD ID
// =========================

$food_id = isset($_GET["id"])
    ? (int)$_GET["id"]
    : 0;


// =========================
// VALIDATE FOOD ID
// =========================

if ($food_id <= 0) {

    die("Invalid food item!");
}


// =========================
// UPDATE FOOD ITEM
// =========================

if (isset($_POST["update_food"])) {

    $restaurant_id = (int)$_POST["restaurant_id"];

    $category_id = (int)$_POST["category_id"];

    $food_name = trim($_POST["food_name"]);

    $description = trim($_POST["description"]);

    $price = (float)$_POST["price"];


    // Get current image
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

    $image_result = $stmt->get_result();

    $current_food = $image_result->fetch_assoc();


    $current_image = $current_food["image"] ?? "";

    $image_name = $current_image;


    // =========================
    // NEW IMAGE UPLOAD
    // =========================

    if (
        isset($_FILES["food_image"]) &&
        $_FILES["food_image"]["error"] === 0
    ) {

        $file_name = $_FILES["food_image"]["name"];

        $file_tmp = $_FILES["food_image"]["tmp_name"];

        $file_size = $_FILES["food_image"]["size"];


        // Get extension

        $extension = strtolower(
            pathinfo(
                $file_name,
                PATHINFO_EXTENSION
            )
        );


        // Allowed extensions

        $allowed_extensions = [
            "jpg",
            "jpeg",
            "png",
            "webp"
        ];


        // Check extension

        if (
            !in_array(
                $extension,
                $allowed_extensions
            )
        ) {

            die(
                "Only JPG, JPEG, PNG and WEBP images are allowed."
            );
        }


        // Check file size

        if (
            $file_size > 5 * 1024 * 1024
        ) {

            die(
                "Image size must be less than 5 MB."
            );
        }


        // Create new unique filename

        $image_name =
            time() . "_" .
            preg_replace(
                "/[^a-zA-Z0-9._-]/",
                "_",
                $file_name
            );


        // Upload path

        $upload_path =
            "../images/" . $image_name;


        // Upload image

        if (
            !move_uploaded_file(
                $file_tmp,
                $upload_path
            )
        ) {

            die("Image upload failed.");
        }


        // =========================
        // DELETE OLD IMAGE
        // =========================

        if (
            !empty($current_image)
        ) {

            $old_image_path =
                "../images/" .
                $current_image;


            if (
                file_exists(
                    $old_image_path
                )
            ) {

                unlink(
                    $old_image_path
                );
            }
        }
    }


    // =========================
    // UPDATE DATABASE
    // =========================

    $stmt = $conn->prepare(
        "UPDATE food_items
         SET
            restaurant_id = ?,
            category_id = ?,
            food_name = ?,
            description = ?,
            price = ?,
            image = ?
         WHERE food_id = ?"
    );


    $stmt->bind_param(
        "iissdsi",
        $restaurant_id,
        $category_id,
        $food_name,
        $description,
        $price,
        $image_name,
        $food_id
    );


    $stmt->execute();


    // Go back to food items

    header(
        "Location: food_items.php"
    );

    exit();
}


// =========================
// GET FOOD DETAILS
// =========================

$stmt = $conn->prepare(
    "SELECT *
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


if (!$food) {

    die("Food item not found!");
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
        Edit Food Item - Foodie
    </title>


    <link
        rel="stylesheet"
        href="../css/style.css"
    >


    <style>

        .current-image {

            width: 180px;

            height: 130px;

            object-fit: cover;

            border-radius: 10px;

            display: block;

            margin-top: 10px;

            margin-bottom: 15px;
        }


        .image-label {

            display: block;

            margin-top: 15px;

            margin-bottom: 8px;

            font-weight: bold;
        }


        .back-btn {

            display: inline-block;

            margin-top: 15px;

            padding: 10px 18px;

            text-decoration: none;

            border-radius: 6px;

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
            href="food_items.php"
            class="admin-home"
        >
            Food Items
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
        ✏️ Edit Food Item
    </h1>


    <div class="manage-form">


        <h2>
            Update Food Details
        </h2>



        <!-- =========================
             FORM
        ========================= -->

        <form
            method="POST"
            enctype="multipart/form-data"
        >


            <!-- =========================
                 RESTAURANT
            ========================= -->

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
                        value="<?php
                            echo $restaurant[
                                "restaurant_id"
                            ];
                        ?>"
                        <?php

                        if (
                            $restaurant[
                                "restaurant_id"
                            ]
                            ==
                            $food[
                                "restaurant_id"
                            ]
                        ) {

                            echo "selected";
                        }

                        ?>
                    >


                        <?php

                        echo htmlspecialchars(
                            $restaurant[
                                "restaurant_name"
                            ]
                        );

                        ?>


                    </option>


                <?php } ?>


            </select>



            <!-- =========================
                 CATEGORY
            ========================= -->

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
                        value="<?php
                            echo $category[
                                "category_id"
                            ];
                        ?>"
                        <?php

                        if (
                            $category[
                                "category_id"
                            ]
                            ==
                            $food[
                                "category_id"
                            ]
                        ) {

                            echo "selected";
                        }

                        ?>
                    >


                        <?php

                        echo htmlspecialchars(
                            $category[
                                "category_name"
                            ]
                        );

                        ?>


                    </option>


                <?php } ?>


            </select>



            <!-- =========================
                 FOOD NAME
            ========================= -->

            <input
                type="text"
                name="food_name"
                value="<?php

                    echo htmlspecialchars(
                        $food[
                            "food_name"
                        ]
                    );

                ?>"
                placeholder="Food Name"
                required
            >



            <!-- =========================
                 DESCRIPTION
            ========================= -->

            <textarea
                name="description"
                placeholder="Food Description"
                required
            ><?php

                echo htmlspecialchars(
                    $food[
                        "description"
                    ]
                );

            ?></textarea>



            <!-- =========================
                 PRICE
            ========================= -->

            <input
                type="number"
                name="price"
                value="<?php

                    echo htmlspecialchars(
                        $food[
                            "price"
                        ]
                    );

                ?>"
                min="1"
                step="0.01"
                placeholder="Price"
                required
            >



            <!-- =========================
                 CURRENT IMAGE
            ========================= -->

            <label class="image-label">

                Current Food Image

            </label>


            <?php if (
                !empty(
                    $food["image"]
                )
            ) { ?>


                <img
                    src="../images/<?php

                        echo htmlspecialchars(
                            $food["image"]
                        );

                    ?>"
                    alt="Food Image"
                    class="current-image"
                >


            <?php } else { ?>


                <p>
                    🍽️ No current image
                </p>


            <?php } ?>



            <!-- =========================
                 NEW IMAGE
            ========================= -->

            <label class="image-label">

                Change Food Image

            </label>


            <input
                type="file"
                name="food_image"
                accept=".jpg,.jpeg,.png,.webp"
            >


            <small>

                Leave empty if you don't want
                to change the image.

            </small>



            <br><br>



            <!-- =========================
                 UPDATE BUTTON
            ========================= -->

            <button
                type="submit"
                name="update_food"
            >

                💾 Update Food Item

            </button>


        </form>


        <!-- BACK -->

        <a
            href="food_items.php"
            class="back-btn"
        >

            ⬅️ Back to Food Items

        </a>


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