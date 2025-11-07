<?php
// api/login.php - User login
require_once '../includes/config.php';

// In api/login.php
require_once '../includes/config.php';

try {
    $apiController = new ApiController();
    $apiController->handleLogin();
} catch (Exception $e) {
    // Log the error
    error_log('Login error: ' . $e->getMessage());
    // Return error response
    http_response_code(500);
    echo json_encode(['error' => 'Server error: ' . $e->getMessage()]);
}

?>