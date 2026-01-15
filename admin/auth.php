<?php
session_start();
require_once 'includes/config.php';

// Check if user is already logged in
if (isset($_SESSION['admin_logged_in'])) {
    header("Location: dashboard.php");
    exit;
}

// Only process POST requests
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: login.php");
    exit;
}

// Check if login form was submitted
if (isset($_POST['login'])) {
    $username = trim($_POST['username'] ?? '');
    $password = trim($_POST['password'] ?? '');
    
    // Validate inputs
    if (empty($username) || empty($password)) {
        $_SESSION['login_error'] = 'Please enter both username and password.';
        header("Location: login.php");
        exit;
    }
    
    try {
        // Create database connection
        $pdo = new PDO(
            "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8mb4",
            DB_USER,
            DB_PASS,
            [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
            ]
        );
        
        // First, check if admin_users table exists, if not create it
        $tableCheck = $pdo->query("SHOW TABLES LIKE 'admin_users'")->fetch();
        
        if (!$tableCheck) {
            // Create admin_users table if it doesn't exist
            $pdo->exec("
                CREATE TABLE IF NOT EXISTS admin_users (
                    id INT AUTO_INCREMENT PRIMARY KEY,
                    username VARCHAR(50) UNIQUE NOT NULL,
                    password_hash VARCHAR(255) NOT NULL,
                    name VARCHAR(100) NOT NULL,
                    email VARCHAR(100),
                    role ENUM('super_admin', 'admin') DEFAULT 'admin',
                    is_active BOOLEAN DEFAULT TRUE,
                    last_login TIMESTAMP NULL,
                    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
            ");
            
            // Create login_logs table if it doesn't exist
            $pdo->exec("
                CREATE TABLE IF NOT EXISTS login_logs (
                    id INT AUTO_INCREMENT PRIMARY KEY,
                    username VARCHAR(50) NOT NULL,
                    ip_address VARCHAR(45) NOT NULL,
                    user_agent TEXT,
                    login_time TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                    status ENUM('success', 'failed') DEFAULT 'failed'
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
            ");
            
            // Insert default admin user if table was just created
            $defaultPasswordHash = password_hash('Admin123!', PASSWORD_BCRYPT);
            $stmt = $pdo->prepare("
                INSERT INTO admin_users (username, password_hash, name, email, role) 
                VALUES (?, ?, ?, ?, ?)
            ");
            $stmt->execute(['admin', $defaultPasswordHash, 'Administrator', 'admin@' . strtolower(str_replace(' ', '', SITE_TITLE)) . '.com', 'admin']);
        }
        
        // Fetch user from database
        $stmt = $pdo->prepare("SELECT * FROM admin_users WHERE username = ? AND is_active = 1");
        $stmt->execute([$username]);
        $user = $stmt->fetch();
        
        if ($user && password_verify($password, $user['password_hash'])) {
            // Login successful
            
            // Update last login time
            $updateStmt = $pdo->prepare("UPDATE admin_users SET last_login = NOW() WHERE id = ?");
            $updateStmt->execute([$user['id']]);
            
            // Log successful login
            $logStmt = $pdo->prepare("INSERT INTO login_logs (username, ip_address, user_agent, status) VALUES (?, ?, ?, 'success')");
            $logStmt->execute([$username, $_SERVER['REMOTE_ADDR'], $_SERVER['HTTP_USER_AGENT']]);
            
            // Set session variables
            $_SESSION['admin_logged_in'] = true;
            $_SESSION['admin_id'] = $user['id'];
            $_SESSION['admin_name'] = $user['name'];
            $_SESSION['admin_username'] = $user['username'];
            $_SESSION['admin_role'] = $user['role'];
            $_SESSION['login_time'] = time();
            $_SESSION['last_activity'] = time();
            
            // Redirect to dashboard
            header("Location: dashboard.php");
            exit;
            
        } else {
            // Log failed login attempt
            try {
                $logStmt = $pdo->prepare("INSERT INTO login_logs (username, ip_address, user_agent, status) VALUES (?, ?, ?, 'failed')");
                $logStmt->execute([$username, $_SERVER['REMOTE_ADDR'], $_SERVER['HTTP_USER_AGENT']]);
            } catch (Exception $e) {
                error_log("Failed to log login attempt: " . $e->getMessage());
            }
            
            $_SESSION['login_error'] = 'Invalid username or password!';
            header("Location: login.php");
            exit;
        }
        
    } catch (PDOException $e) {
        error_log("Auth Error: " . $e->getMessage());
        
        // For debugging, you can show the error
        if (defined('DEBUG') && DEBUG) {
            $_SESSION['login_error'] = 'Database error: ' . $e->getMessage();
        } else {
            $_SESSION['login_error'] = 'System error. Please try again later.';
        }
        
        header("Location: login.php");
        exit;
    }
    
} else {
    // Not a login request
    header("Location: login.php");
    exit;
}
?>