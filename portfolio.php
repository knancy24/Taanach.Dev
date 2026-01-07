<?php
$pageTitle = "Portfolio";
require_once 'includes/config.php';
require_once 'includes/header.php';
?>

<section class="portfolio-section">
    <div class="container">
        <h2 class="section-title">My Portfolio</h2>
        <div class="portfolio-filter">
            <button class="filter-btn active" data-filter="all">All</button>
            <button class="filter-btn" data-filter="web">Web Development</button>
            <button class="filter-btn" data-filter="app">Applications</button>
            <button class="filter-btn" data-filter="design">Design</button>
        </div>
        
        <div class="portfolio-grid">
            <?php 
            $projects = getProjects();
            foreach ($projects as $project): 
            ?>
            <div class="portfolio-item" data-category="<?php echo strtolower($project['category'] ?? 'web'); ?>">
                <div class="portfolio-card">
                    <div class="portfolio-image">
                        <img src="<?php echo $project['image_url']; ?>" alt="<?php echo $project['title']; ?>">
                        <div class="portfolio-overlay">
                            <h3><?php echo $project['title']; ?></h3>
                            <p><?php echo substr($project['description'], 0, 80) . '...'; ?></p>
                            <div class="portfolio-links">
                                <a href="project.php?id=<?php echo $project['id']; ?>" class="btn btn-small">Details</a>
                                <?php if ($project['project_url']): ?>
                                <a href="<?php echo $project['project_url']; ?>" target="_blank" class="btn btn-small">Live Demo</a>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<?php
require_once 'includes/footer.php';
?>