
<?php
session_start();
require_once 'includes/config.php';

if (!isset($_SESSION['admin_logged_in'])) {
    header("Location: login.php");
    exit;
}


if (isset($_SESSION['login_time']) && (time() - $_SESSION['login_time'] > 1800)) {
    session_destroy();
    header("Location: login.php?session=expired");
    exit;
}


$_SESSION['login_time'] = time();


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
    
   
    $stats = [];
    
    
    $projectsQuery = $pdo->query("
        SELECT 
            COUNT(*) as total,
            SUM(CASE WHEN created_at >= DATE_SUB(NOW(), INTERVAL 7 DAY) THEN 1 ELSE 0 END) as new_this_week,
            SUM(featured = 1) as featured
        FROM projects
    ");
    $stats['projects'] = $projectsQuery->fetch() ?? ['total' => 0, 'new_this_week' => 0, 'featured' => 0];
    
  
    $messagesQuery = $pdo->query("
        SELECT 
            COUNT(*) as total,
            SUM(read_status = 0) as unread,
            SUM(CASE WHEN created_at >= DATE_SUB(NOW(), INTERVAL 7 DAY) THEN 1 ELSE 0 END) as new_this_week
        FROM messages
    ");
    $stats['messages'] = $messagesQuery->fetch() ?? ['total' => 0, 'unread' => 0, 'new_this_week' => 0];
    
   
    $skillsQuery = $pdo->query("SELECT COUNT(*) as total FROM skills");
    $stats['skills'] = $skillsQuery->fetch() ?? ['total' => 0];
    
   
    $recentProjects = $pdo->query("
        SELECT id, title, created_at 
        FROM projects 
        ORDER BY created_at DESC 
        LIMIT 5
    ")->fetchAll();
    
   
    $recentMessages = $pdo->query("
        SELECT id, name, email, message, created_at, read_status 
        FROM messages 
        ORDER BY created_at DESC 
        LIMIT 5
    ")->fetchAll();
    
  
    $adminName = $_SESSION['admin_name'] ?? 'Administrator';
    $adminEmail = 'admin@' . strtolower(str_replace(' ', '', SITE_TITLE)) . '.com';

} catch (PDOException $e) {
  
    if ($e->getCode() == '42S02') { 
        die("
            <div style='padding: 50px; text-align: center; font-family: sans-serif;'>
                <h2>Setup Required</h2>
                <p>Database tables are not created yet. Please run the setup.</p>
                <p><a href='setup.php'>Run Setup</a> | <a href='login.php'>Return to Login</a></p>
            </div>
        ");
    } else {
        error_log("Dashboard Error: " . $e->getMessage());
        die("
            <div style='padding: 50px; text-align: center; font-family: sans-serif;'>
                <h2>Database Error</h2>
                <p>Please check your database connection and table structure.</p>
                <p>Error: " . htmlspecialchars($e->getMessage()) . "</p>
                <p><a href='login.php'>Return to Login</a></p>
            </div>
        ");
    }
}


function timeAgo($datetime) {
    $time = strtotime($datetime);
    $now = time();
    $diff = $now - $time;
    
    if ($diff < 60) return 'Just now';
    if ($diff < 3600) return floor($diff/60) . ' minutes ago';
    if ($diff < 86400) return floor($diff/3600) . ' hours ago';
    if ($diff < 604800) return floor($diff/86400) . ' days ago';
    return date('M j, Y', $time);
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
            --primary: #667eea;
            --secondary: #48bb78;
            --danger: #f56565;
            --warning: #ed8936;
            --dark: #0f172b;
            --light: #f8fafc;
            --sidebar-width: 250px;
        }
        
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'Segoe UI', Roboto, sans-serif;
            background: var(--light);
            color: var(--dark);
            min-height: 100vh;
        }
        
       
        .admin-container {
            display: flex;
            min-height: 100vh;
        }
        
       
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
            height: 70px;
            display: flex;
            align-items: center;
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
            display: flex;
            align-items: center;
            padding: 12px 20px;
            color: rgba(255,255,255,0.8);
            text-decoration: none;
            transition: all 0.3s;
            border-left: 3px solid transparent;
        }
        
        .admin-menu a:hover {
            background: rgba(255,255,255,0.1);
            color: white;
        }
        
        .admin-menu a.active {
            background: rgba(102, 126, 234, 0.1);
            color: white;
            border-left: 3px solid var(--primary);
        }
        
        .admin-menu a i {
            margin-right: 12px;
            width: 20px;
            text-align: center;
            font-size: 1.1rem;
        }
        
        .badge {
            background: var(--danger);
            color: white;
            border-radius: 50%;
            width: 20px;
            height: 20px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            font-size: 0.7rem;
            margin-left: auto;
        }
        
      
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
            border-bottom: 1px solid #e2e8f0;
        }
        
        .admin-header h1 {
            font-size: 1.8rem;
            color: var(--dark);
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
            background: linear-gradient(135deg, var(--primary), #764ba2);
            color: white;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: bold;
            font-size: 1.2rem;
        }
        
        
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 25px;
            margin-bottom: 40px;
        }
        
        .stat-card {
            background: white;
            border-radius: 12px;
            padding: 25px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.05);
            transition: transform 0.3s;
            border-left: 4px solid var(--primary);
        }
        
        .stat-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 25px rgba(0,0,0,0.1);
        }
        
        .stat-icon {
            width: 60px;
            height: 60px;
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-size: 1.5rem;
            margin-bottom: 15px;
            background: linear-gradient(135deg, var(--primary), #764ba2);
        }
        
        .stat-value {
            font-size: 2rem;
            font-weight: 700;
            margin: 5px 0;
            color: var(--dark);
        }
        
        .stat-label {
            color: #64748b;
            font-size: 0.95rem;
            margin-bottom: 10px;
        }
        
        .stat-change {
            display: flex;
            align-items: center;
            gap: 5px;
            font-size: 0.85rem;
        }
        
        .stat-change.positive {
            color: var(--secondary);
        }
        
       
        .dashboard-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 25px;
            margin-bottom: 40px;
        }
        
        .dashboard-card {
            background: white;
            border-radius: 12px;
            padding: 25px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.05);
        }
        
        .card-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
            padding-bottom: 15px;
            border-bottom: 1px solid #e2e8f0;
        }
        
        .card-header h3 {
            margin: 0;
            font-size: 1.2rem;
            color: var(--dark);
        }
        
        .card-header i {
            color: var(--primary);
            font-size: 1.2rem;
        }
        
        
        .activity-item {
            display: flex;
            padding: 15px 0;
            border-bottom: 1px solid #e2e8f0;
        }
        
        .activity-item:last-child {
            border-bottom: none;
        }
        
        .activity-icon {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            background: #e2e8f0;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-right: 15px;
            color: var(--primary);
            font-size: 1rem;
        }
        
        .activity-content h4 {
            margin: 0 0 5px 0;
            font-size: 0.95rem;
            font-weight: 600;
        }
        
        .activity-time {
            color: #94a3b8;
            font-size: 0.85rem;
        }
        
     
        .message-item {
            padding: 15px 0;
            border-bottom: 1px solid #e2e8f0;
        }
        
        .message-item:last-child {
            border-bottom: none;
        }
        
        .message-item.unread {
            background: #f8fafc;
            margin: -15px -25px;
            padding: 15px 25px;
            border-left: 3px solid var(--primary);
        }
        
        .message-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 8px;
        }
        
        .message-name {
            font-weight: 600;
            color: var(--dark);
        }
        
        .message-time {
            color: #94a3b8;
            font-size: 0.85rem;
        }
        
        .message-preview {
            color: #64748b;
            margin-bottom: 10px;
            font-size: 0.9rem;
        }
        
        .message-actions {
            display: flex;
            gap: 10px;
        }
        
      
        .btn {
            padding: 8px 16px;
            border-radius: 6px;
            font-size: 0.9rem;
            cursor: pointer;
            transition: all 0.3s;
            border: none;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 5px;
        }
        
        .btn-sm {
            padding: 6px 12px;
            font-size: 0.85rem;
        }
        
        .btn-primary {
            background: var(--primary);
            color: white;
        }
        
        .btn-secondary {
            background: var(--secondary);
            color: white;
        }
        
        .btn-outline {
            background: transparent;
            border: 1px solid var(--primary);
            color: var(--primary);
        }
        
        .btn:hover {
            opacity: 0.9;
            transform: translateY(-2px);
        }
        
        /* System Status */
        .system-status {
            display: flex;
            gap: 15px;
            margin-bottom: 25px;
            flex-wrap: wrap;
        }
        
        .status-item {
            display: flex;
            align-items: center;
            gap: 8px;
            background: white;
            padding: 10px 15px;
            border-radius: 8px;
            box-shadow: 0 2px 5px rgba(0,0,0,0.05);
        }
        
        .status-item i.online {
            color: var(--secondary);
        }
        
     
        .chart-container {
            position: relative;
            height: 250px;
            width: 100%;
        }
        
      
        .empty-state {
            text-align: center;
            padding: 40px 20px;
            color: #94a3b8;
        }
        
        .empty-state i {
            font-size: 3rem;
            margin-bottom: 15px;
            opacity: 0.5;
        }
        
        
        @media (max-width: 1024px) {
            .admin-sidebar {
                width: 70px;
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
            
            .stats-grid {
                grid-template-columns: repeat(2, 1fr);
            }
        }
        
        @media (max-width: 768px) {
            .admin-main {
                padding: 20px;
            }
            
            .stats-grid {
                grid-template-columns: 1fr;
            }
            
            .dashboard-grid {
                grid-template-columns: 1fr;
            }
            
            .admin-header {
                flex-direction: column;
                align-items: flex-start;
                gap: 15px;
            }
        }
    </style>
</head>
<body>
    <div class="admin-container">
      
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
                    <span class="badge"><?php echo $stats['projects']['new_this_week']; ?></span>
                </a>
                <a href="messages.php">
                    <i class="fas fa-envelope"></i>
                    <span>Messages</span>
                    <?php if ($stats['messages']['unread'] > 0): ?>
                    <span class="badge"><?php echo $stats['messages']['unread']; ?></span>
                    <?php endif; ?>
                </a>
                <a href="skills.php">
                    <i class="fas fa-code"></i>
                    <span>Skills</span>
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
        
      
        <main class="admin-main">
           
            <header class="admin-header">
                <h1>Dashboard Overview</h1>
                <div class="admin-user">
                    <div class="user-avatar">
                        <?php echo strtoupper(substr($adminName, 0, 1)); ?>
                    </div>
                    <div>
                        <div style="font-weight: 600;"><?php echo htmlspecialchars($adminName); ?></div>
                        <div style="font-size: 0.85rem; color: #64748b;">Administrator</div>
                    </div>
                </div>
            </header>
            
           
            <div class="system-status">
                <div class="status-item">
                    <i class="fas fa-circle online"></i>
                    <span>System Online</span>
                </div>
                <div class="status-item">
                    <i class="fas fa-database"></i>
                    <span>Database Connected</span>
                </div>
                <div class="status-item">
                    <i class="fas fa-server"></i>
                    <span>PHP <?php echo PHP_VERSION; ?></span>
                </div>
            </div>
            
          
            <div class="stats-grid">
                <div class="stat-card">
                    <div class="stat-icon">
                        <i class="fas fa-project-diagram"></i>
                    </div>
                    <div class="stat-value"><?php echo $stats['projects']['total']; ?></div>
                    <div class="stat-label">Total Projects</div>
                    <div class="stat-change <?php echo $stats['projects']['new_this_week'] > 0 ? 'positive' : ''; ?>">
                        <?php if ($stats['projects']['new_this_week'] > 0): ?>
                        <i class="fas fa-arrow-up"></i>
                        <?php echo $stats['projects']['new_this_week']; ?> new this week
                        <?php else: ?>
                        <i class="fas fa-minus"></i>
                        No new projects this week
                        <?php endif; ?>
                    </div>
                </div>
                
                <div class="stat-card">
                    <div class="stat-icon">
                        <i class="fas fa-envelope"></i>
                    </div>
                    <div class="stat-value"><?php echo $stats['messages']['total']; ?></div>
                    <div class="stat-label">Total Messages</div>
                    <div class="stat-change <?php echo $stats['messages']['new_this_week'] > 0 ? 'positive' : ''; ?>">
                        <?php if ($stats['messages']['new_this_week'] > 0): ?>
                        <i class="fas fa-arrow-up"></i>
                        <?php echo $stats['messages']['new_this_week']; ?> new this week
                        <?php else: ?>
                        <i class="fas fa-minus"></i>
                        No new messages this week
                        <?php endif; ?>
                    </div>
                </div>
                
                <div class="stat-card">
                    <div class="stat-icon">
                        <i class="fas fa-code"></i>
                    </div>
                    <div class="stat-value"><?php echo $stats['skills']['total']; ?></div>
                    <div class="stat-label">Skills Listed</div>
                    <div class="stat-change">
                        <i class="fas fa-edit"></i>
                        <span>Manage in Skills section</span>
                    </div>
                </div>
            </div>
            
         
            <div class="dashboard-grid">
               
                <div class="dashboard-card">
                    <div class="card-header">
                        <h3>Recent Projects</h3>
                        <i class="fas fa-project-diagram"></i>
                    </div>
                    <div class="card-body">
                        <?php if (empty($recentProjects)): ?>
                            <div class="empty-state">
                                <i class="fas fa-folder-open"></i>
                                <p>No projects found</p>
                                <a href="projects.php?action=create" class="btn btn-primary">Add First Project</a>
                            </div>
                        <?php else: ?>
                            <div class="activity-feed">
                                <?php foreach ($recentProjects as $project): ?>
                                <div class="activity-item">
                                    <div class="activity-icon">
                                        <i class="fas fa-file-code"></i>
                                    </div>
                                    <div class="activity-content">
                                        <h4><?php echo htmlspecialchars($project['title']); ?></h4>
                                        <div class="activity-time">
                                            <?php echo timeAgo($project['created_at']); ?>
                                        </div>
                                    </div>
                                </div>
                                <?php endforeach; ?>
                                <div style="text-align: center; margin-top: 15px;">
                                    <a href="projects.php" class="btn btn-outline">View All Projects</a>
                                </div>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
                
               
                <div class="dashboard-card">
                    <div class="card-header">
                        <h3>Recent Messages</h3>
                        <i class="fas fa-inbox"></i>
                    </div>
                    <div class="card-body">
                        <?php if (empty($recentMessages)): ?>
                            <div class="empty-state">
                                <i class="fas fa-comments"></i>
                                <p>No messages yet</p>
                                <p>Messages will appear here when users contact you.</p>
                            </div>
                        <?php else: ?>
                            <div class="messages-list">
                                <?php foreach ($recentMessages as $message): ?>
                                <div class="message-item <?php echo $message['read_status'] == 0 ? 'unread' : ''; ?>">
                                    <div class="message-header">
                                        <div class="message-name"><?php echo htmlspecialchars($message['name']); ?></div>
                                        <div class="message-time">
                                            <?php echo timeAgo($message['created_at']); ?>
                                        </div>
                                    </div>
                                    <div class="message-preview">
                                        <?php echo htmlspecialchars(substr($message['message'], 0, 80) . '...'); ?>
                                    </div>
                                    <div class="message-actions">
                                        <a href="view-message.php?id=<?php echo $message['id']; ?>" class="btn btn-primary btn-sm">
                                            <i class="fas fa-eye"></i> View
                                        </a>
                                        <a href="mailto:<?php echo htmlspecialchars($message['email']); ?>" class="btn btn-secondary btn-sm">
                                            <i class="fas fa-reply"></i> Reply
                                        </a>
                                    </div>
                                </div>
                                <?php endforeach; ?>
                                <div style="text-align: center; margin-top: 15px;">
                                    <a href="messages.php" class="btn btn-outline">View All Messages</a>
                                </div>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
            
         
            <div class="dashboard-card">
                <div class="card-header">
                    <h3>Weekly Activity</h3>
                    <i class="fas fa-chart-line"></i>
                </div>
                <div class="chart-container">
                    <canvas id="activityChart"></canvas>
                </div>
            </div>
            
           
            <div class="dashboard-card">
                <div class="card-header">
                    <h3>Quick Actions</h3>
                    <i class="fas fa-bolt"></i>
                </div>
                <div class="card-body" style="display: grid; grid-template-columns: repeat(auto-fit, minmax(150px, 1fr)); gap: 15px;">
                    <a href="projects.php?action=create" class="btn btn-primary" style="justify-content: center;">
                        <i class="fas fa-plus"></i> Add Project
                    </a>
                    <a href="skills.php?action=create" class="btn btn-secondary" style="justify-content: center;">
                        <i class="fas fa-plus"></i> Add Skill
                    </a>
                    <a href="messages.php" class="btn btn-outline" style="justify-content: center;">
                        <i class="fas fa-inbox"></i> View Messages
                    </a>
                    <a href="settings.php" class="btn btn-outline" style="justify-content: center;">
                        <i class="fas fa-cog"></i> Settings
                    </a>
                </div>
            </div>
            
        </main>
    </div>

 
    <script src="https://cdn.jsdelivr.net/npm/chart.js@3.7.1/dist/chart.min.js"></script>
    <script>
    
        document.addEventListener('DOMContentLoaded', function() {
            const ctx = document.getElementById('activityChart').getContext('2d');
            const activityChart = new Chart(ctx, {
                type: 'bar',
                data: {
                    labels: ['Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat', 'Sun'],
                    datasets: [
                        {
                            label: 'Projects',
                            data: [3, 5, 2, 4, 6, 1, 0],
                            backgroundColor: 'rgba(102, 126, 234, 0.7)',
                            borderColor: 'rgba(102, 126, 234, 1)',
                            borderWidth: 1
                        },
                        {
                            label: 'Messages',
                            data: [4, 3, 6, 2, 5, 2, 1],
                            backgroundColor: 'rgba(72, 187, 120, 0.7)',
                            borderColor: 'rgba(72, 187, 120, 1)',
                            borderWidth: 1
                        }
                    ]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    scales: {
                        y: {
                            beginAtZero: true,
                            ticks: {
                                stepSize: 1
                            }
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

           
            setInterval(() => {
                fetch(window.location.href)
                    .then(response => response.text())
                    .then(html => {
                    
                        console.log('Dashboard auto-refreshed at ' + new Date().toLocaleTimeString());
                    })
                    .catch(error => console.log('Auto-refresh failed:', error));
            }, 30000);

           
            document.querySelectorAll('.message-item a[href*="view-message"]').forEach(link => {
                link.addEventListener('click', function(e) {
                    const messageItem = this.closest('.message-item');
                    if (messageItem.classList.contains('unread')) {
                        messageItem.classList.remove('unread');
                    
                    }
                });
            });

           
            let warningTimeout = setTimeout(() => {
                if (confirm('Your session will expire in 5 minutes. Do you want to stay logged in?')) {
                
                    fetch('keepalive.php').catch(() => {});
                    clearTimeout(warningTimeout);
                    warningTimeout = setTimeout(() => {
                        alert('Session expired. Please log in again.');
                        window.location.href = 'logout.php?session=expired';
                    }, 25 * 60 * 1000); // 25 minutes
                } else {
                    window.location.href = 'logout.php';
                }
            }, 25 * 60 * 1000); 

        
            document.addEventListener('keydown', (e) => {
              
                if (e.ctrlKey && e.key === 'p') {
                    e.preventDefault();
                    window.location.href = 'projects.php';
                }
               
                if (e.ctrlKey && e.key === 'm') {
                    e.preventDefault();
                    window.location.href = 'messages.php';
                }
               
                if (e.ctrlKey && e.key === 's') {
                    e.preventDefault();
                    window.location.href = 'settings.php';
                }
              
                if (e.ctrlKey && e.key === 'l') {
                    e.preventDefault();
                    if (confirm('Are you sure you want to logout?')) {
                        window.location.href = 'logout.php';
                    }
                }
            });
        });
    </script>
</body>
</html>