<?php
// api/login.php - User login
require_once '../includes/config.php';
header('Content-Type: application/json'); // Important

try {
    $apiController = new ApiController();
    $apiController->handleLogin(); // must echo only JSON
} catch (Exception $e) {
    error_log('Login error: ' . $e->getMessage());
    http_response_code(500);
    echo json_encode(['error' => 'Server error']);
}
