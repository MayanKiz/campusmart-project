<?php
session_start();
session_destroy();

header("Location: buyer_login.php");
exit();
?>