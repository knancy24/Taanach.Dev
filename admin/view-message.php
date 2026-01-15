
<?php
session_start();
require_once 'includes/config.php';

if (!isset($_SESSION['admin_logged_in'])) {
    header("Location: login.php");
    exit;
}

$id = $_GET['id'] ?? 0;

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
    
    // Mark as read
    $stmt = $pdo->prepare("UPDATE messages SET read_status = 1, read_at = NOW() WHERE id = ?");
    $stmt->execute([$id]);
    
    // Get message
    $stmt = $pdo->prepare("SELECT * FROM messages WHERE id = ?");
    $stmt->execute([$id]);
    $message = $stmt->fetch();
    
    if (!$message) {
        header("Location: messages.php?error=Message+not+found");
        exit;
    }
    
} catch (PDOException $e) {
    die("Database error: " . $e->getMessage());
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>View Message | <?php echo htmlspecialchars(SITE_TITLE); ?></title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        :root {
            --primary: #667eea;
            --dark: #0f172b;
            --light: #f8fafc;
        }
        
        body {
            font-family: 'Segoe UI', Roboto, sans-serif;
            background: var(--light);
            color: var(--dark);
            margin: 0;
            padding: 20px;
        }
        
        .message-container {
            max-width: 800px;
            margin: 0 auto;
            background: white;
            border-radius: 12px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.05);
            overflow: hidden;
        }
        
        .message-header {
            background: var(--dark);
            color: white;
            padding: 25px 30px;
        }
        
        .message-header h1 {
            margin: 0 0 10px 0;
            font-size: 1.5rem;
        }
        
        .message-meta {
            display: flex;
            flex-wrap: wrap;
            gap: 20px;
            font-size: 0.9rem;
            opacity: 0.9;
        }
        
        .message-meta span {
            display: flex;
            align-items: center;
            gap: 8px;
        }
        
        .message-body {
            padding: 30px;
            line-height: 1.6;
            font-size: 1.1rem;
        }
        
        .message-actions {
            padding: 20px 30px;
            background: #f8f9fa;
            border-top: 1px solid #e2e8f0;
            display: flex;
            gap: 15px;
            flex-wrap: wrap;
        }
        
        .btn {
            padding: 10px 20px;
            border-radius: 6px;
            font-size: 0.95rem;
            cursor: pointer;
            transition: all 0.3s;
            border: none;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 8px;
        }
        
        .btn-primary {
            background: var(--primary);
            color: white;
        }
        
        .btn-outline {
            background: transparent;
            border: 1px solid var(--primary);
            color: var(--primary);
        }
        
        .btn-danger {
            background: #e74c3c;
            color: white;
            border: none;
        }
        
        .btn:hover {
            opacity: 0.9;
            transform: translateY(-2px);
        }
        
        .back-link {
            display: inline-block;
            margin-bottom: 20px;
            color: var(--primary);
            text-decoration: none;
            font-weight: 500;
        }
        
        .back-link:hover {
            text-decoration: underline;
        }
        
        @media (max-width: 768px) {
            body {
                padding: 10px;
            }
            
            .message-header, .message-body, .message-actions {
                padding: 20px;
            }
            
            .message-meta {
                flex-direction: column;
                gap: 10px;
            }
        }
    </style>
</head>
<body>
    <a href="messages.php" class="back-link">
        <i class="fas fa-arrow-left"></i> Back to Messages
    </a>
    
    <div class="message-container">
        <div class="message-header">
            <h1><?php echo htmlspecialchars($message['subject'] ?? 'No Subject'); ?></h1>
            <div class="message-meta">
                <span><i class="fas fa-user"></i> <?php echo htmlspecialchars($message['name']); ?></span>
                <span><i class="fas fa-envelope"></i> <?php echo htmlspecialchars($message['email']); ?></span>
                <span><i class="fas fa-phone"></i> <?php echo htmlspecialchars($message['phone'] ?? 'Not provided'); ?></span>
                <span><i class="fas fa-clock"></i> <?php echo date('F j, Y g:i a', strtotime($message['created_at'])); ?></span>
                <?php if ($message['read_at']): ?>
                    <span><i class="fas fa-eye"></i> Read: <?php echo date('M j, g:i a', strtotime($message['read_at'])); ?></span>
                <?php else: ?>
                    <span class="badge" style="background: #10b981; padding: 2px 8px; border-radius: 10px;">NEW</span>
                <?php endif; ?>
            </div>
        </div>
        
        <div class="message-body">
            <?php echo nl2br(htmlspecialchars($message['message'])); ?>
        </div>
        
        <div class="message-actions">
            <a href="mailto:<?php echo htmlspecialchars($message['email']); ?>?subject=Re: <?php echo urlencode($message['subject'] ?? ''); ?>" 
               class="btn btn-primary">
                <i class="fas fa-reply"></i> Reply via Email
            </a>
            <a href="messages.php?action=mark_unread&id=<?php echo $message['id']; ?>" class="btn btn-outline">
                <i class="fas fa-envelope"></i> Mark as Unread
            </a>
            <a href="messages.php?action=delete&id=<?php echo $message['id']; ?>" 
               class="btn btn-danger"
               onclick="return confirm('Delete this message?')">
                <i class="fas fa-trash"></i> Delete
            </a>
        </div>
    </div>
</body>
</html>