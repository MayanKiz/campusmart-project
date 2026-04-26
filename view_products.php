<!-- view_products.php -->

<?php
$conn = mysqli_connect("localhost", "root", "", "campusmart_project");

if (!$conn) {
    die("Connection failed: " . mysqli_connect_error());
}

if (!isset($_GET['product_id'])) {
    die("Product ID not found");
}

$product_id = $_GET['product_id'];

$sql = "SELECT products.*, seller.name, seller.phone
        FROM products
        JOIN seller ON products.seller_id = seller.seller_id
        WHERE products.product_id = '$product_id'";

$result = mysqli_query($conn, $sql);
$row = mysqli_fetch_assoc($result);
?>

<!DOCTYPE html>
<html>
<head>
    <title>Product Details</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>

<h1>Product Details</h1>

<p><strong>Item Name:</strong> <?php echo $row['item_name']; ?></p>

<p><strong>Category:</strong> <?php echo $row['category']; ?></p>

<p><strong>Condition:</strong> <?php echo $row['item_condition']; ?></p>

<img src="uploads/<?php echo $row['products_image']; ?>"
width="250"
height="250"><br><br>

<p><strong>Price:</strong> ₹ <?php echo $row['price']; ?></p>

<p><strong>Description:</strong> <?php echo $row['description']; ?></p>

<hr>

<h2>Seller Details</h2>

<p><strong>Seller Name:</strong> <?php echo $row['name']; ?></p>

<p><strong>Contact Number:</strong> <?php echo $row['phone']; ?></p>

<a href="buy_now.php?product_id=<?php echo $row['product_id']; ?>">
    <button>Buy Now</button>
</a>

<br><br>

<a href="product_list.php">
    <button>Back to Products</button>
</a>

</body>
</html>