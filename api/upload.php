<?php
// api/upload.php

// Prevent any output before headers
if (ob_get_level()) ob_end_clean();

// Set JSON header first
header('Content-Type: application/json');

try {
    // Use absolute path to prevent include issues
    require_once __DIR__ . '/../includes/config.php';
    
    // Verify the request method
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        throw new Exception('Only POST requests are allowed', 405);
    }
    
    // Check for file upload
    if (empty($_FILES['file'])) {
        throw new Exception('No file was uploaded', 400);
    }
    
    $apiController = new ApiController();
    $apiController->handleUpload();
    
} catch (Exception $e) {
    // Handle errors consistently
    http_response_code($e->getCode() ?: 500);
    echo json_encode([
        'error' => true,
        'message' => $e->getMessage()
    ]);
    exit;
}