<?php
require_once "config/db.php";

$message = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    $name = $_POST["name"];
    $email = $_POST["email"];
    $phone = $_POST["phone"];
    $password = $_POST["password"];

    $password = password_hash($password, PASSWORD_DEFAULT);

    $sql = "INSERT INTO users (name, email, phone, password)
            VALUES (?, ?, ?, ?)";

    $stmt = $conn->prepare($sql);

    $stmt->bind_param(
        "ssss",
        $name,
        $email,
        $phone,
        $password
    );

    if ($stmt->execute()) {
        $message = "Registration successful!";
    } else {
        $message = "Email already exists!";
    }

    $stmt->close();
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Register - Foodie</title>

    <link rel="stylesheet" href="css/style.css">
</head>

<body>

<div class="register-container">

    <div class="register-box">

        <h1>🍔 Foodie</h1>

        <h2>Create Account</h2>

        <?php
        if ($message != "") {
            echo "<p class='message'>$message</p>";
        }
        ?>

        <form method="POST">

            <label>Name</label>

            <input
                type="text"
                name="name"
                placeholder="Enter your name"
                required
            >

            <label>Email</label>

            <input
                type="email"
                name="email"
                placeholder="Enter your email"
                required
            >

            <label>Phone</label>

            <input
                type="text"
                name="phone"
                placeholder="Enter your phone number"
                required
            >

            <label>Password</label>

            <input
                type="password"
                name="password"
                placeholder="Enter your password"
                required
            >

            <button type="submit">
                Register
            </button>

        </form>

        <p>
            Already have an account?
            <a href="login.php">Login</a>
        </p>

        <a href="index.php">
            ← Back to Home
        </a>

    </div>

</div>

</body>
</html>