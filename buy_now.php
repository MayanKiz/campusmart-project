<?php
session_start();
include("db.php");

/* Buyer must be logged in */
if (!isset($_SESSION['buyer_id'])) {
    die("Please login first");
}

$buyer_id = $_SESSION['buyer_id'];

/* Product ID check */
if (!isset($_GET['product_id'])) {
    die("Product ID not found");
}

$product_id = $_GET['product_id'];

/* Get product details */
$query = "SELECT * FROM products WHERE product_id = '$product_id'";
$result = mysqli_query($conn, $query);

if (mysqli_num_rows($result) == 0) {
    die("Product not found");
}

$product = mysqli_fetch_assoc($result);

/* Confirm Purchase */
if (isset($_POST['confirm_purchase'])) {

    $delivery_option = $_POST['delivery_option'];
    $details = "";

    if ($delivery_option == "Campus Delivery") {

        $hostel_department = $_POST['hostel_department'];
        $room_number = $_POST['room_number'];
        $contact_number = $_POST['contact_number'];

        $details = "Hostel/Department: $hostel_department,
        Room Number: $room_number,
        Contact: $contact_number";

    } 
    elseif ($delivery_option == "Self Pickup") {

        $pickup_location = $_POST['pickup_location'];
        $pickup_time = $_POST['pickup_time'];

        $details = "Pickup Location: $pickup_location,
        Pickup Time: $pickup_time";
    }

    /* Save order */
    $insert_order = "INSERT INTO orders
    (product_id, buyer_id, delivery_option, delivery_details, payment_method, order_status)
    VALUES
    ('$product_id', '$buyer_id', '$delivery_option', '$details', 'Cash on Delivery', 'Pending')";

    mysqli_query($conn, $insert_order);

    /* Update product status */
    $update_product = "UPDATE products
    SET status='Sold'
    WHERE product_id='$product_id'";

    mysqli_query($conn, $update_product);

    echo "<script>
    alert('Purchase Confirmed Successfully');
    window.location.href='buy_now.php?product_id=$product_id';
    </script>";
    exit();
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Buy Now</title>
    <link rel="stylesheet"
    href="style.css">
</head>
<body>

<h1><?php echo $product['item_name']; ?></h1>

<img src="uploads/<?php echo $product['products_image']; ?>"
     width="250" height="250">

<p><strong>Price:</strong> ₹ <?php echo $product['price']; ?></p>

<?php
if ($product['status'] == 'Sold') {
    echo "<h2 style='color:red;'>SOLD</h2>";
}
?>

<h2>Select Delivery Option</h2>

<form method="POST">

    <select 
        name="delivery_option"
        id="delivery_option"
        onchange="toggleDeliveryFields()"
        required
    >
        <option value="">Select Delivery Option</option>
        <option value="Campus Delivery">Campus Delivery</option>
        <option value="Self Pickup">Self Pickup</option>
    </select>

    <br><br>

    <!-- Campus Delivery -->
    <div id="campus_delivery" style="display:none;">

        <h2>Campus Delivery Details</h2>
        <p>Delivery available only inside college campus.</p>

        <label>Hostel / Department Name:</label><br>
        <input type="text" name="hostel_department"><br><br>

        <label>Block / Room Number:</label><br>
        <input type="text" name="room_number"><br><br>

        <label>Contact Number:</label><br>
        <input type="text" name="contact_number"><br><br>

    </div>

    <!-- Self Pickup -->
    <div id="self_pickup" style="display:none;">

        <h2>Self Pickup Details</h2>
        <p>Buyer must collect the product personally from seller.</p>

        <label>Preferred Pickup Location:</label><br>
        <input type="text" name="pickup_location"><br><br>

        <label>Preferred Pickup Time:</label><br>
        <input type="text" name="pickup_time"><br><br>

    </div>

    <h2>Payment Method</h2>
    <p>Cash on Delivery</p>

    <button type="submit" name="confirm_purchase">
        Confirm Purchase
    </button>

</form>

<br>

<a href="product_list.php">
    <button>Back to Products</button>
</a>

<script>
function toggleDeliveryFields() {
    let option = document.getElementById("delivery_option").value;

    document.getElementById("campus_delivery").style.display = "none";
    document.getElementById("self_pickup").style.display = "none";

    if (option === "Campus Delivery") {
        document.getElementById("campus_delivery").style.display = "block";
    }

    if (option === "Self Pickup") {
        document.getElementById("self_pickup").style.display = "block";
    }
}
</script>

</body>
</html>