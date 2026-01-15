
<?php
session_start();
require_once 'includes/config.php';

// Check if already logged in, redirect to dashboard
if (isset($_SESSION['admin_logged_in'])) {
    header("Location: dashboard.php");
    exit;
}

$message = '';
$success = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['setup'])) {
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
        
        // Create tables
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
        
        // Insert default admin user
        $defaultPasswordHash = password_hash('Admin123!', PASSWORD_BCRYPT);
        $stmt = $pdo->prepare("
            INSERT IGNORE INTO admin_users (username, password_hash, name, email, role) 
            VALUES (?, ?, ?, ?, ?)
        ");
        $stmt->execute(['admin', $defaultPasswordHash, 'Administrator', 'admin@' . strtolower(str_replace(' ', '', SITE_TITLE)) . '.com', 'admin']);
        
        $success = true;
        $message = 'Database setup completed successfully! Default admin user created:<br>
                   Username: admin<br>
                   Password: Admin123!';
        
    } catch (PDOException $e) {
        $message = 'Setup failed: ' . $e->getMessage();
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Setup | <?php echo htmlspecialchars(SITE_TITLE); ?></title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        body {
            font-family: 'Segoe UI', Roboto, sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            height: 100vh;
            display: flex;
            justify-content: center;
            align-items: center;
            margin: 0;
        }
        
        .setup-container {
            background: white;
            border-radius: 15px;
            box-shadow: 0 20px 40px rgba(0,0,0,0.2);
            width: 100%;
            max-width: 500px;
            padding: 40px;
            text-align: center;
        }
        
        h1 {
            color: #333;
            margin-bottom: 20px;
        }
        
        .message {
            padding: 15px;
            border-radius: 8px;
            margin-bottom: 20px;
        }
        
        .success {
            background: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }
        
        .error {
            background: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }
        
        .btn {
            padding: 12px 30px;
            background: #667eea;
            color: white;
            border: none;
            border-radius: 8px;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s;
            text-decoration: none;
            display: inline-block;
            margin-top: 20px;
        }
        
        .btn:hover {
            background: #5a6fd8;
        }
    </style>
</head>
<body>
    <div class="setup-container">
        <h1><i class="fas fa-database"></i> Database Setup</h1>
        
        <?php if ($message): ?>
            <div class="message <?php echo $success ? 'success' : 'error'; ?>">
                <?php echo $message; ?>
            </div>
        <?php endif; ?>
        
        <?php if (!$success): ?>
            <p>Click the button below to create the necessary database tables and default admin user.</p>
            <form method="POST">
                <button type="submit" name="setup" class="btn">
                    <i class="fas fa-cogs"></i> Run Setup
                </button>
            </form>
        <?php else: ?>
            <a href="login.php" class="btn">
                <i class="fas fa-sign-in-alt"></i> Go to Login
            </a>
        <?php endif; ?>
        
        <div style="margin-top: 30px; font-size: 14px; color: #666;">
            <p><strong>Note:</strong> Make sure your database configuration in <code>includes/config.php</code> is correct.</p>
        </div>
    </div>
</body>
</html>