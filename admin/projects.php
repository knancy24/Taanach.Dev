
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
    
    // Handle different actions
    switch ($action) {
        case 'create':
        case 'edit':
            // Handle form submission
            if ($_SERVER['REQUEST_METHOD'] === 'POST') {
                $title = trim($_POST['title']);
                $description = trim($_POST['description']);
                $technologies = trim($_POST['technologies']);
                $github_url = trim($_POST['github_url']);
                $live_url = trim($_POST['live_url']);
                $featured = isset($_POST['featured']) ? 1 : 0;
                $category = trim($_POST['category']);
                
                if ($action === 'create') {
                    $stmt = $pdo->prepare("
                        INSERT INTO projects (title, description, technologies, github_url, live_url, featured, category)
                        VALUES (?, ?, ?, ?, ?, ?, ?)
                    ");
                    $stmt->execute([$title, $description, $technologies, $github_url, $live_url, $featured, $category]);
                    header("Location: projects.php?message=Project+created+successfully");
                } else {
                    $id = $_POST['id'];
                    $stmt = $pdo->prepare("
                        UPDATE projects 
                        SET title = ?, description = ?, technologies = ?, github_url = ?, live_url = ?, featured = ?, category = ?
                        WHERE id = ?
                    ");
                    $stmt->execute([$title, $description, $technologies, $github_url, $live_url, $featured, $category, $id]);
                    header("Location: projects.php?message=Project+updated+successfully");
                }
                exit;
            }
            
            $project = [];
            if ($action === 'edit' && isset($_GET['id'])) {
                $stmt = $pdo->prepare("SELECT * FROM projects WHERE id = ?");
                $stmt->execute([$_GET['id']]);
                $project = $stmt->fetch();
                if (!$project) {
                    header("Location: projects.php?error=Project+not+found");
                    exit;
                }
            }
            
            // Show form
            include 'includes/header.php';
            ?>
            <div class="admin-container">
                <?php include 'includes/sidebar.php'; ?>
                <main class="admin-main">
                    <header class="admin-header">
                        <h1><?php echo $action === 'create' ? 'Add New Project' : 'Edit Project'; ?></h1>
                        <a href="projects.php" class="btn btn-outline">
                            <i class="fas fa-arrow-left"></i> Back to Projects
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
                            <input type="hidden" name="id" value="<?php echo $project['id']; ?>">
                        <?php endif; ?>
                        
                        <div class="form-group">
                            <label for="title">Project Title *</label>
                            <input type="text" id="title" name="title" required 
                                   value="<?php echo htmlspecialchars($project['title'] ?? ''); ?>">
                        </div>
                        
                        <div class="form-group">
                            <label for="description">Description *</label>
                            <textarea id="description" name="description" rows="6" required><?php echo htmlspecialchars($project['description'] ?? ''); ?></textarea>
                        </div>
                        
                        <div class="form-row">
                            <div class="form-group">
                                <label for="technologies">Technologies (comma separated)</label>
                                <input type="text" id="technologies" name="technologies"
                                       value="<?php echo htmlspecialchars($project['technologies'] ?? ''); ?>"
                                       placeholder="HTML, CSS, PHP, JavaScript">
                            </div>
                            
                            <div class="form-group">
                                <label for="category">Category</label>
                                <input type="text" id="category" name="category"
                                       value="<?php echo htmlspecialchars($project['category'] ?? ''); ?>"
                                       placeholder="Web Development, Mobile App, etc.">
                            </div>
                        </div>
                        
                        <div class="form-row">
                            <div class="form-group">
                                <label for="github_url">GitHub URL</label>
                                <input type="url" id="github_url" name="github_url"
                                       value="<?php echo htmlspecialchars($project['github_url'] ?? ''); ?>">
                            </div>
                            
                            <div class="form-group">
                                <label for="live_url">Live Demo URL</label>
                                <input type="url" id="live_url" name="live_url"
                                       value="<?php echo htmlspecialchars($project['live_url'] ?? ''); ?>">
                            </div>
                        </div>
                        
                        <div class="form-group">
                            <label class="checkbox-label">
                                <input type="checkbox" name="featured" value="1" 
                                    <?php echo isset($project['featured']) && $project['featured'] ? 'checked' : ''; ?>>
                                <span>Featured Project</span>
                            </label>
                        </div>
                        
                        <div class="form-actions">
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save"></i> Save Project
                            </button>
                            <a href="projects.php" class="btn btn-outline">Cancel</a>
                        </div>
                    </form>
                </main>
            </div>
            <?php
            include 'includes/footer.php';
            break;
            
        case 'delete':
            if (isset($_GET['id'])) {
                $stmt = $pdo->prepare("DELETE FROM projects WHERE id = ?");
                $stmt->execute([$_GET['id']]);
                header("Location: projects.php?message=Project+deleted+successfully");
                exit;
            }
            break;
            
        default:
            // List all projects
            $search = $_GET['search'] ?? '';
            $page = max(1, intval($_GET['page'] ?? 1));
            $limit = 10;
            $offset = ($page - 1) * $limit;
            
            $where = [];
            $params = [];
            
            if ($search) {
                $where[] = "(title LIKE ? OR description LIKE ? OR technologies LIKE ?)";
                $searchTerm = "%$search%";
                $params = [$searchTerm, $searchTerm, $searchTerm];
            }
            
            $whereClause = $where ? 'WHERE ' . implode(' AND ', $where) : '';
            
            // Get total count
            $countStmt = $pdo->prepare("SELECT COUNT(*) FROM projects $whereClause");
            $countStmt->execute($params);
            $total = $countStmt->fetchColumn();
            $totalPages = ceil($total / $limit);
            
            // Get projects
            $stmt = $pdo->prepare("
                SELECT * FROM projects 
                $whereClause 
                ORDER BY created_at DESC 
                LIMIT $limit OFFSET $offset
            ");
            $stmt->execute($params);
            $projects = $stmt->fetchAll();
            
            include 'includes/header.php';
            ?>
            <div class="admin-container">
                <?php include 'includes/sidebar.php'; ?>
                <main class="admin-main">
                    <header class="admin-header">
                        <h1>Manage Projects</h1>
                        <a href="projects.php?action=create" class="btn btn-primary">
                            <i class="fas fa-plus"></i> Add New Project
                        </a>
                    </header>
                    
                    <?php if ($message): ?>
                        <div class="alert alert-success">
                            <i class="fas fa-check-circle"></i> <?php echo htmlspecialchars($message); ?>
                        </div>
                    <?php endif; ?>
                    
                    <div class="card">
                        <div class="card-header">
                            <h2>All Projects (<?php echo $total; ?>)</h2>
                            <form method="GET" class="search-form">
                                <input type="hidden" name="action" value="list">
                                <input type="text" name="search" placeholder="Search projects..." 
                                       value="<?php echo htmlspecialchars($search); ?>">
                                <button type="submit"><i class="fas fa-search"></i></button>
                            </form>
                        </div>
                        
                        <div class="table-responsive">
                            <table class="data-table">
                                <thead>
                                    <tr>
                                        <th>ID</th>
                                        <th>Title</th>
                                        <th>Category</th>
                                        <th>Technologies</th>
                                        <th>Featured</th>
                                        <th>Created</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if (empty($projects)): ?>
                                        <tr>
                                            <td colspan="7" class="text-center">
                                                No projects found. <a href="projects.php?action=create">Create your first project</a>
                                            </td>
                                        </tr>
                                    <?php else: ?>
                                        <?php foreach ($projects as $project): ?>
                                        <tr>
                                            <td>#<?php echo $project['id']; ?></td>
                                            <td>
                                                <strong><?php echo htmlspecialchars($project['title']); ?></strong>
                                                <?php if ($project['github_url']): ?>
                                                    <br><small><i class="fab fa-github"></i> <a href="<?php echo htmlspecialchars($project['github_url']); ?>" target="_blank">GitHub</a></small>
                                                <?php endif; ?>
                                            </td>
                                            <td><?php echo htmlspecialchars($project['category']); ?></td>
                                            <td><?php echo htmlspecialchars($project['technologies']); ?></td>
                                            <td>
                                                <?php if ($project['featured']): ?>
                                                    <span class="badge badge-success">Featured</span>
                                                <?php else: ?>
                                                    <span class="badge">Normal</span>
                                                <?php endif; ?>
                                            </td>
                                            <td><?php echo date('M d, Y', strtotime($project['created_at'])); ?></td>
                                            <td>
                                                <div class="action-buttons">
                                                    <a href="projects.php?action=edit&id=<?php echo $project['id']; ?>" class="btn btn-sm btn-outline">
                                                        <i class="fas fa-edit"></i> Edit
                                                    </a>
                                                    <a href="projects.php?action=delete&id=<?php echo $project['id']; ?>" 
                                                       class="btn btn-sm btn-danger" 
                                                       onclick="return confirm('Delete this project?')">
                                                        <i class="fas fa-trash"></i> Delete
                                                    </a>
                                                </div>
                                            </td>
                                        </tr>
                                        <?php endforeach; ?>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                        
                        <?php if ($totalPages > 1): ?>
                            <div class="pagination">
                                <?php if ($page > 1): ?>
                                    <a href="?action=list&page=<?php echo $page - 1; ?>&search=<?php echo urlencode($search); ?>">
                                        <i class="fas fa-chevron-left"></i> Previous
                                    </a>
                                <?php endif; ?>
                                
                                <span>Page <?php echo $page; ?> of <?php echo $totalPages; ?></span>
                                
                                <?php if ($page < $totalPages): ?>
                                    <a href="?action=list&page=<?php echo $page + 1; ?>&search=<?php echo urlencode($search); ?>">
                                        Next <i class="fas fa-chevron-right"></i>
                                    </a>
                                <?php endif; ?>
                            </div>
                        <?php endif; ?>
                    </div>
                </main>
            </div>
            <?php
            include 'includes/footer.php';
    }
    
} catch (PDOException $e) {
    die("Database error: " . $e->getMessage());
}
?>