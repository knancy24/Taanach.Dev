<?php
$pageTitle = "Skills";
require_once 'includes/config.php';
require_once 'includes/header.php';
?>

<section class="skills-section">
    <div class="container">
        <h2 class="section-title">My Skills</h2>
        <div class="skills-intro">
            <p>I've developed a diverse skill set through years of experience working on various projects. Here's a breakdown of my technical capabilities.</p>
        </div>
        
        <div class="skills-container">
            <?php 
            $skills = getSkillsByCategory();
            foreach ($skills as $category => $categorySkills): 
            ?>
            <div class="skill-category">
                <h3><?php echo $category; ?></h3>
                <div class="skills-list">
                    <?php foreach ($categorySkills as $skill): ?>
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
            </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<section class="tools-section">
    <div class="container">
        <h2 class="section-title">Tools & Technologies</h2>
        <div class="tools-grid">
            <div class="tool-item">
                <i class="fab fa-html5"></i>
                <span>HTML5</span>
            </div>
            <div class="tool-item">
                <i class="fab fa-css3-alt"></i>
                <span>CSS3</span>
            </div>
            <div class="tool-item">
                <i class="fab fa-js"></i>
                <span>JavaScript</span>
            </div>
            <div class="tool-item">
                <i class="fab fa-php"></i>
                <span>PHP</span>
            </div>
            <div class="tool-item">
                <i class="fab fa-laravel"></i>
                <span>Laravel</span>
            </div>
            <div class="tool-item">
                <i class="fab fa-react"></i>
                <span>React</span>
            </div>
            <div class="tool-item">
                <i class="fab fa-node-js"></i>
                <span>Node.js</span>
            </div>
            <div class="tool-item">
                <i class="fas fa-database"></i>
                <span>MySQL</span>
            </div>
            <div class="tool-item">
                <i class="fab fa-git"></i>
                <span>Git</span>
            </div>
            <div class="tool-item">
                <i class="fab fa-docker"></i>
                <span>Docker</span>
            </div>
            <div class="tool-item">
                <i class="fab fa-aws"></i>
                <span>AWS</span>
            </div>
            <div class="tool-item">
                <i class="fas fa-terminal"></i>
                <span>CLI</span>
            </div>
        </div>
    </div>
</section>

<?php
require_once 'includes/footer.php';
?>