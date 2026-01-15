
<?php
session_start();
require_once 'includes/config.php';

if (!isset($_SESSION['admin_logged_in'])) {
    header("Location: login.php");
    exit;
}

try {
    $pdo = new PDO(
        "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8mb4",
        DB_USER,
        DB_PASS,
        [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
        ]
    );
    
    $message = '';
    $error = '';
    
    // Handle form submission
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        if (isset($_POST['update_profile'])) {
            // Update admin profile
            $name = trim($_POST['name']);
            $email = trim($_POST['email']);
            
            $stmt = $pdo->prepare("UPDATE admin_users SET name = ?, email = ? WHERE id = ?");
            $stmt->execute([$name, $email, $_SESSION['admin_id']]);
            
            // Update session
            $_SESSION['admin_name'] = $name;
            
            $message = 'Profile updated successfully';
            
        } elseif (isset($_POST['change_password'])) {
            // Change password
            $current_password = $_POST['current_password'];
            $new_password = $_POST['new_password'];
            $confirm_password = $_POST['confirm_password'];
            
            // Validate
            if (empty($current_password) || empty($new_password) || empty($confirm_password)) {
                $error = 'All password fields are required';
            } elseif ($new_password !== $confirm_password) {
                $error = 'New passwords do not match';
            } elseif (strlen($new_password) < 8) {
                $error = 'Password must be at least 8 characters';
            } else {
                // Get current password hash
                $stmt = $pdo->prepare("SELECT password_hash FROM admin_users WHERE id = ?");
                $stmt->execute([$_SESSION['admin_id']]);
                $user = $stmt->fetch();
                
                if ($user && password_verify($current_password, $user['password_hash'])) {
                    $new_hash = password_hash($new_password, PASSWORD_BCRYPT);
                    $stmt = $pdo->prepare("UPDATE admin_users SET password_hash = ? WHERE id = ?");
                    $stmt->execute([$new_hash, $_SESSION['admin_id']]);
                    $message = 'Password changed successfully';
                } else {
                    $error = 'Current password is incorrect';
                }
            }
            
        } elseif (isset($_POST['update_site'])) {
            // Update site settings (you might want to store these in a separate table)
            $site_title = trim($_POST['site_title']);
            $site_description = trim($_POST['site_description']);
            $contact_email = trim($_POST['contact_email']);
            
            // For now, we'll just show a message
            // In production, you'd save these to a database table
            $message = 'Site settings updated (database integration needed)';
        }
    }
    
    // Get admin info
    $stmt = $pdo->prepare("SELECT * FROM admin_users WHERE id = ?");
    $stmt->execute([$_SESSION['admin_id']]);
    $admin = $stmt->fetch();
    
    // Get system info
    $system_info = [
        'php_version' => PHP_VERSION,
        'server_software' => $_SERVER['SERVER_SOFTWARE'],
        'mysql_version' => $pdo->query("SELECT VERSION()")->fetchColumn(),
        'upload_max_filesize' => ini_get('upload_max_filesize'),
        'memory_limit' => ini_get('memory_limit')
    ];
    
    include 'includes/header.php';
    ?>
    <div class="admin-container">
        <?php include 'includes/sidebar.php'; ?>
        <main class="admin-main">
            <header class="admin-header">
                <h1>Settings</h1>
                <div class="admin-user">
                    <div class="user-avatar">
                        <?php echo strtoupper(substr($admin['name'], 0, 1)); ?>
                    </div>
                    <div>
                        <div style="font-weight: 600;"><?php echo htmlspecialchars($admin['name']); ?></div>
                        <div style="font-size: 0.85rem; color: #64748b;">Administrator</div>
                    </div>
                </div>
            </header>
            
            <?php if ($message): ?>
                <div class="alert alert-success">
                    <i class="fas fa-check-circle"></i> <?php echo htmlspecialchars($message); ?>
                </div>
            <?php endif; ?>
            
            <?php if ($error): ?>
                <div class="alert alert-danger">
                    <i class="fas fa-exclamation-circle"></i> <?php echo htmlspecialchars($error); ?>
                </div>
            <?php endif; ?>
            
            <div class="settings-tabs">
                <div class="tab-nav">
                    <button class="tab-link active" data-tab="profile">Profile</button>
                    <button class="tab-link" data-tab="password">Password</button>
                    <button class="tab-link" data-tab="site">Site Settings</button>
                    <button class="tab-link" data-tab="system">System Info</button>
                </div>
                
                <div class="tab-content">
                    <!-- Profile Tab -->
                    <div id="profile" class="tab-pane active">
                        <div class="card">
                            <h2>Profile Information</h2>
                            <form method="POST">
                                <div class="form-group">
                                    <label for="name">Full Name</label>
                                    <input type="text" id="name" name="name" 
                                           value="<?php echo htmlspecialchars($admin['name']); ?>" required>
                                </div>
                                
                                <div class="form-group">
                                    <label for="username">Username</label>
                                    <input type="text" id="username" value="<?php echo htmlspecialchars($admin['username']); ?>" disabled>
                                    <small class="help-text">Username cannot be changed</small>
                                </div>
                                
                                <div class="form-group">
                                    <label for="email">Email Address</label>
                                    <input type="email" id="email" name="email" 
                                           value="<?php echo htmlspecialchars($admin['email']); ?>" required>
                                </div>
                                
                                <div class="form-group">
                                    <label>Role</label>
                                    <input type="text" value="<?php echo ucfirst(str_replace('_', ' ', $admin['role'])); ?>" disabled>
                                </div>
                                
                                <div class="form-group">
                                    <label>Last Login</label>
                                    <input type="text" value="<?php echo $admin['last_login'] ? date('F j, Y g:i a', strtotime($admin['last_login'])) : 'Never'; ?>" disabled>
                                </div>
                                
                                <div class="form-actions">
                                    <button type="submit" name="update_profile" class="btn btn-primary">
                                        <i class="fas fa-save"></i> Update Profile
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                    
                    <!-- Password Tab -->
                    <div id="password" class="tab-pane">
                        <div class="card">
                            <h2>Change Password</h2>
                            <form method="POST">
                                <div class="form-group">
                                    <label for="current_password">Current Password</label>
                                    <input type="password" id="current_password" name="current_password" required>
                                </div>
                                
                                <div class="form-group">
                                    <label for="new_password">New Password</label>
                                    <input type="password" id="new_password" name="new_password" required>
                                    <small class="help-text">Minimum 8 characters</small>
                                </div>
                                
                                <div class="form-group">
                                    <label for="confirm_password">Confirm New Password</label>
                                    <input type="password" id="confirm_password" name="confirm_password" required>
                                </div>
                                
                                <div class="form-actions">
                                    <button type="submit" name="change_password" class="btn btn-primary">
                                        <i class="fas fa-key"></i> Change Password
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                    
                    <!-- Site Settings Tab -->
                    <div id="site" class="tab-pane">
                        <div class="card">
                            <h2>Site Settings</h2>
                            <form method="POST">
                                <div class="form-group">
                                    <label for="site_title">Site Title</label>
                                    <input type="text" id="site_title" name="site_title" 
                                           value="<?php echo htmlspecialchars(SITE_TITLE); ?>">
                                </div>
                                
                                <div class="form-group">
                                    <label for="site_description">Site Description</label>
                                    <textarea id="site_description" name="site_description" rows="3"></textarea>
                                </div>
                                
                                <div class="form-group">
                                    <label for="contact_email">Contact Email</label>
                                    <input type="email" id="contact_email" name="contact_email">
                                </div>
                                
                                <div class="form-actions">
                                    <button type="submit" name="update_site" class="btn btn-primary">
                                        <i class="fas fa-save"></i> Save Settings
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                    
                    <!-- System Info Tab -->
                    <div id="system" class="tab-pane">
                        <div class="card">
                            <h2>System Information</h2>
                            <div class="system-info-grid">
                                <div class="info-item">
                                    <span class="info-label">PHP Version</span>
                                    <span class="info-value"><?php echo $system_info['php_version']; ?></span>
                                </div>
                                
                                <div class="info-item">
                                    <span class="info-label">Server Software</span>
                                    <span class="info-value"><?php echo $system_info['server_software']; ?></span>
                                </div>
                                
                                <div class="info-item">
                                    <span class="info-label">MySQL Version</span>
                                    <span class="info-value"><?php echo $system_info['mysql_version']; ?></span>
                                </div>
                                
                                <div class="info-item">
                                    <span class="info-label">Max Upload Size</span>
                                    <span class="info-value"><?php echo $system_info['upload_max_filesize']; ?></span>
                                </div>
                                
                                <div class="info-item">
                                    <span class="info-label">Memory Limit</span>
                                    <span class="info-value"><?php echo $system_info['memory_limit']; ?></span>
                                </div>
                                
                                <div class="info-item">
                                    <span class="info-label">Session Timeout</span>
                                    <span class="info-value"><?php echo SESSION_TIMEOUT / 60; ?> minutes</span>
                                </div>
                            </div>
                            
                            <div class="system-actions">
                                <a href="phpinfo.php" target="_blank" class="btn btn-outline">
                                    <i class="fas fa-info-circle"></i> View phpinfo()
                                </a>
                                <a href="logs.php" class="btn btn-outline">
                                    <i class="fas fa-file-alt"></i> View Logs
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </main>
    </div>
    
    <script>
        // Tab switching
        document.querySelectorAll('.tab-link').forEach(link => {
            link.addEventListener('click', function() {
                // Remove active class from all tabs
                document.querySelectorAll('.tab-link').forEach(t => t.classList.remove('active'));
                document.querySelectorAll('.tab-pane').forEach(p => p.classList.remove('active'));
                
                // Add active class to clicked tab
                this.classList.add('active');
                const tabId = this.getAttribute('data-tab');
                document.getElementById(tabId).classList.add('active');
            });
        });
        
        // Password confirmation validation
        const newPassword = document.getElementById('new_password');
        const confirmPassword = document.getElementById('confirm_password');
        
        if (newPassword && confirmPassword) {
            function validatePasswords() {
                if (newPassword.value !== confirmPassword.value) {
                    confirmPassword.setCustomValidity('Passwords do not match');
                } else {
                    confirmPassword.setCustomValidity('');
                }
            }
            
            newPassword.addEventListener('input', validatePasswords);
            confirmPassword.addEventListener('input', validatePasswords);
        }
    </script>
    <?php
    include 'includes/footer.php';
    
} catch (PDOException $e) {
    die("Database error: " . $e->getMessage());
}
?>