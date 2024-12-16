<?php
session_start();

// Function to check if the user is logged in
function is_logged_in() {
    return isset($_SESSION['username']);
}

// Set a session timeout (in seconds)
$session_lifetime = 1800; // 2 minutes

// Check if session has expired
if (isset($_SESSION['last_activity']) && (time() - $_SESSION['last_activity']) > $session_lifetime) {
    session_unset();
    session_destroy();

    // Redirect with session_expired flag and current page for redirection
    $current_page = basename($_SERVER['PHP_SELF']);
    $redirect_url = "templates/login.html?session_expired=true&redirect=" . urlencode($current_page);
    header("Location: $redirect_url");
    exit;
}

// Update the last activity timestamp
$_SESSION['last_activity'] = time();
?>
