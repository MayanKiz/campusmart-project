<?php

$conn = mysqli_connect("localhost", "root", "", "campusmart_project");

if (!$conn) {
    die("Connection failed:". mysqli_connect_error() );
}

?>