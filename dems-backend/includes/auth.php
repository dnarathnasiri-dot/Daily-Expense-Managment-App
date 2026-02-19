<?php
require_once _DIR_ . '/../config/database.php';
include "response.php";

$data = json_decode(file_get_contents("php://input"), true);

$email = $data['email'] ?? '';
$password = $data['password'] ?? '';

$query = "SELECT * FROM users WHERE email='$email'";
$result = mysqli_query($conn, $query);

if (mysqli_num_rows($result) > 0) {
    $user = mysqli_fetch_assoc($result);

    if ($user['password'] == $password) {
        jsonResponse(true, "Login successful", $user);
    } else {
        jsonResponse(false, "Invalid password");
    }
} else {
    jsonResponse(false, "User not found");
}
?>