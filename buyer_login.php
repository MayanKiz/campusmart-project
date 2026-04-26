<?php
session_start();
include("db.php");

if (isset($_POST['buyer_login'])) {

    $email = $_POST['email'];
    $password = $_POST['password'];

    $query = "SELECT * FROM buyer 
              WHERE email='$email' 
              AND password='$password'";

    $result = mysqli_query($conn, $query);

    if (mysqli_num_rows($result) > 0) {

        $row = mysqli_fetch_assoc($result);

        /* save buyer id in session */
        $_SESSION['buyer_id'] = $row['buyer_id'];

        /* redirect to buyer dashboard */
        header("Location: buyer_dashboard.php");
        exit();

    } else {
        echo "<script>alert('Invalid Email or Password');</script>";
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Buyer Login</title>
    <link rel="stylesheet"
    href="style.css">
</head>
<body>

<h1>Buyer Login</h1>

<form method="POST">

    <label>Email:</label><br>
    <input type="email" name="email" required><br><br>

    <label>Password:</label><br>
    <input type="password" name="password" required><br><br>

    <button type="submit" name="buyer_login">
        Login
    </button>

</form>

<br>

<a href="buyer_signup.php">
    <button type="button">Create New Account</button>
</a>

</body>
</html>