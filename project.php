<?php
require_once 'includes/config.php';

if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    header("Location: portfolio.php");
    exit();
}

$projectId = (int)$_GET['id'];

try {
    $stmt = $db->prepare("SELECT * FROM projects WHERE id = ?");
    $stmt->execute([$projectId]);
    $project = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$project) {
        header("Location: portfolio.php");
        exit();
    }
    
    $pageTitle = $project['title'];
    require_once 'includes/header.php';
?>

<section class="project-detail">
    <div class="container">
        <div class="project-header">
            <h1><?php echo $project['title']; ?></h1>
            <div class="project-meta">
                <span class="project-category"><?php echo $project['category'] ?? 'Web Development'; ?></span>
                <span class="project-date"><?php echo date('F Y', strtotime($project['created_at'])); ?></span>
            </div>
        </div>
        
        <div class="project-content">
            <div class="project-image-main">
                <img src="<?php echo $project['image_url']; ?>" alt="<?php echo $project['title']; ?>">
            </div>
            
            <div class="project-description">
                <h2>Project Overview</h2>
                <p><?php echo nl2br($project['description']); ?></p>
                
                <?php if (!empty($project['technologies'])): ?>
                <h3>Technologies Used</h3>
                <ul class="tech-list">
                    <?php 
                    $techs = explode(',', $project['technologies']);
                    foreach ($techs as $tech): 
                    ?>
                    <li><?php echo trim($tech); ?></li>
                    <?php endforeach; ?>
                </ul>
                <?php endif; ?>
                
                <div class="project-actions">
                    <?php if ($project['project_url']): ?>
                    <a href="<?php echo $project['project_url']; ?>" target="_blank" class="btn btn-primary">View Live Project</a>
                    <?php endif; ?>
                    <a href="portfolio.php" class="btn btn-secondary">Back to Portfolio</a>
                </div>
            </div>
        </div>
    </div>
</section>

<?php
} catch(PDOException $e) {
    // Handle error
    $pageTitle = "Error";
    require_once 'includes/header.php';
    echo '<div class="container"><div class="alert alert-danger">Error loading project details.</div></div>';
}

require_once 'includes/footer.php';
?>