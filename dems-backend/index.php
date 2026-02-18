<?php
include "cors.php";

$page = $_GET['page'] ?? '';

switch ($page) {

    case "auth":
        include "auth.php";
        break;

    case "dashboard":
        include "dashboard.php";
        break;

    case "summary":
        include "summary.php";
        break;

    case "account":
        include "account.php";
        break;

    case "profile":
        include "profile.php";
        break;

    case "password":
        include "password.php";
        break;

    case "notifications":
        include "notifications.php";
        break;

    case "export":
        include "export.php";
        break;

    default:
        echo json_encode([
            "status" => false,
            "message" => "Invalid API endpoint"
        ]);
}
?>
