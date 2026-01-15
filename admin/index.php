<?php
session_start();
require_once '../includes/config.php';

if (!isset($_SESSION['admin_logged_in'])) {
    $_SESSION['redirect_url'] = $_SERVER['REQUEST_URI'];
    header("Location: login.php");
    exit;
}


header("X-Frame-Options: DENY");
header("X-Content-Type-Options: nosniff");

try {
   
    $projectsCount = $db->query("SELECT COUNT(*) FROM projects")->fetchColumn();
    $messagesCount = $db->query("SELECT COUNT(*) FROM messages WHERE is_read = 0")->fetchColumn();
    $skillsCount = $db->query("SELECT COUNT(*) FROM skills")->fetchColumn();
    
    $recentMessages = $db->query("SELECT * FROM messages ORDER BY created_at DESC LIMIT 5")->fetchAll(PDO::FETCH_ASSOC);
    
} catch (PDOException $e) {
    error_log("Admin Dashboard Error: " . $e->getMessage());
    die("Database error. Please try again later.");
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard | <?php echo htmlspecialchars(SITE_TITLE); ?></title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        :root {
            --primary: #3498db;
            --secondary: #2ecc71;
            --dark: #2c3e50;
            --light: #ecf0f1;
            --danger: #e74c3c;
            --sidebar-width: 250px;
        }
        
        body {
            font-family: 'Segoe UI', Roboto, sans-serif;
            margin: 0;
            background: #f5f7fa;
            color: var(--dark);
        }
        
        .admin-container {
            display: flex;
            min-height: 100vh;
        }
        
        /* Sidebar Styles */
        .admin-sidebar {
            width: var(--sidebar-width);
            background: var(--dark);
            color: white;
            padding: 20px 0;
            position: fixed;
            height: 100vh;
            box-shadow: 2px 0 10px rgba(0,0,0,0.1);
        }
        
        .sidebar-header {
            padding: 0 20px 20px;
            border-bottom: 1px solid rgba(255,255,255,0.1);
        }
        
        .sidebar-header h2 {
            color: white;
            margin: 0;
            font-size: 1.2rem;
        }
        
        .sidebar-header h2 span {
            color: var(--primary);
        }
        
        .admin-menu {
            padding: 20px 0;
        }
        
        .admin-menu a {
            display: block;
            padding: 12px 20px;
            color: var(--light);
            text-decoration: none;
            transition: all 0.3s;
        }
        
        .admin-menu a:hover, .admin-menu a.active {
            background: rgba(255,255,255,0.1);
            color: white;
            border-left: 3px solid var(--primary);
        }
        
        .admin-menu a i {
            margin-right: 10px;
            width: 20px;
            text-align: center;
        }
        
        /* Main Content */
        .admin-main {
            margin-left: var(--sidebar-width);
            flex: 1;
            padding: 30px;
        }
        
        .admin-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 30px;
            padding-bottom: 20px;
            border-bottom: 1px solid #eee;
        }
        
        .admin-user {
            display: flex;
            align-items: center;
            gap: 15px;
        }
        
        .user-avatar {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            background: var(--primary);
            color: white;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: bold;
        }
        
        /* Stats Cards */
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }
        
        .stat-card {
            background: white;
            border-radius: 8px;
            padding: 20px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.05);
            transition: transform 0.3s;
        }
        
        .stat-card:hover {
            transform: translateY(-5px);
        }
        
        .stat-icon {
            width: 50px;
            height: 50px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-size: 1.2rem;
            margin-bottom: 15px;
        }
        
        .stat-info h3 {
            font-size: 1.8rem;
            margin: 0;
            color: var(--dark);
        }
        
        .stat-info p {
            margin: 5px 0 0;
            color: #777;
        }
        
        /* Recent Messages */
        .messages-list {
            background: white;
            border-radius: 8px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.05);
            overflow: hidden;
        }
        
        .message-item {
            padding: 15px 20px;
            border-bottom: 1px solid #eee;
            transition: background 0.3s;
        }
        
        .message-item:hover {
            background: #f9f9f9;
        }
        
        .message-item.unread {
            background: #f8f9fa;
            border-left: 3px solid var(--primary);
        }
        
        .message-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 10px;
        }
        
        .message-header h4 {
            margin: 0;
            color: var(--dark);
        }
        
        .message-header span {
            color: #777;
            font-size: 0.9rem;
        }
        
        .message-item p {
            margin: 0;
            color: #555;
        }
        
        .message-actions {
            display: flex;
            gap: 10px;
            margin-top: 15px;
        }
        
        .btn {
            padding: 8px 15px;
            border-radius: 4px;
            font-size: 0.9rem;
            cursor: pointer;
            transition: all 0.3s;
            border: none;
        }
        
        .btn-primary {
            background: var(--primary);
            color: white;
        }
        
        .btn-danger {
            background: var(--danger);
            color: white;
        }
        
        .btn:hover {
            opacity: 0.9;
            transform: translateY(-2px);
        }
        
        @media (max-width: 768px) {
            .admin-container {
                flex-direction: column;
            }
            
            .admin-sidebar {
                width: 100%;
                height: auto;
                position: relative;
            }
            
            .admin-main {
                margin-left: 0;
            }
        }
    </style>
