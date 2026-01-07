<?php
session_start();
require_once '../includes/config.php';

// Security check
if (!isset($_SESSION['admin_logged_in'])) {
    header("Location: login.php");
    exit;
}

// Set secure headers
header("X-Frame-Options: DENY");
header("X-Content-Type-Options: nosniff");

// Fetch dashboard data
try {
    // Projects statistics
    $projectsData = $db->query("
        SELECT 
            COUNT(*) as total,
            SUM(CASE WHEN created_at >= DATE_SUB(NOW(), INTERVAL 7 DAY) THEN 1 ELSE 0 END) as new_this_week
        FROM projects
    ")->fetch(PDO::FETCH_ASSOC);

    // Messages statistics
    $messagesData = $db->query("
        SELECT 
            COUNT(*) as total,
            SUM(is_read = 0) as unread,
            SUM(CASE WHEN created_at >= DATE_SUB(NOW(), INTERVAL 7 DAY) THEN 1 ELSE 0 END) as new_this_week
        FROM messages
    ")->fetch(PDO::FETCH_ASSOC);

    // Skills statistics
    $skillsCount = $db->query("SELECT COUNT(*) FROM skills")->fetchColumn();

    // Recent activity
    $recentActivity = $db->query("
        (SELECT 'project' as type, id, title as name, created_at FROM projects ORDER BY created_at DESC LIMIT 3)
        UNION ALL
        (SELECT 'message' as type, id, name, created_at FROM messages ORDER BY created_at DESC LIMIT 3)
        ORDER BY created_at DESC LIMIT 5
    ")->fetchAll(PDO::FETCH_ASSOC);

    // Recent messages
    $recentMessages = $db->query("SELECT * FROM messages ORDER BY created_at DESC LIMIT 5")->fetchAll(PDO::FETCH_ASSOC);

} catch (PDOException $e) {
    error_log("Dashboard Error: " . $e->getMessage());
    die("System temporarily unavailable. Please try again later.");
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard | <?php echo htmlspecialchars(SITE_TITLE); ?></title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link href="https://cdn.jsdelivr.net/npm/chart.js@3.7.1/dist/chart.min.css" rel="stylesheet">
    <style>
        :root {
            --primary: #3498db;
            --secondary: #2ecc71;
            --danger: #e74c3c;
            --warning: #f39c12;
            --dark: #2c3e50;
            --light: #ecf0f1;
            --sidebar-width: 250px;
            --header-height: 70px;
        }
        
        body {
            font-family: 'Segoe UI', Roboto, sans-serif;
            margin: 0;
            background: #f5f7fa;
            color: var(--dark);
        }
        
        /* Admin Layout */
        .admin-container {
            display: flex;
            min-height: 100vh;
        }
        
        /* Sidebar Styles */
        .admin-sidebar {
            width: var(--sidebar-width);
            background: var(--dark);
            color: white;
            position: fixed;
            height: 100vh;
            box-shadow: 2px 0 10px rgba(0,0,0,0.1);
            z-index: 100;
        }
        
        .sidebar-header {
            padding: 20px;
            border-bottom: 1px solid rgba(255,255,255,0.1);
            height: var(--header-height);
            display: flex;
            align-items: center;
        }
        
        .sidebar-header h2 {
            color: white;
            margin: 0;
            font-size: 1.3rem;
        }
        
        .sidebar-header h2 span {
            color: var(--primary);
        }
        
        .admin-menu {
            padding: 20px 0;
        }
        
        .admin-menu a {
            display: flex;
            align-items: center;
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
            margin-right: 12px;
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
        
        /* Dashboard Widgets */
        .dashboard-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 25px;
            margin-bottom: 30px;
        }
        
        .dashboard-card {
            background: white;
            border-radius: 10px;
            padding: 25px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.05);
        }
        
        .card-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
        }
        
        .card-header h3 {
            margin: 0;
            font-size: 1.1rem;
            color: var(--dark);
        }
        
        .card-header i {
            font-size: 1.2rem;
            color: #aaa;
        }
        
        /* Stats Cards */
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }
        
        .stat-card {
            background: white;
            border-radius: 10px;
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
        
        .stat-value {
            font-size: 1.8rem;
            font-weight: 700;
            margin: 5px 0;
            color: var(--dark);
        }
        
        .stat-label {
            color: #777;
            font-size: 0.9rem;
        }
        
        .stat-change {
            display: flex;
            align-items: center;
            margin-top: 10px;
            font-size: 0.85rem;
        }
        
        .stat-change.positive {
            color: var(--secondary);
        }
        
        .stat-change.negative {
            color: var(--danger);
        }
        
        /* Activity Feed */
        .activity-item {
            display: flex;
            padding: 15px 0;
            border-bottom: 1px solid #eee;
        }
        
        .activity-icon {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            background: #f0f0f0;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-right: 15px;
            color: var(--primary);
        }
        
        .activity-content {
            flex: 1;
        }
        
        .activity-title {
            margin: 0 0 5px;
            font-weight: 500;
        }
        
        .activity-time {
            color: #888;
            font-size: 0.85rem;
        }
        
        /* Recent Messages */
        .message-item {
            padding: 15px 0;
            border-bottom: 1px solid #eee;
        }
        
        .message-item.unread {
            background: #f8f9fa;
            border-left: 3px solid var(--primary);
            margin-left: -3px;
            padding-left: 3px;
        }
        
        .message-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 8px;
        }
        
        .message-name {
            font-weight: 500;
            color: var(--dark);
        }
        
        .message-time {
            color: #888;
            font-size: 0.85rem;
        }
        
        .message-preview {
            color: #666;
            margin-bottom: 10px;
        }
        
        .message-actions {
            display: flex;
            gap: 10px;
        }
        
        /* Buttons */
        .btn {
            padding: 8px 15px;
            border-radius: 5px;
            font-size: 0.9rem;
            cursor: pointer;
            transition: all 0.3s;
            border: none;
            text-decoration: none;
            display: inline-block;
        }
        
        .btn-sm {
            padding: 5px 10px;
            font-size: 0.8rem;
        }
        
        .btn-primary {
            background: var(--primary);
            color: white;
        }
        
        .btn-secondary {
            background: var(--secondary);
            color: white;
        }
        
        .btn-danger {
            background: var(--danger);
            color: white;
        }
        
        .btn:hover {
            opacity: 0.9;
            transform: translateY(-2px);
            box-shadow: 0 3px 10px rgba(0,0,0,0.1);
        }
        
        /* Charts */
        .chart-container {
            position: relative;
            height: 250px;
            width: 100%;
        }
        
        /* Responsive */
        @media (max-width: 992px) {
            .admin-sidebar {
                width: 70px;
                overflow: hidden;
            }
            
            .sidebar-header h2, .admin-menu a span {
                display: none;
            }
            
            .admin-menu a {
                justify-content: center;
                padding: 15px 0;
            }
            
            .admin-menu a i {
                margin-right: 0;
                font-size: 1.2rem;
            }
            
            .admin-main {
                margin-left: 70px;
            }
        }
        
        @media (max-width: 768px) {
            .stats-grid {
                grid-template-columns: 1fr;
            }
            
            .admin-main {
                padding: 20px;
            }
        }
    </style>
</head>
<body>
    <div class="admin-container">
        <!-- Sidebar Navigation -->
        <aside class="admin-sidebar">
            <div class="sidebar-header">
                <h2><?php echo htmlspecialchars(SITE_TITLE); ?> <span>Admin</span></h2>
            </div>
            
            <nav class="admin-menu">
                <a href="dashboard.php" class="active">
                    <i class="fas fa-tachometer-alt"></i>
                    <span>Dashboard</span>
                </a>
                <a href="projects.php">
                    <i class="fas fa-project-diagram"></i>
                    <span>Projects</span>
                </a>
                <a href="messages.php">
                    <i class="fas fa-envelope"></i>
                    <span>Messages</span>
                    <?php if ($messagesData['unread'] > 0): ?>
                    <span class="badge"><?php echo $messagesData['unread']; ?></span>
                    <?php endif; ?>
                </a>
                <a href="skills.php">
                    <i class="fas fa-code"></i>
                    <span>Skills</span>
                </a>
                <a href="users.php">
                    <i class="fas fa-users"></i>
                    <span>Users</span>
                </a>
                <a href="settings.php">
                    <i class="fas fa-cog"></i>
                    <span>Settings</span>
                </a>
                <a href="logout.php">
                    <i class="fas fa-sign-out-alt"></i>
                    <span>Logout</span>
                </a>
            </nav>
        </aside>
        
        <!-- Main Content -->
        <main class="admin-main">
            <!-- Header -->
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
                    <div class="stat-value"><?php echo $projectsData['total']; ?></div>
                    <div class="stat-label">Total Projects</div>
                    <div class="stat-change positive">
                        <i class="fas fa-arrow-up"></i>
                        <?php echo $projectsData['new_this_week']; ?> this week
                    </div>
                </div>
                
                <div class="stat-card">
                    <div class="stat-icon" style="background-color: var(--secondary);">
                        <i class="fas fa-envelope"></i>
                    </div>
                    <div class="stat-value"><?php echo $messagesData['total']; ?></div>
                    <div class="stat-label">Total Messages</div>
                    <div class="stat-change positive">
                        <i class="fas fa-arrow-up"></i>
                        <?php echo $messagesData['new_this_week']; ?> this week
                    </div>
                </div>
                
                <div class="stat-card">
                    <div class="stat-icon" style="background-color: var(--danger);">
                        <i class="fas fa-code"></i>
                    </div>
                    <div class="stat-value"><?php echo $skillsCount; ?></div>
                    <div class="stat-label">Skills Listed</div>
                    <div class="stat-change">
                        <i class="fas fa-minus"></i>
                        Manage skills
                    </div>
                </div>
            </div>
            
            <!-- Dashboard Widgets -->
            <div class="dashboard-grid">
                <!-- Recent Activity -->
                <div class="dashboard-card">
                    <div class="card-header">
                        <h3>Recent Activity</h3>
                        <i class="fas fa-history"></i>
                    </div>
                    <div class="activity-feed">
                        <?php foreach ($recentActivity as $activity): ?>
                        <div class="activity-item">
                            <div class="activity-icon">
                                <i class="fas fa-<?php echo $activity['type'] === 'project' ? 'project-diagram' : 'envelope'; ?>"></i>
                            </div>
                            <div class="activity-content">
                                <h4 class="activity-title">
                                    <?php if ($activity['type'] === 'project'): ?>
                                    New project: <?php echo htmlspecialchars($activity['name']); ?>
                                    <?php else: ?>
                                    Message from <?php echo htmlspecialchars($activity['name']); ?>
                                    <?php endif; ?>
                                </h4>
                                <div class="activity-time">
                                    <?php echo date('M j, Y g:i a', strtotime($activity['created_at'])); ?>
                                </div>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    </div>
                </div>
                
                <!-- Recent Messages -->
                <div class="dashboard-card">
                    <div class="card-header">
                        <h3>Recent Messages</h3>
                        <i class="fas fa-inbox"></i>
                    </div>
                    <div class="messages-list">
                        <?php foreach ($recentMessages as $message): ?>
                        <div class="message-item <?php echo $message['is_read'] ? '' : 'unread'; ?>">
                            <div class="message-header">
                                <div class="message-name"><?php echo htmlspecialchars($message['name']); ?></div>
                                <div class="message-time">
                                    <?php echo date('M j, g:i a', strtotime($message['created_at'])); ?>
                                </div>
                            </div>
                            <div class="message-preview">
                                <?php echo htmlspecialchars(substr($message['message'], 0, 60) . '...'); ?>
                            </div>
                            <div class="message-actions">
                                <a href="view-message.php?id=<?php echo (int)$message['id']; ?>" class="btn btn-primary btn-sm">View</a>
                                <a href="reply-message.php?id=<?php echo (int)$message['id']; ?>" class="btn btn-secondary btn-sm">Reply</a>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>
            
            <!-- Charts Section -->
            <div class="dashboard-card">
                <div class="card-header">
                    <h3>Weekly Activity</h3>
                    <i class="fas fa-chart-line"></i>
                </div>
                <div class="chart-container">
                    <canvas id="activityChart"></canvas>
                </div>
            </div>
        </main>
    </div>

    <!-- JavaScript Libraries -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js@3.7.1/dist/chart.min.js"></script>
    <script>
        // Activity Chart
        const ctx = document.getElementById('activityChart').getContext('2d');
        const activityChart = new Chart(ctx, {
            type: 'bar',
            data: {
                labels: ['Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat', 'Sun'],
                datasets: [
                    {
                        label: 'Projects',
                        data: [3, 5, 2, 4, 6, 1, 0],
                        backgroundColor: 'rgba(52, 152, 219, 0.7)',
                        borderColor: 'rgba(52, 152, 219, 1)',
                        borderWidth: 1
                    },
                    {
                        label: 'Messages',
                        data: [4, 3, 6, 2, 5, 2, 1],
                        backgroundColor: 'rgba(46, 204, 113, 0.7)',
                        borderColor: 'rgba(46, 204, 113, 1)',
                        borderWidth: 1
                    }
                ]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                scales: {
                    y: {
                        beginAtZero: true
                    }
                },
                plugins: {
                    legend: {
                        position: 'top',
                    },
                    tooltip: {
                        mode: 'index',
                        intersect: false,
                    }
                }
            }
        });

        // Confirm before destructive actions
        document.querySelectorAll('.btn-danger').forEach(btn => {
            btn.addEventListener('click', (e) => {
                if (!confirm('Are you sure you want to delete this?')) {
                    e.preventDefault();
                }
            });
        });
    </script>
</body>
</html>