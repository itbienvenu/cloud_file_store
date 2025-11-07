<?php
require_once '../includes/config.php';

if (!isset($_GET['code'])) {
    header('Location: ../');
    exit;
}

$shortCode = $_GET['code'];
$db = Database::getInstance();
$result = $db->query(
    "SELECT f.id, f.download_token 
     FROM files f 
     JOIN short_urls s ON f.id = s.file_id 
     WHERE s.short_code = ?",
    [$shortCode]
)->fetch();

if (!$result) {
    header('Location: ../');
    exit;
}

header('Location: ../download.php?token=' . $result['download_token']);