<?php
include("db.php");

if (isset($_GET['order_id'])) {

    $order_id = $_GET['order_id'];

    $update_query = "UPDATE orders 
                     SET order_status = 'Delivered'
                     WHERE order_id = '$order_id'";

    mysqli_query($conn, $update_query);

    header("Location: my_products.php");
    exit();
}
?>