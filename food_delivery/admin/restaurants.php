<?php

session_start();

require_once "../config/db.php";

// Admin check
if (!isset($_SESSION["user_id"]) || $_SESSION["role"] !== "admin") {
    header("Location: ../login.php");
    exit();
}


// =============================
// ADD RESTAURANT
// =============================

if (isset($_POST["add_restaurant"])) {

    $name = trim($_POST["restaurant_name"]);
    $address = trim($_POST["address"]);
    $phone = trim($_POST["phone"]);

    $image_name = "";


    // =============================
    // IMAGE UPLOAD
    // =============================

    if (
        isset($_FILES["restaurant_image"]) &&
        $_FILES["restaurant_image"]["error"] === 0
    ) {

        $file_name = $_FILES["restaurant_image"]["name"];
        $file_tmp = $_FILES["restaurant_image"]["tmp_name"];
        $file_size = $_FILES["restaurant_image"]["size"];

        $extension = strtolower(
            pathinfo($file_name, PATHINFO_EXTENSION)
        );

        $allowed_extensions = [
            "jpg",
            "jpeg",
            "png",
            "webp"
        ];


        // Check extension
        if (!in_array($extension, $allowed_extensions)) {

            die(
                "Only JPG, JPEG, PNG and WEBP images are allowed."
            );
        }


        // Check size - 5 MB
        if ($file_size > 5 * 1024 * 1024) {

            die("Image size must be less than 5 MB.");
        }


        // Create unique image name
        $image_name =
            time() . "_" .
            preg_replace(
                "/[^a-zA-Z0-9._-]/",
                "_",
                $file_name
            );


        // Upload path
        $upload_path = "../images/" . $image_name;


        // Move image
        if (!move_uploaded_file(
            $file_tmp,
            $upload_path
        )) {

            die("Image upload failed.");
        }
    }


    // =============================
    // INSERT RESTAURANT
    // =============================

    if ($name != "") {

        $stmt = $conn->prepare(
            "INSERT INTO restaurants
            (restaurant_name, address, phone, image)
            VALUES (?, ?, ?, ?)"
        );

        $stmt->bind_param(
            "ssss",
            $name,
            $address,
            $phone,
            $image_name
        );

        $stmt->execute();
    }


    header("Location: restaurants.php");
    exit();
}



// =============================
// DELETE RESTAURANT
// =============================

if (isset($_GET["delete"])) {

    $restaurant_id = (int)$_GET["delete"];


    // Get image before deleting
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

    $result_image = $stmt->get_result();

    $restaurant = $result_image->fetch_assoc();


    // Delete restaurant
    $stmt = $conn->prepare(
        "DELETE FROM restaurants
         WHERE restaurant_id = ?"
    );

    $stmt->bind_param(
        "i",
        $restaurant_id
    );

    $stmt->execute();


    // Delete image file
    if (
        !empty($restaurant["image"])
    ) {

        $image_path =
            "../images/" .
            $restaurant["image"];

        if (file_exists($image_path)) {

            unlink($image_path);
        }
    }


    header("Location: restaurants.php");
    exit();
}



// =============================
// GET RESTAURANTS
// =============================

$result = $conn->query(
    "SELECT *
     FROM restaurants
     ORDER BY restaurant_id DESC"
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

    <title>Manage Restaurants - Foodie</title>

    <link
        rel="stylesheet"
        href="../css/style.css"
    >

    <style>

        .restaurant-admin-image {

            width: 120px;
            height: 90px;

            object-fit: cover;

            border-radius: 10px;

            margin-bottom: 10px;
        }

        .no-image {

            color: #888;

            font-size: 14px;

            margin-bottom: 10px;
        }

    </style>

</head>


<body>


<!-- =============================
     NAVBAR
============================= -->

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



<!-- =============================
     PAGE
============================= -->

<section class="manage-page">


    <h1>
        🍽️ Manage Restaurants
    </h1>



    <!-- =============================
         ADD RESTAURANT
    ============================= -->

    <div class="manage-form">

        <h2>
            Add New Restaurant
        </h2>


        <form
            method="POST"
            enctype="multipart/form-data"
        >


            <input
                type="text"
                name="restaurant_name"
                placeholder="Restaurant Name"
                required
            >


            <input
                type="text"
                name="address"
                placeholder="Address"
                required
            >


            <input
                type="tel"
                name="phone"
                placeholder="Phone Number"
                maxlength="10"
                required
            >


            <!-- RESTAURANT IMAGE -->

            <label>
                Restaurant Image
            </label>

            <input
                type="file"
                name="restaurant_image"
                accept=".jpg,.jpeg,.png,.webp"
                required
            >


            <button
                type="submit"
                name="add_restaurant"
            >

                ➕ Add Restaurant

            </button>


        </form>

    </div>



    <!-- =============================
         RESTAURANT LIST
    ============================= -->

    <div class="manage-list">


        <h2>
            Existing Restaurants
        </h2>


        <?php if ($result->num_rows > 0) { ?>


            <?php while (
                $restaurant =
                $result->fetch_assoc()
            ) { ?>


                <div class="manage-item">


                    <div>


                        <!-- IMAGE -->

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
                                alt="<?php
                                    echo htmlspecialchars(
                                        $restaurant[
                                            "restaurant_name"
                                        ]
                                    );
                                ?>"
                                class="restaurant-admin-image"
                            >

                        <?php } else { ?>

                            <p class="no-image">
                                No Image
                            </p>

                        <?php } ?>



                        <!-- NAME -->

                        <h3>

                            <?php

                            echo htmlspecialchars(
                                $restaurant[
                                    "restaurant_name"
                                ]
                            );

                            ?>

                        </h3>



                        <!-- ADDRESS -->

                        <p>

                            📍

                            <?php

                            echo htmlspecialchars(
                                $restaurant["address"]
                            );

                            ?>

                        </p>



                        <!-- PHONE -->

                        <p>

                            📞

                            <?php

                            echo htmlspecialchars(
                                $restaurant["phone"]
                            );

                            ?>

                        </p>


                    </div>
                    <a
    href="edit_restaurant.php?id=<?php echo $restaurant['restaurant_id']; ?>"
    class="menu-btn"
>
    ✏️ Edit
</a>


                    <!-- DELETE -->

                    <a
                        href="restaurants.php?delete=<?php
                            echo $restaurant[
                                "restaurant_id"
                            ];
                        ?>"
                        class="delete-btn"
                        onclick="return confirm(
                            'Delete this restaurant?'
                        );"
                    >

                        🗑️ Delete

                    </a>


                </div>


            <?php } ?>


        <?php } else { ?>


            <p>
                No restaurants found.
            </p>


        <?php } ?>


    </div>


</section>



<!-- =============================
     FOOTER
============================= -->

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