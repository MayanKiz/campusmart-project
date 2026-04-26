<?php
session_start();
include("db.php");

/* Seller must be logged in */
if (!isset($_SESSION['seller_id'])) {
    die("Please login first");
}

$seller_id = $_SESSION['seller_id'];

/* Add Product */
if (isset($_POST['add_product'])) {

    $item_name = $_POST['item_name'];
    $category = $_POST['category'];
    $item_condition = $_POST['item_condition'];
    $price = $_POST['price'];
    $description = $_POST['description'];

    $status = "Available";

    $product_image = $_FILES['product_image']['name'];
    $temp_image = $_FILES['product_image']['tmp_name'];

    /* Image validation */
    $image_extension = strtolower(pathinfo($product_image, PATHINFO_EXTENSION));
    $allowed_extensions = array("jpg", "jpeg", "png");

    if (!in_array($image_extension, $allowed_extensions)) {
        die("Only JPG, JPEG and PNG files are allowed.");
    }

    move_uploaded_file($temp_image, "uploads/" . $product_image);

    $insert_query = "INSERT INTO products
    (seller_id, item_name, category, item_condition, price, description, status, products_image)
    VALUES
    ('$seller_id', '$item_name', '$category', '$item_condition', '$price', '$description', '$status', '$product_image')";

    $result = mysqli_query($conn, $insert_query);

    if ($result) {
        echo "<script>alert('Product Added Successfully');</script>";
        echo "<script>window.location='my_products.php';</script>";
        exit();
    } else {
        echo "Error: " . mysqli_error($conn);
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Add Product</title>
    <link rel="stylesheet"
    href="style.css">
</head>
<body>

<h1>Add Product</h1>

<form method="POST" enctype="multipart/form-data">

    <label>Item Name:</label><br>
    <input type="text" name="item_name" required><br><br>

    <label>Category:</label><br>
    <input type="text" name="category" required><br><br>

    <label>Item Condition:</label><br>
    <input type="text" name="item_condition" required><br><br>

    <label>Price:</label><br>
    <input type="number" name="price" required><br><br>

    <label>Description:</label><br>
    <textarea name="description" required></textarea><br><br>

    <label>Product Image:</label><br>
    <input 
        type="file" 
        name="product_image" 
        accept=".jpg,.jpeg,.png" 
        required
    ><br>

    <small>
        Only JPG, JPEG and PNG files are allowed.
    </small><br><br>

    <button type="submit" name="add_product">
        Add Product
    </button>

</form>

<br><br>

<a href="my_products.php">
    <button type="button">View My Products</button>
</a>

</body>
</html>