<?php
$host = "localhost";
$user = "root";
$password = "";
$dbname = "dems";

$conn = mysqli_connect($host, $user, $password, $dbname, 3306);

if (!$conn) {
    die("Database connection failed: " . mysqli_connect_error());
}
?>