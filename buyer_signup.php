<!-- buyer_signup.php -->

<?php
include("db.php");

if (isset($_POST['signup'])) {

    $name = $_POST['name'];
    $email = $_POST['email'];
    $phone = $_POST['phone'];
    $password = $_POST['password'];

    $query = "INSERT INTO buyer (name, email, phone, password)
              VALUES ('$name', '$email', '$phone', '$password')";

    $result = mysqli_query($conn, $query);

    if ($result) {

        echo "<script>
                alert('Registration Successful');
                window.location='buyer_login.php';
              </script>";

    } else {
        echo "Error: " . mysqli_error($conn);
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Buyer Signup</title>
    <link rel="stylesheet"
    href="style.css">
</head>
<body>

<h2>Buyer Signup</h2>

<form method="POST">

    <label>Name:</label><br>
    <input type="text" name="name" required><br><br>

    <label>Email:</label><br>
    <input type="email" name="email" required><br><br>

    <label>Phone:</label><br>
    <input type="text" name="phone" required><br><br>

    <label>Password:</label><br>
    <input type="password" name="password" required><br><br>

    <button type="submit" name="signup">Signup</button>

</form>

<br><br>

<p>Already have an account?</p>
<a href="buyer_login.php">
    <button>Login Here</button>
</a>

</body>
</html>