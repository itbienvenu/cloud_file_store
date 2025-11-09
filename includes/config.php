<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
define('BASE_DIR', dirname(dirname(__FILE__))); // Points to project root
define('STORAGE_DIR', BASE_DIR . '/storage');  // Correct path to storage
define('ENCRYPTION_KEY', getenv('ENCRYPTION_KEY') ?: 'bienvenugashema');
define('DB_HOST', getenv('DB_HOST') ?: 'localhost');
define('DB_NAME', getenv('DB_NAME') ?: 'file');
define('DB_USER', getenv('DB_USER') ?: 'root');
define('DB_PASS', getenv('DB_PASS') ?: '');

// Initialize database connection
function getDbConnection() {
    static $db = null;
    if ($db === null) {
        try {
            $db = new PDO('mysql:host=127.0.0.1;dbname=' . DB_NAME . ';charset=utf8', DB_USER, DB_PASS);
            $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        } catch (PDOException $e) {
            die('Database connection failed: ' . $e->getMessage());
        }
    }
    return $db;
}


// Security utilities
class Security {
    // Generate a secure random token
    public static function generateToken($length = 32) {
        return bin2hex(random_bytes($length));
    }
    
    // Generate a secure file path that can't be traced or guessed
    public static function generateSecureFilePath() {
        $path = '';
        // Create multi-level directory structure to make path harder to guess
        for ($i = 0; $i < 3; $i++) {
            $path .= substr(self::generateToken(8), 0, 8) . '/';
        }
        return $path . self::generateToken(16);
    }
    
    // Encrypt file content
    public static function encryptFile($fileContent) {
        $iv = random_bytes(16); // Generate initialization vector
        $encrypted = openssl_encrypt(
            $fileContent,
            'AES-256-CBC',
            ENCRYPTION_KEY,
            0,
            $iv
        );
        
        // Prepend IV to encrypted content
        return base64_encode($iv . base64_decode($encrypted));
    }
    
    // Decrypt file content
    public static function decryptFile($encryptedContent) {
        $data = base64_decode($encryptedContent);
        $iv = substr($data, 0, 16);
        $encrypted = base64_encode(substr($data, 16));
        
        return openssl_decrypt(
            $encrypted,
            'AES-256-CBC',
            ENCRYPTION_KEY,
            0,
            $iv
        );
    }
}

// File manager class
class FileManager {
    private $db;
    
    public function __construct() {
        $this->db = getDbConnection();
        
        // Create storage directory if it doesn't exist
        if (!file_exists(STORAGE_DIR)) {
            mkdir(STORAGE_DIR, 777, true);
        }
    }
    
    // Upload a file with encryption
    public function uploadFile($fileData, $fileName, $userId, $expireHours = 24) {
        // Encrypt the file content
        $encryptedContent = Security::encryptFile($fileData);
        
        // Generate secure path
        $securePath = Security::generateSecureFilePath();
        $fullPath = STORAGE_DIR . '/' . $securePath;
        
        // Create directory structure
        $dirPath = dirname($fullPath);
        if (!file_exists($dirPath)) {
            mkdir($dirPath, 0770, true);
        }
        
        // Save the encrypted file
        file_put_contents($fullPath, $encryptedContent);
        
        // Generate download token
        $downloadToken = Security::generateToken();
        
        // Calculate expiration time
        $expireTime = date('Y-m-d H:i:s', strtotime("+{$expireHours} hours"));
        
        // Store file metadata in database
        $stmt = $this->db->prepare(
            "INSERT INTO files (file_name, secure_path, download_token, user_id, expire_time) 
             VALUES (?, ?, ?, ?, ?)"
        );
        $stmt->execute([$fileName, $securePath, $downloadToken, $userId, $expireTime]);
        
        return $downloadToken;
    }
    
