<?php
// api/logout.php - User logout
require_once '../includes/config.php';

$apiController = new ApiController();
$apiController->handleLogout();
?>