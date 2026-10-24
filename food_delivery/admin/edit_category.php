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
// GET CATEGORY ID
// =========================

$category_id = isset($_GET["id"])
    ? (int)$_GET["id"]
    : 0;


if ($category_id <= 0) {

    die("Invalid category!");
}


// =========================
// UPDATE CATEGORY
// =========================

if (isset($_POST["update_category"])) {

    $category_name =
        trim($_POST["category_name"]);


    if ($category_name == "") {

        die("Category name is required.");
    }


    $stmt = $conn->prepare(
        "UPDATE categories
         SET category_name = ?
         WHERE category_id = ?"
    );


    $stmt->bind_param(
        "si",
        $category_name,
        $category_id
    );


    $stmt->execute();


    header(
        "Location: categories.php"
    );

    exit();
}


// =========================
// GET CATEGORY
// =========================

$stmt = $conn->prepare(
    "SELECT *
     FROM categories
     WHERE category_id = ?"
);

$stmt->bind_param(
    "i",
    $category_id
);

$stmt->execute();


$result = $stmt->get_result();


$category = $result->fetch_assoc();


if (!$category) {

    die("Category not found!");
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
        Edit Category - Foodie
    </title>


    <link
        rel="stylesheet"
        href="../css/style.css"
    >

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
            href="categories.php"
            class="admin-home"
        >
            Categories
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
        ✏️ Edit Category
    </h1>


    <div class="manage-form">


        <h2>
            Update Category
        </h2>


        <form method="POST">


            <!-- CATEGORY NAME -->

            <input
                type="text"
                name="category_name"
                value="<?php

                    echo htmlspecialchars(
                        $category[
                            "category_name"
                        ]
                    );

                ?>"
                placeholder="Category Name"
                required
            >


            <!-- UPDATE -->

            <button
                type="submit"
                name="update_category"
            >

                💾 Update Category

            </button>


        </form>


        <a
            href="categories.php"
            class="menu-btn"
            style="
                display:inline-block;
                margin-top:15px;
            "
        >

            ⬅️ Back to Categories

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