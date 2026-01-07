<?php
$pageTitle = "Home";
require_once 'includes/config.php';
require_once 'includes/header.php';

?>

<section class="hero">
    <div class="container">
        <div class="hero-content">
            <h1>Hi, I'm <span>Kajogo Nancy</span></h1>
            <h2>A Passionate Full Stack Developer</h2>
            <p>I love turning ideas into clean, functional and creative digital solution with  efficient code.</p>
            <div class="hero-buttons">
                <a href="portfolio.php" class="btn btn-primary">View My Work</a>
                <a href="contact.php" class="btn btn-secondary">Contact Me</a>
                <!-- <a href="contact.php" class="btn btn-secondary">Login</a> -->
            </div>
        </div>
          <div class="hero-image">
            <img src="<?php echo SITE_URL; ?>/assets/images/profile.jpg" alt="Developer Illustration"> 
        </div> 
    </div> 
</section>



<section class="featured-projects">
    <div class="container">
        <h2 class="section-title">Featured Projects</h2>
        <div class="projects-grid">
            <?php 
            $projects = getProjects(3);
            foreach ($projects as $project): 
            ?>
            <div class="project-card">
                <div class="project-image">
                    <img src="<?php echo $project['image_url']; ?>" alt="<?php echo $project['title']; ?>">
                </div>
                <div class="project-info">
                    <h3><?php echo $project['title']; ?></h3>
                    <p><?php echo substr($project['description'], 0, 100) . '...'; ?></p>
                    <a href="project.php?id=<?php echo $project['id']; ?>" class="btn btn-small">View Details</a>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
        <div class="text-center">
            <a href="portfolio.php" class="btn btn-primary">View All Projects</a>
        </div>
    </div>
</section>
            

<section class="skills-preview">
    <div class="container">
        <h2 class="section-title">My Skills</h2>
        <div class="skills-container">
            <?php 
            $skills = getSkillsByCategory();
            $categories = array_keys($skills);
            $half = ceil(count($categories) / 2);
            ?>
            <div class="skills-column">
                <?php for ($i = 0; $i < $half; $i++): ?>
                <div class="skill-category">
                    <h3><?php echo $categories[$i]; ?></h3>
                    <?php foreach ($skills[$categories[$i]] as $skill): ?>
                    <div class="skill-item">
                        <div class="skill-info">
                            <span><?php echo $skill['name']; ?></span>
                            <span><?php echo $skill['percentage']; ?>%</span>
                        </div>
                        <div class="skill-bar">
                            <div class="skill-progress" style="width: <?php echo $skill['percentage']; ?>%"></div>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
                <?php endfor; ?>
            </div>
            <div class="skills-column">
                <?php for ($i = $half; $i < count($categories); $i++): ?>
                <div class="skill-category">
                    <h3><?php echo $categories[$i]; ?></h3>
                    <?php foreach ($skills[$categories[$i]] as $skill): ?>
                    <div class="skill-item">
                        <div class="skill-info">
                            <span><?php echo $skill['name']; ?></span>
                            <span><?php echo $skill['percentage']; ?>%</span>
                        </div>
                        <div class="skill-bar">
                            <div class="skill-progress" style="width: <?php echo $skill['percentage']; ?>%"></div>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
                <?php endfor; ?>
            </div>
        </div>
        <div class="text-center">
            <a href="skills.php" class="btn btn-primary">View All Skills</a>
        </div>
    </div>
</section>

<?php
require_once 'includes/footer.php';
?>