    // Download a file using the token
    public function downloadFile($token) {
        $stmt = $this->db->prepare(
            "SELECT * FROM files WHERE download_token = ? AND expire_time > NOW()"
        );
        $stmt->execute([$token]);
        $file = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$file) {
            return false; // File not found or link expired
        }
        
        $fullPath = STORAGE_DIR . '/' . $file['secure_path'];
        
        if (!file_exists($fullPath)) {
            return false; // File doesn't exist on disk
        }
        
        // Read and decrypt file
        $encryptedContent = file_get_contents($fullPath);
        $decryptedContent = Security::decryptFile($encryptedContent);
        
        // Log access
        $this->logAccess($file['id'], $_SERVER['REMOTE_ADDR']);
        
        return [
            'content' => $decryptedContent,
            'name' => $file['file_name']
        ];
    }
    
    // Log file access for auditing
    private function logAccess($fileId, $ipAddress) {
        $stmt = $this->db->prepare(
            "INSERT INTO access_logs (file_id, ip_address, access_time) 
             VALUES (?, ?, NOW())"
        );
        $stmt->execute([$fileId, $ipAddress]);
    }
    
    // Get all files for a user
    public function getUserFiles($userId) {
        $stmt = $this->db->prepare(
            "SELECT id, file_name, download_token, created_at, expire_time 
             FROM files 
             WHERE user_id = ? 
             ORDER BY created_at DESC"
        );
        $stmt->execute([$userId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    // Delete a file
    public function deleteFile($fileId, $userId) {
        // First, get the file info
        $stmt = $this->db->prepare(
            "SELECT secure_path FROM files WHERE id = ? AND user_id = ?"
        );
        $stmt->execute([$fileId, $userId]);
        $file = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$file) {
            return false;
        }
        
        // Delete the physical file
        $fullPath = STORAGE_DIR . '/' . $file['secure_path'];
        if (file_exists($fullPath)) {
            unlink($fullPath);
        }
        
        // Delete from database
        $stmt = $this->db->prepare(
            "DELETE FROM files WHERE id = ? AND user_id = ?"
        );
        $stmt->execute([$fileId, $userId]);
        
        return true;
    }
}

// User authentication class
class Auth {
    private $db;
    
    public function __construct() {
        $this->db = getDbConnection();
    }
    
    // Register a new user
    public function register($username, $email, $password) {
        // Check if username or email already exists
        $stmt = $this->db->prepare(
            "SELECT id FROM users WHERE username = ? OR email = ?"
        );
        $stmt->execute([$username, $email]);
        
        if ($stmt->rowCount() > 0) {
            return false; // Username or email already exists
        }
        
        // Hash password
        $passwordHash = password_hash($password, PASSWORD_DEFAULT);
        
        // Insert new user
        $stmt = $this->db->prepare(
            "INSERT INTO users (username, email, password_hash) 
             VALUES (?, ?, ?)"
        );
        $stmt->execute([$username, $email, $passwordHash]);
        
        return $this->db->lastInsertId();
    }
    
    // Login user
    public function login($username, $password) {
        $stmt = $this->db->prepare(
            "SELECT id, username, password_hash 
             FROM users 
             WHERE username = ?"
        );
        $stmt->execute([$username]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$user || !password_verify($password, $user['password_hash'])) {
            return false;
        }
        
        // Generate session token
        $sessionToken = Security::generateToken();
        
        // Store session
        $stmt = $this->db->prepare(
            "INSERT INTO user_sessions (user_id, token, expires_at) 
             VALUES (?, ?, DATE_ADD(NOW(), INTERVAL 7 DAY))"
        );
        $stmt->execute([$user['id'], $sessionToken]);
        
        return [
            'user_id' => $user['id'],
            'username' => $user['username'],
            'token' => $sessionToken
        ];
    }
    
    // Validate session token
    public function validateToken($token) {
        $stmt = $this->db->prepare(
            "SELECT user_id FROM user_sessions 
             WHERE token = ? AND expires_at > NOW()"
        );
        $stmt->execute([$token]);
        $session = $stmt->fetch(PDO::FETCH_ASSOC);
        
        return $session ? $session['user_id'] : false;
    }
    
    // Logout (invalidate token)
    public function logout($token) {
        $stmt = $this->db->prepare("DELETE FROM user_sessions WHERE token = ?");
        $stmt->execute([$token]);
    }
}

// API controller for handling file operations
class ApiController {
    private $fileManager;
    private $auth;
    
    public function __construct() {
        $this->fileManager = new FileManager();
        $this->auth = new Auth();
    }
    
    // Handle upload request
    public function handleUpload() {
        try{
        // Verify authentication
            $userId = $this->authenticate();
            if (!$userId) {
                return $this->jsonResponse(['error' => 'Unauthorized'], 401);
            }
            
            // Check if file was uploaded
            if (!isset($_FILES['file']) || $_FILES['file']['error'] !== UPLOAD_ERR_OK) {
                return $this->jsonResponse(['error' => 'No file uploaded or upload error'], 400);
            }
            
            $file = $_FILES['file'];
            $fileContent = file_get_contents($file['tmp_name']);
            $fileName = basename($file['name']);
            
            // Get expiration time (default 24 hours)
            $expireHours = isset($_POST['expire_hours']) ? (int)$_POST['expire_hours'] : 24;
            
            // Upload file
            $downloadToken = $this->fileManager->uploadFile($fileContent, $fileName, $userId, $expireHours);
            
            // Generate download URL
            $downloadUrl = $this->getBaseUrl() . '/php_file_store/file/download.php?token=' . $downloadToken;
            
            return $this->jsonResponse([
                'success' => true,
                'download_url' => $downloadUrl,
                'token' => $downloadToken,
                'expires_in' => $expireHours . ' hours'
            ]);
        }
        catch (Exception $e) {
            return $this->jsonResponse(['error' => 'An error occurred: ' . $e->getMessage()], 500);
        }
    }
    
    // Handle file download
    public function handleDownload($token) {
        $file = $this->fileManager->downloadFile($token);
        
        if (!$file) {
            header('HTTP/1.1 404 Not Found');
            echo 'File not found or link expired';
            exit;
        }
        
        // Set headers for download
        header('Content-Type: application/octet-stream');
        header('Content-Disposition: attachment; filename="' . $file['name'] . '"');
        header('Content-Length: ' . strlen($file['content']));
        header('Cache-Control: no-cache, no-store, must-revalidate');
        
        // Output file content
        echo $file['content'];
        exit;
    }
    
    // Handle file listing for a user
    public function handleListFiles() {
        $userId = $this->authenticate();
        if (!$userId) {
            return $this->jsonResponse(['error' => 'Unauthorized'], 401);
        }
        
        $files = $this->fileManager->getUserFiles($userId);
        
        // Add download URLs to each file
        foreach ($files as &$file) {
            $file['download_url'] = $this->getBaseUrl() . '/php_file_store/file/download.php?token=' . $file['download_token'];
        }
        
        return $this->jsonResponse(['files' => $files]);
    }
    
    // Handle file deletion
    public function handleDeleteFile() {
        $userId = $this->authenticate();
        if (!$userId) {
            return $this->jsonResponse(['error' => 'Unauthorized'], 401);
        }
        
        if (!isset($_POST['file_id'])) {
            return $this->jsonResponse(['error' => 'File ID is required'], 400);
        }
        
        $fileId = (int)$_POST['file_id'];
        $success = $this->fileManager->deleteFile($fileId, $userId);
        
        if (!$success) {
            return $this->jsonResponse(['error' => 'File not found or not authorized'], 404);
        }
        
        return $this->jsonResponse(['success' => true]);
    }
    
    // Handle user registration
    public function handleRegister() {
        // Check required fields
        if (!isset($_POST['username']) || !isset($_POST['email']) || !isset($_POST['password'])) {
            return $this->jsonResponse(['error' => 'Missing required fields'], 400);
        }
        
        $username = $_POST['username'];
        $email = $_POST['email'];
        $password = $_POST['password'];
        
        // Validate input
        if (strlen($username) < 3 || strlen($password) < 8) {
            return $this->jsonResponse([
                'error' => 'Username must be at least 3 characters and password at least 8 characters'
            ], 400);
        }
        
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            return $this->jsonResponse(['error' => 'Invalid email format'], 400);
        }
        
        // Register user
        $userId = $this->auth->register($username, $email, $password);
        
        if (!$userId) {
            return $this->jsonResponse(['error' => 'Username or email already exists'], 409);
        }
        
        return $this->jsonResponse([
            'success' => true,
            'user_id' => $userId
        ]);
    }
    
    // Handle user login
public function handleLogin() {
    if (!isset($_POST['username']) || !isset($_POST['password'])) {
        $this->jsonResponse(['error' => 'Missing username or password'], 400);
    }

    $username = $_POST['username'];
    $password = $_POST['password'];

    $session = $this->auth->login($username, $password);

    if (!$session) {
        $this->jsonResponse(['error' => 'Invalid username or password'], 401);
    }

    $this->jsonResponse([
        'success' => true,
        'user_id' => $session['user_id'],
        'username' => $session['username'],
        'token' => $session['token']
    ]);
}


private function jsonResponse(array $data, int $statusCode = 200) {
    http_response_code($statusCode);
    header('Content-Type: application/json');
    echo json_encode($data);
    exit;
}

    
    // Handle user logout
    public function handleLogout() {
        $token = $this->getAuthToken();
        
        if ($token) {
            $this->auth->logout($token);
        }
        
        return $this->jsonResponse(['success' => true]);
    }
    
    // Helper function to authenticate requests
    private function authenticate() {
        $token = $this->getAuthToken();
        
        if (!$token) {
            return false;
        }
        
        return $this->auth->validateToken($token);
    }
    
    // Helper function to get auth token from request
    private function getAuthToken() {
        // Check Authorization header
        $headers = getallheaders();
        if (isset($headers['Authorization'])) {
            if (preg_match('/Bearer\s+(.*)$/i', $headers['Authorization'], $matches)) {
                return $matches[1];
            }
        }
        
        // Check query parameter
        if (isset($_GET['token'])) {
            return $_GET['token'];
        }
        
        // Check POST parameter
        if (isset($_POST['token'])) {
            return $_POST['token'];
        }
        
        return null;
    }
    
   
    // Helper function to get base URL
    private function getBaseUrl() {
        $protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http';
        return $protocol . '://' . $_SERVER['HTTP_HOST'];
    }
}

class UrlShortener {
    private $db;
    
    public function __construct($db) {
        $this->db = $db;
    }
    
    public function createShortUrl($fileId) {
        // Generate unique short code
        do {
            $shortCode = $this->generateShortCode();
            $exists = $this->db->query("SELECT id FROM short_urls WHERE short_code = ?", [$shortCode])->fetch();
        } while ($exists);
        
        // Save short URL
        $this->db->query(
            "INSERT INTO short_urls (file_id, short_code) VALUES (?, ?)",
            [$fileId, $shortCode]
        );
        
        return $shortCode;
    }
    
    private function generateShortCode($length = 6) {
        $chars = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $code = '';
        for ($i = 0; $i < $length; $i++) {
            $code .= $chars[rand(0, strlen($chars) - 1)];
        }
        return $code;
    }
    
    public function getShortUrl($fileId) {
        $result = $this->db->query(
            "SELECT short_code FROM short_urls WHERE file_id = ?",
            [$fileId]
        )->fetch();
        
        if (!$result) {
            $shortCode = $this->createShortUrl($fileId);
        } else {
            $shortCode = $result['short_code'];
        }
        
        return $shortCode;
    }
}