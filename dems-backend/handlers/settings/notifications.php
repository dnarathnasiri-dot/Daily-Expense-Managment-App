<?php
include "response.php";

$notifications = [
    ["message" => "You exceeded your monthly budget"],
    ["message" => "New expense category added"]
];

jsonResponse(true, "Notifications", $notifications);
?>
