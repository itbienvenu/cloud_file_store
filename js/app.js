// app.js - Frontend JavaScript

document.addEventListener('DOMContentLoaded', function() {
    // DOM Elements
    const authContainer = document.getElementById('auth-container');
    const fileManagement = document.getElementById('file-management');
    const loginForm = document.getElementById('login-form');
    const registerForm = document.getElementById('register-form');
    const showLoginLink = document.getElementById('show-login');
    const showRegisterLink = document.getElementById('show-register');
    const loginAlert = document.getElementById('login-alert');
    const registerAlert = document.getElementById('register-alert');
    const uploadAlert = document.getElementById('upload-alert');
    const fileListContainer = document.getElementById('file-list');
    const fileTable = fileListContainer.querySelector('table');
    const fileTableBody = fileTable ? fileTable.querySelector('tbody') : null;
    const emptyMessage = fileListContainer.querySelector('.empty-message');
    const loader = fileListContainer.querySelector('.loader');

    // Session token management
    let sessionToken = localStorage.getItem('session_token');

    // Check if user is logged in
    function checkAuth() {
        if (sessionToken) {
            // User is logged in
            if (authContainer) {
                authContainer.innerHTML = `
                    <div id="user-info">
                        <p>Welcome, <span id="username">${localStorage.getItem('username') || 'User'}</span>!</p>
                        <a href="#" id="logout">Logout</a>
                    </div>
                `;
                document.getElementById('logout').addEventListener('click', logout);
            }
            
            if (fileManagement) {
                fileManagement.style.display = 'block';
                loadUserFiles();
            }
        } else {
            // User is not logged in
            if (authContainer && !loginForm) {
                authContainer.innerHTML = `
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
                `;
                
                // Re-attach event listeners
                document.getElementById('login').addEventListener('submit', handleLogin);
                document.getElementById('register').addEventListener('submit', handleRegister);
                document.getElementById('show-login').addEventListener('click', toggleAuthForms);
                document.getElementById('show-register').addEventListener('click', toggleAuthForms);
            }
            
            if (fileManagement) {
                fileManagement.style.display = 'none';
            }
        }
    }

    // Toggle between login and register forms
    function toggleAuthForms(e) {
        e.preventDefault();
        
        if (this.id === 'show-register') {
            loginForm.style.display = 'none';
            registerForm.style.display = 'block';
        } else {
            registerForm.style.display = 'none';
            loginForm.style.display = 'block';
        }
    }

    // Handle login form submission
    function handleLogin(e) {
        e.preventDefault();
        
        const username = document.getElementById('login-username').value;
        const password = document.getElementById('login-password').value;
        
        // Create form data
        const formData = new FormData();
        formData.append('username', username);
        formData.append('password', password);
        
        // Send login request
        fetch('api/login.php', {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                // Store session info
                localStorage.setItem('session_token', data.token);
                localStorage.setItem('user_id', data.user_id);
                localStorage.setItem('username', data.username);
                sessionToken = data.token;
                
                // Update UI
                loginAlert.style.display = 'none';
                checkAuth();
            } else {
                // Show error
                loginAlert.textContent = data.error || 'Login failed';
                loginAlert.style.display = 'block';
            }
        })
        .catch(error => {
            loginAlert.textContent = 'An error occurred. Please try again.';
            loginAlert.style.display = 'block';
            console.error('Login error:', error);
        });
    }

    // Handle register form submission
    function handleRegister(e) {
        e.preventDefault();
        
        const username = document.getElementById('reg-username').value;
        const email = document.getElementById('reg-email').value;
        const password = document.getElementById('reg-password').value;
        
        // Validate input
        if (username.length < 3) {
            registerAlert.textContent = 'Username must be at least 3 characters';
            registerAlert.style.display = 'block';
            return;
        }
        
        if (password.length < 8) {
            registerAlert.textContent = 'Password must be at least 8 characters';
            registerAlert.style.display = 'block';
            return;
        }
        
        // Create form data
        const formData = new FormData();
        formData.append('username', username);
        formData.append('email', email);
        formData.append('password', password);
        
        // Send register request
        fetch('api/register.php', {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                // Show success message
                registerAlert.textContent = 'Registration successful! You can now login.';
                registerAlert.className = 'alert success';
                registerAlert.style.display = 'block';
                
                // Clear form
                document.getElementById('reg-username').value = '';
                document.getElementById('reg-email').value = '';
                document.getElementById('reg-password').value = '';
                
                // Show login form after 2 seconds
                setTimeout(() => {
                    registerForm.style.display = 'none';
                    loginForm.style.display = 'block';
                }, 2000);
            } else {
                // Show error
                registerAlert.textContent = data.error || 'Registration failed';
                registerAlert.className = 'alert';
                registerAlert.style.display = 'block';
            }
        })
        .catch(error => {
            registerAlert.textContent = 'An error occurred. Please try again.';
            registerAlert.className = 'alert';
            registerAlert.style.display = 'block';
            console.error('Register error:', error);
        });
    }

    // Handle logout
    function logout(e) {
        if (e) e.preventDefault();
        
        // Send logout request
        fetch('api/logout.php', {
            method: 'POST',
            headers: {
                'Authorization': 'Bearer ' + sessionToken
            }
        })
        .then(() => {
            // Clear session
            localStorage.removeItem('session_token');
            localStorage.removeItem('user_id');
            localStorage.removeItem('username');
            sessionToken = null;
            
            // Update UI
            checkAuth();
        })
        .catch(error => {
            console.error('Logout error:', error);
            // Force logout anyway
            localStorage.removeItem('session_token');
            localStorage.removeItem('user_id');
            localStorage.removeItem('username');
            sessionToken = null;
            checkAuth();
        });
    }

    // Handle file upload
    function handleFileUpload(e) {
        e.preventDefault();
        
        const fileInput = document.getElementById('file');
        const expireHours = document.getElementById('expire-hours').value;
        
        if (!fileInput.files || fileInput.files.length === 0) {
            uploadAlert.textContent = 'Please select a file to upload';
            uploadAlert.className = 'alert';
            uploadAlert.style.display = 'block';
            return;
        }
        
        // Create form data
        const formData = new FormData();
        formData.append('file', fileInput.files[0]);
        formData.append('expire_hours', expireHours);
        formData.append('token', sessionToken);
        
        // Show loading state
        const submitBtn = document.querySelector('#upload-form button');
        const originalBtnText = submitBtn.textContent;
        submitBtn.disabled = true;
        submitBtn.textContent = 'Uploading...';
        
        // Send upload request
        fetch('api/upload.php', {
            method: 'POST',
            headers: {
                'Authorization': 'Bearer ' + sessionToken
            },
            body: formData
        })
        .then(response => {
            // First check if the response is OK
            if (!response.ok) {
                throw new Error(`HTTP error! status: ${response.status}`);
            }
            // Get the response text first
            return response.text().then(text => {
                try {
                    return text ? JSON.parse(text) : { error: 'Empty response' };
                } catch (e) {
                    console.error('Failed to parse JSON:', text);
                    throw new Error('Invalid server response');
                }
            });
        })
        .then(data => {
            // Reset button
            submitBtn.disabled = false;
            submitBtn.textContent = originalBtnText;
            
            if (data.success) {
                // Show success message
                uploadAlert.textContent = 'File uploaded successfully!';
                uploadAlert.className = 'alert success';
                uploadAlert.style.display = 'block';
                
                // Clear file input
                fileInput.value = '';
                
                // Refresh file list
                loadUserFiles();
                
                // Copy download URL to clipboard
                navigator.clipboard.writeText(data.download_url)
                    .then(() => {
                        uploadAlert.textContent += ' Download link copied to clipboard.';
                    })
                    .catch(err => {
                        console.error('Could not copy URL: ', err);
                    });
            } else {
                // Show error
                uploadAlert.textContent = data.error || 'Upload failed';
                uploadAlert.className = 'alert';
                uploadAlert.style.display = 'block';
            }
        })
        .catch(error => {
            // Reset button
            submitBtn.disabled = false;
            submitBtn.textContent = originalBtnText;
            
            // Show error
            uploadAlert.textContent = 'An error occurred. Please try again.';
            uploadAlert.className = 'alert';
            uploadAlert.style.display = 'block';
            console.error('Upload error:', error);
        });
    }

    // Load user files
    function loadUserFiles() {
        if (!fileListContainer) return;
        
        // Show loader
        loader.style.display = 'block';
        fileTable.style.display = 'none';
        emptyMessage.style.display = 'none';
        
        fetch('api/list-files.php', {
            method: 'GET',
            headers: {
                'Authorization': 'Bearer ' + sessionToken
            }
        })
        .then(response => response.json())
        .then(data => {
            loader.style.display = 'none';
            
            if (data.files && data.files.length > 0) {
                fileTable.style.display = 'table';
                fileTableBody.innerHTML = '';
                
                data.files.forEach(file => {
                    const row = document.createElement('tr');
                    const uploadDate = new Date(file.created_at);
                    const expireDate = new Date(file.expire_time);
                    const now = new Date();
                    
                    let timeLeft = '';
                    if (expireDate > now) {
                        const diffMs = expireDate - now;
                        const diffHrs = Math.floor(diffMs / (1000 * 60 * 60));
                        timeLeft = diffHrs > 24 ? 
                            `${Math.floor(diffHrs/24)} days` : 
                            `${diffHrs} hours`;
                    } else {
                        timeLeft = 'Expired';
                    }
                    
                    row.innerHTML = `
                        <td>${file.file_name}</td>
                        <td>${uploadDate.toLocaleString()}</td>
                        <td>${timeLeft}</td>
                        <td>
                            <a href="${file.download_url}" class="btn btn-small" target="_blank">Download</a>
                            <button class="btn btn-small btn-copy" data-url="${file.download_url}">Copy Link</button>
                            <button class="btn btn-small btn-delete" data-id="${file.id}">Delete</button>
                        </td>
                    `;
                    
                    fileTableBody.appendChild(row);
                });
                
                // Add event listeners for buttons
                document.querySelectorAll('.btn-copy').forEach(btn => {
                    btn.addEventListener('click', copyDownloadLink);
                });
                document.querySelectorAll('.btn-delete').forEach(btn => {
                    btn.addEventListener('click', deleteFile);
                });
            } else {
                emptyMessage.style.display = 'block';
            }
        })
        .catch(error => {
            loader.style.display = 'none';
            emptyMessage.textContent = 'Could not load files. Please try again.';
            emptyMessage.style.display = 'block';
            console.error('Load files error:', error);
        });
    }

    // Copy download link to clipboard
    function copyDownloadLink() {
        const url = this.getAttribute('data-url');
        
        navigator.clipboard.writeText(url)
            .then(() => {
                // Show temporary success indicator
                const originalText = this.textContent;
                this.textContent = 'Copied!';
                
                setTimeout(() => {
                    this.textContent = originalText;
                }, 2000);
            })
            .catch(err => {
                console.error('Could not copy URL: ', err);
                alert('Failed to copy link. Please try again.');
            });
    }

    // Delete file
    function deleteFile() {
        if (!confirm('Are you sure you want to delete this file?')) {
            return;
        }
        
        const fileId = this.getAttribute('data-id');
        const row = this.closest('tr');
        
        // Create form data
        const formData = new FormData();
        formData.append('file_id', fileId);
        
        // Send delete request
        fetch('api/delete-file.php', {
            method: 'POST',
            headers: {
                'Authorization': 'Bearer ' + sessionToken
            },
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                // Remove row from table
                row.remove();
                
                // Check if table is now empty
                if (fileTableBody.children.length === 0) {
                    fileTable.style.display = 'none';
                    emptyMessage.style.display = 'block';
                }
            } else {
                alert(data.error || 'Failed to delete file. Please try again.');
            }
        })
        .catch(error => {
            console.error('Delete error:', error);
            alert('An error occurred. Please try again.');
        });
    }

    // Attach event listeners
    if (showLoginLink) showLoginLink.addEventListener('click', toggleAuthForms);
    if (showRegisterLink) showRegisterLink.addEventListener('click', toggleAuthForms);
    
    if (document.getElementById('login')) {
        document.getElementById('login').addEventListener('submit', handleLogin);
    }
    
    if (document.getElementById('register')) {
        document.getElementById('register').addEventListener('submit', handleRegister);
    }
    
    if (document.getElementById('upload-form')) {
        document.getElementById('upload-form').addEventListener('submit', handleFileUpload);
    }
    
    if (document.getElementById('logout')) {
        document.getElementById('logout').addEventListener('click', logout);
    }
    
    // Check authentication status
    checkAuth();
});