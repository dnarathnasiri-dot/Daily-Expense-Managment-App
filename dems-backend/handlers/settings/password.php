<?php
include "config.php";
include "response.php";

$data = json_decode(file_get_contents("php://input"), true);

$user_id = $data['user_id'] ?? 0;
$new_password = $data['password'] ?? '';

$query = "UPDATE users 
          SET password='$new_password' 
          WHERE user_id='$user_id'";

if (mysqli_query($conn, $query)) {
    jsonResponse(true, "Password changed successfully");
} else {
    jsonResponse(false, "Password change failed");
}
?>
