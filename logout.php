<?php
include 'config/database.php';

// Update online status to offline
if (isset($_SESSION['user_id'])) {
    $stmt = $pdo->prepare("UPDATE users SET is_online = FALSE WHERE id = ?");
    $stmt->execute([$_SESSION['user_id']]);
}

// Destroy session
session_destroy();

// Redirect to login page
header("Location: login.php");
exit();
?>