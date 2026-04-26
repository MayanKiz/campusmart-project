<!-- seller_signup.php -->

<?php
include("db.php");

if(isset($_POST['signup']))
{
    $name = $_POST['name'];
    $email = $_POST['email'];
    $phone = $_POST['phone'];
    $password = $_POST['password'];

    $query = "INSERT INTO seller (name, email, phone, password)
              VALUES ('$name', '$email', '$phone', '$password')";

    if(mysqli_query($conn, $query))
    {
        echo "<script>
                alert('Registration Successful');
                window.location='seller_login.php';
              </script>";
    }
    else
    {
        echo "Error: " . mysqli_error($conn);
    }
}
?>

<h2>Seller Signup</h2>

<form method="POST">
    Name:<br>
    <input type="text" name="name" required><br><br>

    Email:<br>
    <input type="email" name="email" required><br><br>

    Phone:<br>
    <input type="text" name="phone" required><br><br>

    Password:<br>
    <input type="password" name="password" required><br><br>

    <button type="submit" name="signup">Signup</button>
</form>

<br><br>

<p>Already have account?</p>
<a href="seller_login.php">
    <button>Login Here</button>
</a>