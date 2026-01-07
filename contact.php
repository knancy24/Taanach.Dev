<?php
$pageTitle = "Contact";
require_once 'includes/config.php';
require_once 'includes/header.php';

$errors = [];
$success = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = sanitize($_POST['name']);
    $email = sanitize($_POST['email']);
    $message = sanitize($_POST['message']);
    
    // Validation
    if (empty($name)) {
        $errors['name'] = 'Name is required';
    }
    
    if (empty($email)) {
        $errors['email'] = 'Email is required';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors['email'] = 'Please enter a valid email';
    }
    
    if (empty($message)) {
        $errors['message'] = 'Message is required';
    } elseif (strlen($message) < 10) {
        $errors['message'] = 'Message should be at least 10 characters';
    }
    
    if (empty($errors)) {
        if (saveMessage($name, $email, $message)) {
            $success = true;
        } else {
            $errors['database'] = 'There was an error sending your message. Please try again.';
        }
    }
}
?>

<section class="contact-section">
    <div class="container">
        <h2 class="section-title">Get In Touch</h2>
        
        <div class="contact-container">
            <div class="contact-info">
                <h3>Contact Information</h3>
                <p>Feel free to reach out to me for project inquiries, collaborations, or just to say hello!</p>
                
                <ul class="info-list">
                    <li>
                        <i class="fas fa-envelope"></i>
                        <span><?php echo SITE_EMAIL; ?></span>
                    </li>
                    <li>
                        <i class="fas fa-map-marker-alt"></i>
                        <span>Based in Juba-South Sudan</span>
                    </li>
                    <li>
                        <i class="fas fa-clock"></i>
                        <span>Available for freelance work</span>
                    </li>
                </ul>
                
                <div class="social-links">
                    <a href="#"><i class="fab fa-github"></i></a>
                    <a href="#"><i class="fab fa-linkedin"></i></a>
                    <a href="#"><i class="fab fa-twitter"></i></a>
                    <a href="#"><i class="fab fa-codepen"></i></a>
                </div>
            </div>
            
            <div class="contact-form">
                <?php if ($success): ?>
                <div class="alert alert-success">
                    Thank you for your message! I'll get back to you as soon as possible.
                </div>
                <?php elseif (!empty($errors['database'])): ?>
                <div class="alert alert-danger">
                    <?php echo $errors['database']; ?>
                </div>
                <?php endif; ?>
                
                <form action="contact.php" method="POST">
                    <div class="form-group <?php echo isset($errors['name']) ? 'has-error' : ''; ?>">
                        <label for="name">Your Name</label>
                        <input type="text" id="name" name="name" value="<?php echo isset($_POST['name']) ? $_POST['name'] : ''; ?>">
                        <?php if (isset($errors['name'])): ?>
                        <span class="error-message"><?php echo $errors['name']; ?></span>
                        <?php endif; ?>
                    </div>
                    
                    <div class="form-group <?php echo isset($errors['email']) ? 'has-error' : ''; ?>">
                        <label for="email">Your Email</label>
                        <input type="email" id="email" name="email" value="<?php echo isset($_POST['email']) ? $_POST['email'] : ''; ?>">
                        <?php if (isset($errors['email'])): ?>
                        <span class="error-message"><?php echo $errors['email']; ?></span>
                        <?php endif; ?>
                    </div>
                    
                    <div class="form-group <?php echo isset($errors['message']) ? 'has-error' : ''; ?>">
                        <label for="message">Your Message</label>
                        <textarea id="message" name="message" rows="5"><?php echo isset($_POST['message']) ? $_POST['message'] : ''; ?></textarea>
                        <?php if (isset($errors['message'])): ?>
                        <span class="error-message"><?php echo $errors['message']; ?></span>
                        <?php endif; ?>
                    </div>
                    
                    <button type="submit" class="btn btn-primary">Send Message</button>
                </form>
            </div>
        </div>
    </div>
</section>

<?php
require_once 'includes/footer.php';
?>