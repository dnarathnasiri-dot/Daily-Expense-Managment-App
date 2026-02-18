<?php
include "config.php";

$user_id = $_GET['user_id'] ?? 0;

header('Content-Type: text/csv');
header('Content-Disposition: attachment; filename="expenses.csv"');

$output = fopen("php://output", "w");

fputcsv($output, ["Amount", "Date", "Description"]);

$query = "SELECT amount, expense_date, description 
          FROM expenses 
          WHERE user_id='$user_id'";

$result = mysqli_query($conn, $query);

while ($row = mysqli_fetch_assoc($result)) {
    fputcsv($output, $row);
}

fclose($output);
exit;
?>
