
<?php
// download.php - Handle file downloads
require_once 'includes/config.php';

// Check if token is provided
if (!isset($_GET['token']) || empty($_GET['token'])) {
    http_response_code(400);
    echo json_encode(['error' => 'Token is required']);
    exit;
}

$token = $_GET['token'];
$apiController = new ApiController();
$apiController->handleDownload($token);
