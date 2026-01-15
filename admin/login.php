
<?php
// Start session
session_start();

// Check if already logged in
if (isset($_SESSION['admin_logged_in'])) {
    header("Location: dashboard.php");
    exit;
}

// Simple credentials
$valid_username = 'admin';
$valid_password = 'Admin123!';

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = $_POST['username'] ?? '';
    $password = $_POST['password'] ?? '';
    
    if ($username === $valid_username && $password === $valid_password) {
        $_SESSION['admin_logged_in'] = true;
        $_SESSION['admin_name'] = 'Administrator';
        
        // Redirect to dashboard
        header("Location: dashboard.php");
        exit;
    } else {
        $error = 'Invalid credentials';
    }
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Login</title>
    <style>
        body { font-family: Arial; padding: 50px; }
        .login-box { max-width: 400px; margin: 0 auto; }
        .error { color: red; }
    </style>
</head>
<body>
    <div class="login-box">
        <h2>Login</h2>
        
        <?php if ($error): ?>
            <div class="error"><?php echo $error; ?></div>
        <?php endif; ?>
        
        <form method="POST">
            <div>
                <label>Username:</label><br>
                <input type="text" name="username" required>
            </div>
            <div>
                <label>Password:</label><br>
                <input type="password" name="password" required>
            </div>
            <div>
                <button type="submit">Login</button>
            </div>
        </form>
        
        <p>Test credentials: admin / Admin123!</p>
    </div>
</body>
</html>