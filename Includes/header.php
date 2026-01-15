<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo isset($pageTitle) ? $pageTitle . ' | ' : ''; ?><?php echo SITE_TITLE; ?></title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@300;400;500;700&family=Fira+Code:wght@400;500&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="<?php echo SITE_URL; ?>/assets/css/style.css">
    <script src="<?php echo SITE_URL; ?>/assets/js/main.js" defer></script>
</head>
<body>
    <header class="main-header">
        <div class="container">
            <div class="logo">
                <a href="<?php echo SITE_URL; ?>">Taanach<span>.dev</span></a>
            </div>
            <nav class="main-nav">
                <ul>
                    <li><a href="<?php echo SITE_URL; ?>">Home</a></li>
                    <li><a href="<?php echo SITE_URL; ?>/about.php">About</a></li>
                    <li><a href="<?php echo SITE_URL; ?>/portfolio.php">Portfolio</a></li>
                    <li><a href="<?php echo SITE_URL; ?>/skills.php">Skills</a></li>
                    <li><a href="<?php echo SITE_URL; ?>/contact.php">Contact</a></li>
                </ul>
            </nav>
            <div class="mobile-menu-toggle">
                <i class="fas fa-bars"></i>
            </div>
        </div>
    </header>
    <main class="main-content">