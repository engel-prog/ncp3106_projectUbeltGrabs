<?php
// Call this at the top of pages that require login

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Set secure session options
ini_set('session.cookie_httponly', 1);
ini_set('session.cookie_secure', 1); // Only for HTTPS
ini_set('session.cookie_samesite', 'Strict');

// Session timeout (30 minutes)
$timeout = 1800;
if (isset($_SESSION['last_activity']) && (time() - $_SESSION['last_activity'] > $timeout)) {
    session_unset();
    session_destroy();
    header("Location: logins.php?expired=1");
    exit;
}

$_SESSION['last_activity'] = time();

// Regenerate session ID periodically
if (!isset($_SESSION['regenerated']) || (time() - $_SESSION['regenerated'] > 600)) {
    session_regenerate_id(true);
    $_SESSION['regenerated'] = time();
}
?>
