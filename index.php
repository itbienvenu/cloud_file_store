<?php
// index.php - Main entry point
session_start();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Secure File Sharing</title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
    <div class="container">
        <h1>Secure File Sharing</h1>
        
        <div id="auth-container">
            <?php if (!isset($_SESSION['user_id'])): ?>
                <div id="login-form">
                    <h2>Login</h2>
                    <div class="alert" id="login-alert" style="display: none;"></div>
                    <form id="login">
                        <div class="form-group">
                            <label for="login-username">Username</label>
                            <input type="text" id="login-username" required>
                        </div>
                        <div class="form-group">
                            <label for="login-password">Password</label>
                            <input type="password" id="login-password" required>
                        </div>
                        <button type="submit">Login</button>
                    </form>
                    <p>Don't have an account? <a href="#" id="show-register">Register</a></p>
                </div>
                
                <div id="register-form" style="display: none;">
                    <h2>Register</h2>
                    <div class="alert" id="register-alert" style="display: none;"></div>
                    <form id="register">
                        <div class="form-group">
                            <label for="reg-username">Username</label>
                            <input type="text" id="reg-username" required>
                        </div>
                        <div class="form-group">
                            <label for="reg-email">Email</label>
                            <input type="email" id="reg-email" required>
                        </div>
                        <div class="form-group">
                            <label for="reg-password">Password</label>
                            <input type="password" id="reg-password" required>
                        </div>
                        <button type="submit">Register</button>
                    </form>
                    <p>Already have an account? <a href="#" id="show-login">Login</a></p>
                </div>
            <?php else: ?>
                <div id="user-info">
                    <p>Welcome, <span id="username"><?php echo htmlspecialchars($_SESSION['username']); ?></span>!</p>
                    <a href="#" id="logout">Logout</a>
                </div>
            <?php endif; ?>
        </div>
        
        <div id="file-management" <?php if (!isset($_SESSION['user_id'])) echo 'style="display: none;"'; ?>>
            <div class="panel">
                <h2>Upload File</h2>
                <div class="alert" id="upload-alert" style="display: none;"></div>
                <form id="upload-form">
                    <div class="form-group">
                        <label for="file">Select File</label>
                        <input type="file" id="file" required>
                    </div>
                    <div class="form-group">
                        <label for="expire-hours">Link Expires In (hours)</label>
                        <select id="expire-hours">
                            <option value="1">1 hour</option>
                            <option value="6">6 hours</option>
                            <option value="24" selected>24 hours</option>
                            <option value="72">3 days</option>
                            <option value="168">7 days</option>
                        </select>
                    </div>
                    <button type="submit">Upload</button>
                </form>
            </div>
            
            <div class="panel">
                <h2>Your Files</h2>
                <div id="file-list">
                    <div class="loader"></div>
                    <p class="empty-message" style="display: none;">No files found.</p>
                    <table style="display: none;">
                        <thead>
                            <tr>
                                <th>File Name</th>
                                <th>Upload Date</th>
                                <th>Expires</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody></tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <script src="js/app.js"></script>
</body>
</html>


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
?>
