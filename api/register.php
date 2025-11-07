
<?php
// api/register.php - User registration
require_once '../includes/config.php';

$apiController = new ApiController();
$apiController->handleRegister();
?>