</head>
<body>
    <div class="admin-container">
        <!-- Sidebar -->
        <aside class="admin-sidebar">
            <div class="sidebar-header">
                <h2><?php echo htmlspecialchars(SITE_TITLE); ?> <span>Admin</span></h2>
            </div>
            
            <nav class="admin-menu">
                <a href="index.php" class="active"><i class="fas fa-tachometer-alt"></i> Dashboard</a>
                <a href="manage-projects.php"><i class="fas fa-project-diagram"></i> Projects</a>
                <a href="manage-messages.php"><i class="fas fa-envelope"></i> Messages</a>
                <a href="manage-skills.php"><i class="fas fa-code"></i> Skills</a>
                <a href="settings.php"><i class="fas fa-cog"></i> Settings</a>
                <a href="logout.php"><i class="fas fa-sign-out-alt"></i> Logout</a>
            </nav>
        </aside>
        
        <!-- Main Content -->
        <main class="admin-main">
            <header class="admin-header">
                <h1>Dashboard Overview</h1>
                <div class="admin-user">
                    <div class="user-avatar">
                        <?php echo strtoupper(substr($_SESSION['admin_name'] ?? 'A', 0, 1)); ?>
                    </div>
                    <span><?php echo htmlspecialchars($_SESSION['admin_name'] ?? 'Admin'); ?></span>
                </div>
            </header>
            
            <!-- Stats Cards -->
            <div class="stats-grid">
                <div class="stat-card">
                    <div class="stat-icon" style="background-color: var(--primary);">
                        <i class="fas fa-project-diagram"></i>
                    </div>
                    <div class="stat-info">
                        <h3><?php echo htmlspecialchars($projectsCount); ?></h3>
                        <p>Total Projects</p>
                    </div>
                </div>
                
                <div class="stat-card">
                    <div class="stat-icon" style="background-color: var(--secondary);">
                        <i class="fas fa-envelope"></i>
                    </div>
                    <div class="stat-info">
                        <h3><?php echo htmlspecialchars($messagesCount); ?></h3>
                        <p>Unread Messages</p>
                    </div>
                </div>
                
                <div class="stat-card">
                    <div class="stat-icon" style="background-color: var(--danger);">
                        <i class="fas fa-code"></i>
                    </div>
                    <div class="stat-info">
                        <h3><?php echo htmlspecialchars($skillsCount); ?></h3>
                        <p>Skills Listed</p>
                    </div>
                </div>
            </div>
            
            <!-- Recent Messages -->
            <section class="recent-section">
                <h2>Recent Messages</h2>
                <div class="messages-list">
                    <?php foreach ($recentMessages as $message): ?>
                    <div class="message-item <?php echo $message['is_read'] ? '' : 'unread'; ?>">
                        <div class="message-header">
                            <h4><?php echo htmlspecialchars($message['name']); ?></h4>
                            <span><?php echo date('M j, Y g:i a', strtotime($message['created_at'])); ?></span>
                        </div>
                        <p><?php echo htmlspecialchars(substr($message['message'], 0, 100) . '...'); ?></p>
                        <div class="message-actions">
                            <a href="view-message.php?id=<?php echo (int)$message['id']; ?>" class="btn btn-primary">View</a>
                            <a href="delete-message.php?id=<?php echo (int)$message['id']; ?>" class="btn btn-danger" onclick="return confirm('Delete this message?')">Delete</a>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
            </section>
        </main>
    </div>

    <script>
        // Confirm before destructive actions
        document.querySelectorAll('.btn-danger').forEach(btn => {
            btn.addEventListener('click', (e) => {
                if (!confirm('Are you sure?')) {
                    e.preventDefault();
                }
            });
        });
    </script>
</body>
</html>