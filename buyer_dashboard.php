<?php
session_start();
include("db.php");

if (!isset($_SESSION['buyer_id'])) {
    die("Please login first");
}

$buyer_id = $_SESSION['buyer_id'];

/* Buyer confirms received */
if (isset($_GET['complete_order'])) {
    $order_id = $_GET['complete_order'];

    $update_query = "UPDATE orders 
                     SET order_status='Completed' 
                     WHERE order_id='$order_id' 
                     AND buyer_id='$buyer_id'";

    mysqli_query($conn, $update_query);

    header("Location: buyer_dashboard.php");
    exit();
}

/* Fetch products + seller details */
$query = "SELECT products.*, seller.name, seller.phone
          FROM products
          JOIN seller 
          ON products.seller_id = seller.seller_id";

$result = mysqli_query($conn, $query);
?>

<!DOCTYPE html>
<html>
<head>
    <title>Buyer Dashboard</title>
    <link rel="stylesheet"
    href="style.css">
</head>
<body>

<h1>Available Products</h1>

<?php
if (mysqli_num_rows($result) > 0) {

    while ($row = mysqli_fetch_assoc($result)) {
?>

        <h2><?php echo $row['item_name']; ?></h2>

        <p><strong>Category:</strong> <?php echo $row['category']; ?></p>

        <p><strong>Condition:</strong> <?php echo $row['item_condition']; ?></p>

        <p><strong>Price:</strong> ₹ <?php echo $row['price']; ?></p>

        <p><strong>Description:</strong> <?php echo $row['description']; ?></p>

        <img src="uploads/<?php echo $row['products_image']; ?>"
             width="250" height="250"><br><br>

        <p><strong>Seller Name:</strong>
            <?php echo $row['name']; ?>
        </p>

        <p><strong>Seller Contact:</strong>
            <?php echo $row['phone']; ?>
        </p>

        <p><strong>Status:</strong> <?php echo $row['status']; ?></p>

<?php
        /* Check if buyer already ordered this product */
        $product_id = $row['product_id'];

        $order_check = "SELECT * FROM orders
                        WHERE product_id='$product_id'
                        AND buyer_id='$buyer_id'";

        $order_result = mysqli_query($conn, $order_check);

        if (mysqli_num_rows($order_result) > 0) {

            $order = mysqli_fetch_assoc($order_result);

            echo "<p><strong>Order Status:</strong> " . $order['order_status'] . "</p>";

            /* Buyer confirmation after seller delivered */
            if ($order['order_status'] == "Delivered") {
?>

                <p><strong>Buyer:</strong> If product received, click below</p>

                <a href="buyer_dashboard.php?complete_order=<?php echo $order['order_id']; ?>">
                    <button type="button">
                        Confirm Received
                    </button>
                </a>

<?php
            }

        } else {

            /* Product available for purchase */
            if ($row['status'] == "Available") {
?>

                <a href="buy_now.php?product_id=<?php echo $row['product_id']; ?>">
                    <button type="button">Buy Now</button>
                </a>

<?php
            } else {
?>

                <button type="button" disabled>Sold</button>

<?php
            }
        }
?>

        <hr>

<?php
    }

} else {
    echo "<h3>No Products Available</h3>";
}
?>

</body>
</html>