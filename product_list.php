<?php
session_start();
include("db.php");

/* Seller must be logged in */
if (!isset($_SESSION['seller_id'])) {
    die("Please login first");
}

$seller_id = $_SESSION['seller_id'];

/* Seller products + buyer details */
$query = "SELECT 
products.product_id,
products.item_name,
products.price,
products.description,
products.status,
products.products_image,
orders.order_id,
orders.delivery_option,
orders.delivery_details,
orders.order_status,
buyer.name
FROM products
LEFT JOIN orders ON products.product_id = orders.product_id
LEFT JOIN buyer ON orders.buyer_id = buyer.buyer_id
WHERE products.seller_id = '$seller_id'
ORDER BY products.product_id DESC";

$result = mysqli_query($conn, $query);
?>

<!DOCTYPE html>
<html>
<head>
    <title>My Products</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>

<h1>My Products</h1>

<?php
while ($row = mysqli_fetch_assoc($result)) {
?>

<hr>

<h2><?php echo $row['item_name']; ?></h2>

<img src="uploads/<?php echo $row['products_image']; ?>" 
width="250" height="250">

<p><strong>Price:</strong> ₹ <?php echo $row['price']; ?></p>

<p><strong>Status:</strong> <?php echo $row['status']; ?></p>

<p><strong>Description:</strong> <?php echo $row['description']; ?></p>

<?php
if (!empty($row['order_id'])) {
?>

<h3>Buyer Details</h3>

<p><strong>Name:</strong> <?php echo $row['name']; ?></p>

<p><strong>Delivery Option:</strong> <?php echo $row['delivery_option']; ?></p>

<p><strong>Delivery Details:</strong> <?php echo $row['delivery_details']; ?></p>

<p><strong>Order Status:</strong> <?php echo $row['order_status']; ?></p>

<?php
} else {
    echo "<p><strong>No Buyer Yet</strong></p>";
}
?>

<?php
}
?>

<br><br>

<a href="seller_dashboard.php">
    <button>Back to Dashboard</button>
</a>

</body>
</html>