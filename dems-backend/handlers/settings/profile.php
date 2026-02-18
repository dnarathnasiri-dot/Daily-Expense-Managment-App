<?php
include "config.php";
include "response.php";

$data = json_decode(file_get_contents("php://input"), true);

$user_id = $data['user_id'] ?? 0;
$name = $data['name'] ?? '';

$query = "UPDATE users 
          SET name='$name' 
          WHERE user_id='$user_id'";

if (mysqli_query($conn, $query)) {
    jsonResponse(true, "Profile updated successfully");
} else {
    jsonResponse(false, "Update failed");
}
?>
