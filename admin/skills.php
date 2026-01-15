
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
    
    $action = $_GET['action'] ?? 'list';
    $message = $_GET['message'] ?? '';
    $error = $_GET['error'] ?? '';
    
    switch ($action) {
        case 'create':
        case 'edit':
            if ($_SERVER['REQUEST_METHOD'] === 'POST') {
                $name = trim($_POST['name']);
                $percentage = intval($_POST['percentage']);
                $category = trim($_POST['category']);
                $color = trim($_POST['color']);
                $icon = trim($_POST['icon']);
                $sort_order = intval($_POST['sort_order']);
                
                if ($action === 'create') {
                    $stmt = $pdo->prepare("
                        INSERT INTO skills (name, percentage, category, color, icon, sort_order)
                        VALUES (?, ?, ?, ?, ?, ?)
                    ");
                    $stmt->execute([$name, $percentage, $category, $color, $icon, $sort_order]);
                    header("Location: skills.php?message=Skill+created+successfully");
                } else {
                    $id = $_POST['id'];
                    $stmt = $pdo->prepare("
                        UPDATE skills 
                        SET name = ?, percentage = ?, category = ?, color = ?, icon = ?, sort_order = ?
                        WHERE id = ?
                    ");
                    $stmt->execute([$name, $percentage, $category, $color, $icon, $sort_order, $id]);
                    header("Location: skills.php?message=Skill+updated+successfully");
                }
                exit;
            }
            
            $skill = [];
            if ($action === 'edit' && isset($_GET['id'])) {
                $stmt = $pdo->prepare("SELECT * FROM skills WHERE id = ?");
                $stmt->execute([$_GET['id']]);
                $skill = $stmt->fetch();
                if (!$skill) {
                    header("Location: skills.php?error=Skill+not+found");
                    exit;
                }
            }
            
            include 'includes/header.php';
            ?>
            <div class="admin-container">
                <?php include 'includes/sidebar.php'; ?>
                <main class="admin-main">
                    <header class="admin-header">
                        <h1><?php echo $action === 'create' ? 'Add New Skill' : 'Edit Skill'; ?></h1>
                        <a href="skills.php" class="btn btn-outline">
                            <i class="fas fa-arrow-left"></i> Back to Skills
                        </a>
                    </header>
                    
                    <?php if ($message): ?>
                        <div class="alert alert-success"><?php echo htmlspecialchars($message); ?></div>
                    <?php endif; ?>
                    
                    <?php if ($error): ?>
                        <div class="alert alert-danger"><?php echo htmlspecialchars($error); ?></div>
                    <?php endif; ?>
                    
                    <form method="POST" class="form-card">
                        <?php if ($action === 'edit'): ?>
                            <input type="hidden" name="id" value="<?php echo $skill['id']; ?>">
                        <?php endif; ?>
                        
                        <div class="form-row">
                            <div class="form-group">
                                <label for="name">Skill Name *</label>
                                <input type="text" id="name" name="name" required 
                                       value="<?php echo htmlspecialchars($skill['name'] ?? ''); ?>"
                                       placeholder="e.g., PHP, JavaScript, Photoshop">
                            </div>
                            
                            <div class="form-group">
                                <label for="percentage">Percentage (0-100) *</label>
                                <input type="number" id="percentage" name="percentage" min="0" max="100" required
                                       value="<?php echo $skill['percentage'] ?? 80; ?>">
                                <div class="percentage-preview">
                                    <div class="progress-bar">
                                        <div class="progress-fill" style="width: <?php echo $skill['percentage'] ?? 80; ?>%; background-color: <?php echo $skill['color'] ?? '#667eea'; ?>"></div>
                                    </div>
                                    <span><?php echo $skill['percentage'] ?? 80; ?>%</span>
                                </div>
                            </div>
                        </div>
                        
                        <div class="form-row">
                            <div class="form-group">
                                <label for="category">Category</label>
                                <input type="text" id="category" name="category"
                                       value="<?php echo htmlspecialchars($skill['category'] ?? ''); ?>"
                                       placeholder="e.g., Programming, Design, Tools">
                            </div>
                            
                            <div class="form-group">
                                <label for="color">Color</label>
                                <input type="color" id="color" name="color"
                                       value="<?php echo $skill['color'] ?? '#667eea'; ?>">
                            </div>
                        </div>
                        
                        <div class="form-row">
                            <div class="form-group">
                                <label for="icon">Icon Class (Font Awesome)</label>
                                <input type="text" id="icon" name="icon"
                                       value="<?php echo htmlspecialchars($skill['icon'] ?? 'fas fa-code'); ?>"
                                       placeholder="fas fa-code">
                                <small class="help-text">e.g., fab fa-js-square, fas fa-database</small>
                            </div>
                            
                            <div class="form-group">
                                <label for="sort_order">Sort Order</label>
                                <input type="number" id="sort_order" name="sort_order"
                                       value="<?php echo $skill['sort_order'] ?? 0; ?>">
                            </div>
                        </div>
                        
                        <div class="form-actions">
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save"></i> Save Skill
                            </button>
                            <a href="skills.php" class="btn btn-outline">Cancel</a>
                        </div>
                    </form>
                    
                    <script>
                        // Update preview when percentage changes
                        document.getElementById('percentage').addEventListener('input', function() {
                            const preview = document.querySelector('.progress-fill');
                            const value = this.value;
                            preview.style.width = value + '%';
                            preview.nextElementSibling.textContent = value + '%';
                        });
                        
                        // Update preview when color changes
                        document.getElementById('color').addEventListener('input', function() {
                            const preview = document.querySelector('.progress-fill');
                            preview.style.backgroundColor = this.value;
                        });
                    </script>
                </main>
            </div>
            <?php
            include 'includes/footer.php';
            break;
            
        case 'delete':
            if (isset($_GET['id'])) {
                $stmt = $pdo->prepare("DELETE FROM skills WHERE id = ?");
                $stmt->execute([$_GET['id']]);
                header("Location: skills.php?message=Skill+deleted+successfully");
                exit;
            }
            break;
            
        default:
            // List all skills
            $stmt = $pdo->query("SELECT * FROM skills ORDER BY sort_order, name");
            $skills = $stmt->fetchAll();
            
            include 'includes/header.php';
            ?>
            <div class="admin-container">
                <?php include 'includes/sidebar.php'; ?>
                <main class="admin-main">
                    <header class="admin-header">
                        <h1>Manage Skills (<?php echo count($skills); ?>)</h1>
                        <a href="skills.php?action=create" class="btn btn-primary">
                            <i class="fas fa-plus"></i> Add New Skill
                        </a>
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
                    
                    <?php if (empty($skills)): ?>
                        <div class="card">
                            <div class="empty-state">
                                <i class="fas fa-chart-bar fa-3x"></i>
                                <h3>No skills added yet</h3>
                                <p>Add your technical skills and proficiency levels here.</p>
                                <a href="skills.php?action=create" class="btn btn-primary">
                                    <i class="fas fa-plus"></i> Add Your First Skill
                                </a>
                            </div>
                        </div>
                    <?php else: ?>
                        <div class="card">
                            <div class="skills-grid">
                                <?php foreach ($skills as $skill): ?>
                                <div class="skill-card">
                                    <div class="skill-header">
                                        <div class="skill-icon" style="color: <?php echo $skill['color']; ?>">
                                            <i class="<?php echo $skill['icon']; ?>"></i>
                                        </div>
                                        <div class="skill-info">
                                            <h3><?php echo htmlspecialchars($skill['name']); ?></h3>
                                            <span class="skill-category"><?php echo htmlspecialchars($skill['category']); ?></span>
                                        </div>
                                        <div class="skill-actions">
                                            <a href="skills.php?action=edit&id=<?php echo $skill['id']; ?>" class="btn btn-sm btn-outline">
                                                <i class="fas fa-edit"></i>
                                            </a>
                                            <a href="skills.php?action=delete&id=<?php echo $skill['id']; ?>" 
                                               class="btn btn-sm btn-danger"
                                               onclick="return confirm('Delete this skill?')">
                                                <i class="fas fa-trash"></i>
                                            </a>
                                        </div>
                                    </div>
                                    <div class="skill-progress">
                                        <div class="progress-bar">
                                            <div class="progress-fill" style="width: <?php echo $skill['percentage']; ?>%; background-color: <?php echo $skill['color']; ?>"></div>
                                        </div>
                                        <span class="skill-percentage"><?php echo $skill['percentage']; ?>%</span>
                                    </div>
                                </div>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    <?php endif; ?>
                </main>
            </div>
            <?php
            include 'includes/footer.php';
    }
    
} catch (PDOException $e) {
    die("Database error: " . $e->getMessage());
}
?>