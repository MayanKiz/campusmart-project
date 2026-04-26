<?php
session_start();
include("db.php");

if (isset($_POST['login'])) {

    $email = $_POST['email'];
    $password = $_POST['password'];

    $query = "SELECT * FROM seller 
              WHERE email='$email' 
              AND password='$password'";

    $result = mysqli_query($conn, $query);

    if (mysqli_num_rows($result) > 0) {

        $row = mysqli_fetch_assoc($result);

        $_SESSION['seller_id'] = $row['seller_id'];

        echo "<script>
                alert('Login Successful');
                window.location='add_product.php';
              </script>";

    } else {

        echo "<script>
                alert('Invalid Email or Password');
              </script>";
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Seller Login</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>

<h2>Seller Login</h2>

<form method="POST">

    Email:<br>
    <input type="email" name="email" required>
    <br><br>

    Password:<br>
    <input type="password" name="password" required>
    <br><br>

    <button type="submit" name="login">
        Login
    </button>

</form>

<br><br>

<p>New Seller?</p>

<a href="seller_signup.php">
    <button type="button">
        Signup Here
    </button>
</a>

</body>
</html>