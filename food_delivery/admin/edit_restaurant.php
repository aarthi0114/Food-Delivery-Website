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
// GET RESTAURANT ID
// =========================

$restaurant_id = isset($_GET["id"])
    ? (int)$_GET["id"]
    : 0;


if ($restaurant_id <= 0) {
    die("Invalid restaurant!");
}


// =========================
// UPDATE RESTAURANT
// =========================

if (isset($_POST["update_restaurant"])) {

    $name = trim($_POST["restaurant_name"]);
    $address = trim($_POST["address"]);
    $phone = trim($_POST["phone"]);


    // Get current image

    $stmt = $conn->prepare(
        "SELECT image
         FROM restaurants
         WHERE restaurant_id = ?"
    );

    $stmt->bind_param(
        "i",
        $restaurant_id
    );

    $stmt->execute();

    $result = $stmt->get_result();

    $restaurant = $result->fetch_assoc();


    if (!$restaurant) {
        die("Restaurant not found!");
    }


    $current_image = $restaurant["image"] ?? "";

    $image_name = $current_image;


    // =========================
    // NEW IMAGE UPLOAD
    // =========================

    if (
        isset($_FILES["restaurant_image"]) &&
        $_FILES["restaurant_image"]["error"] === 0
    ) {

        $file_name = $_FILES["restaurant_image"]["name"];

        $file_tmp = $_FILES["restaurant_image"]["tmp_name"];

        $file_size = $_FILES["restaurant_image"]["size"];


        $extension = strtolower(
            pathinfo(
                $file_name,
                PATHINFO_EXTENSION
            )
        );


        $allowed_extensions = [
            "jpg",
            "jpeg",
            "png",
            "webp"
        ];


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


        if (
            $file_size > 5 * 1024 * 1024
        ) {

            die(
                "Image size must be less than 5 MB."
            );
        }


        $image_name =
            time() . "_" .
            preg_replace(
                "/[^a-zA-Z0-9._-]/",
                "_",
                $file_name
            );


        $upload_path =
            "../images/" . $image_name;


        if (
            !move_uploaded_file(
                $file_tmp,
                $upload_path
            )
        ) {

            die("Image upload failed.");
        }


        // Delete old image

        if (!empty($current_image)) {

            $old_image_path =
                "../images/" .
                $current_image;


            if (
                file_exists(
                    $old_image_path
                )
            ) {

                unlink($old_image_path);
            }
        }
    }


    // =========================
    // UPDATE DATABASE
    // =========================

    $stmt = $conn->prepare(
        "UPDATE restaurants
         SET
            restaurant_name = ?,
            address = ?,
            phone = ?,
            image = ?
         WHERE restaurant_id = ?"
    );


    $stmt->bind_param(
        "ssssi",
        $name,
        $address,
        $phone,
        $image_name,
        $restaurant_id
    );


    $stmt->execute();


    header(
        "Location: restaurants.php"
    );

    exit();
}


// =========================
// GET RESTAURANT
// =========================

$stmt = $conn->prepare(
    "SELECT *
     FROM restaurants
     WHERE restaurant_id = ?"
);

$stmt->bind_param(
    "i",
    $restaurant_id
);

$stmt->execute();

$result = $stmt->get_result();

$restaurant = $result->fetch_assoc();


if (!$restaurant) {
    die("Restaurant not found!");
}

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
        Edit Restaurant - Foodie
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


<!-- NAVBAR -->

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
            href="restaurants.php"
            class="admin-home"
        >
            Restaurants
        </a>


        <a
            href="../logout.php"
            class="admin-logout"
        >
            Logout
        </a>

    </div>

</nav>



<!-- PAGE -->

<section class="manage-page">

    <h1>
        ✏️ Edit Restaurant
    </h1>


    <div class="manage-form">

        <h2>
            Update Restaurant Details
        </h2>


        <form
            method="POST"
            enctype="multipart/form-data"
        >


            <!-- NAME -->

            <input
                type="text"
                name="restaurant_name"
                value="<?php
                    echo htmlspecialchars(
                        $restaurant[
                            "restaurant_name"
                        ]
                    );
                ?>"
                placeholder="Restaurant Name"
                required
            >


            <!-- ADDRESS -->

            <input
                type="text"
                name="address"
                value="<?php
                    echo htmlspecialchars(
                        $restaurant[
                            "address"
                        ]
                    );
                ?>"
                placeholder="Address"
                required
            >


            <!-- PHONE -->

            <input
                type="tel"
                name="phone"
                value="<?php
                    echo htmlspecialchars(
                        $restaurant[
                            "phone"
                        ]
                    );
                ?>"
                maxlength="10"
                placeholder="Phone Number"
                required
            >


            <!-- CURRENT IMAGE -->

            <label class="image-label">

                Current Restaurant Image

            </label>


            <?php if (
                !empty(
                    $restaurant["image"]
                )
            ) { ?>

                <img
                    src="../images/<?php
                        echo htmlspecialchars(
                            $restaurant["image"]
                        );
                    ?>"
                    alt="Restaurant Image"
                    class="current-image"
                >

            <?php } else { ?>

                <p>
                    🍽️ No current image
                </p>

            <?php } ?>


            <!-- NEW IMAGE -->

            <label class="image-label">

                Change Restaurant Image

            </label>


            <input
                type="file"
                name="restaurant_image"
                accept=".jpg,.jpeg,.png,.webp"
            >


            <small>

                Leave empty if you don't want
                to change the image.

            </small>


            <br><br>


            <!-- UPDATE -->

            <button
                type="submit"
                name="update_restaurant"
            >

                💾 Update Restaurant

            </button>


        </form>


        <a
            href="restaurants.php"
            class="back-btn"
        >

            ⬅️ Back to Restaurants

        </a>

    </div>

</section>



<!-- FOOTER -->

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