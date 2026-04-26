<?php
session_start();
include("db.php");

/* Seller must be logged in */
if (!isset($_SESSION['seller_id'])) {
    die("Please login first");
}

$seller_id = $_SESSION['seller_id'];

/* Get seller products */
$select_query = "SELECT * FROM products WHERE seller_id = '$seller_id'";
$result = mysqli_query($conn, $select_query);
?>

<!DOCTYPE html>
<html>
<head>
    <title>My Products</title>
    <linl rel="stylesheet"
    href="style.css">
</head>
<body>

<h1>My Products</h1>

<a href="add_product.php">
    <button type="button">Add New Product</button>
</a>

<br><br>

<?php
if (mysqli_num_rows($result) > 0) {

    while ($row = mysqli_fetch_assoc($result)) {
?>

        <h2><?php echo $row['item_name']; ?></h2>

        <p><strong>Category:</strong>
            <?php echo $row['category']; ?>
        </p>

        <p><strong>Condition:</strong>
            <?php echo $row['item_condition']; ?>
        </p>

        <p><strong>Price:</strong> ₹
            <?php echo $row['price']; ?>
        </p>

        <p><strong>Description:</strong>
            <?php echo $row['description']; ?>
        </p>

        <img src="uploads/<?php echo $row['products_image']; ?>"
             width="250" height="250"><br><br>

        <p><strong>Status:</strong>
            <?php echo $row['status']; ?>
        </p>

<?php
        /* If product is sold, show buyer details */
        if ($row['status'] == "Sold") {

            $product_id = $row['product_id'];

            $order_query = "SELECT * FROM orders 
                            WHERE product_id = '$product_id'";

            $order_result = mysqli_query($conn, $order_query);

            if (mysqli_num_rows($order_result) > 0) {

                $order = mysqli_fetch_assoc($order_result);
?>

                <h3>Buyer Details</h3>

                <p><strong>Buyer ID:</strong>
                    <?php echo $order['buyer_id']; ?>
                </p>

                <p><strong>Delivery Option:</strong>
                    <?php echo $order['delivery_option']; ?>
                </p>

                <p><strong>Delivery Details:</strong>
                    <?php echo $order['delivery_details']; ?>
                </p>

                <p><strong>Payment Method:</strong>
                    <?php echo $order['payment_method']; ?>
                </p>

                <p><strong>Order Status:</strong>
                    <?php echo $order['order_status']; ?>
                </p>

<?php if ($order['order_status'] == "Pending") { ?>

    <a href="seller_delivered.php?order_id=<?php echo $order['order_id']; ?>">
        <button type="button">
            Mark as Delivered
        </button>
    </a>

<?php } elseif ($order['order_status'] == "Delivered") { ?>

    <button type="button" disabled>
        Waiting for Buyer Confirmation
    </button>

<?php } elseif ($order['order_status'] == "Completed") { ?>

    <button type="button" disabled>
        Completed
    </button>

<?php } ?>

<?php
            }
        }
?>

        <hr>

<?php
    }

} else {
    echo "<h3>No products added yet.</h3>";
}
?>

</body>
</html>