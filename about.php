<?php
$pageTitle = "About Me";
require_once 'includes/config.php';
require_once 'includes/header.php';
?>

<section class="about-section">
    <div class="container">
        <div class="about-content">
            <div class="about-image">
                <img src="<?php echo SITE_URL; ?>/assets/images/profile.jpg" alt="Taanach">
            </div>
            <div class="about-text">
                <h2 class="section-title">About Me</h2>
                <p>Hello! I'm Kajogo Nancy, a passionate web developer with expertise in PHP, JavaScript, and modern web technologies. I specialize in creating responsive, user-friendly websites and applications.</p>
                
                <h3>My Journey</h3>
                <p>I started coding in 2023 and have since worked on numerous projects ranging from small business websites to complex web applications. My approach combines technical excellence with creative design thinking.</p>
                
                <h3>What I Do</h3>
                <ul class="about-list">
                    <li><i class="fas fa-check"></i> Full-stack web development</li>
                    <li><i class="fas fa-check"></i> Responsive website design</li>
                    <li><i class="fas fa-check"></i> API development and integration</li>
                    <li><i class="fas fa-check"></i> Performance optimization</li>
                    <li><i class="fas fa-check"></i> Technical consulting</li>
                </ul>
                
                <div class="about-buttons">
                    <a href="<?php echo SITE_URL; ?>/portfolio.php" class="btn btn-primary">View My Work</a>
                    <a href="<?php echo SITE_URL; ?>/contact.php" class="btn btn-secondary">Get In Touch</a>
                </div>
            </div>
        </div>
    </div>
</section>

<section class="timeline-section">
    <div class="container">
        <h2 class="section-title">My Experience</h2>
        <div class="timeline">
            <div class="timeline-item">
                <div class="timeline-date">2022 - Present</div>
                <div class="timeline-content">
                    <h3>Senior Web Developer</h3>
                    <h4>Tech Solutions Inc.</h4>
                    <p>Lead developer for enterprise web applications, mentoring junior developers and implementing modern development practices.</p>
                </div>
            </div>
            <div class="timeline-item">
                <div class="timeline-date">2019 - 2022</div>
                <div class="timeline-content">
                    <h3>Web Developer</h3>
                    <h4>Digital Creations Agency</h4>
                    <p>Developed and maintained client websites, implemented custom CMS solutions, and optimized site performance.</p>
                </div>
            </div>
            <div class="timeline-item">
                <div class="timeline-date">2017 - 2019</div>
                <div class="timeline-content">
                    <h3>Junior Developer</h3>
                    <h4>Startup Ventures</h4>
                    <p>Assisted in building MVP products, fixed bugs, and contributed to front-end development.</p>
                </div>
            </div>
        </div>
    </div>
</section>

<?php
require_once 'includes/footer.php';
?>