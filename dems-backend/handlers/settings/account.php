<?php
include "config.php";
include "response.php";

$user_id = $_GET['user_id'] ?? 0;

$query = "SELECT name, email 
          FROM users 
          WHERE user_id='$user_id'";

$result = mysqli_query($conn, $query);
$user = mysqli_fetch_assoc($result);

jsonResponse(true, "Account Info", $user);
?>
