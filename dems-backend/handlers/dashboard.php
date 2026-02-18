<?php
include "config.php";
include "response.php";

$user_id = $_GET['user_id'] ?? 0;

$query = "SELECT SUM(amount) as total_expense 
          FROM expenses 
          WHERE user_id='$user_id'";

$result = mysqli_query($conn, $query);
$data = mysqli_fetch_assoc($result);

jsonResponse(true, "Dashboard Data", $data);
?>
