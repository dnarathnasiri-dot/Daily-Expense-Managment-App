<?php
include "config.php";
include "response.php";

$user_id = $_GET['user_id'] ?? 0;

$query = "SELECT MONTH(expense_date) as month,
          SUM(amount) as total
          FROM expenses
          WHERE user_id='$user_id'
          GROUP BY MONTH(expense_date)";

$result = mysqli_query($conn, $query);

$data = [];
while ($row = mysqli_fetch_assoc($result)) {
    $data[] = $row;
}

jsonResponse(true, "Summary Data", $data);
?>
