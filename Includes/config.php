
<?php

define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_NAME', 'portfolio_db');


define('SITE_TITLE', 'Taanach.dev');
define('SITE_EMAIL', 'kajogonancy287@gmail.com');
define('SITE_URL', 'http://localhost/taanach.dev');


try {
    $db = new PDO("mysql:host=".DB_HOST.";dbname=".DB_NAME, DB_USER, DB_PASS);
    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch(PDOException $e) {
    die("Connection failed: " . $e->getMessage());
}

require_once 'functions.php';
?>