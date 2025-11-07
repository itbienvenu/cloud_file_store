<?php
// api/test.php
header('Content-Type: application/json');
echo json_encode(['status' => 'success', 'message' => 'Test successful']);
exit;