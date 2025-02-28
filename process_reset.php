<?php
// process_reset.php

require_once __DIR__ . '/includes/db.php';

// Retrieve token and new password from the POST request
$token = $_POST['token'] ?? '';
$newPassword = $_POST['new_password'] ?? '';

if ($token && $newPassword) {
    // Verify token and expiration
    $stmt = $pdo->prepare("SELECT id, reset_expires FROM users WHERE reset_token = ?");
    $stmt->execute([$token]);
    $user = $stmt->fetch();

    if ($user && strtotime($user['reset_expires']) > time()) {
        // Hash the new password securely
        $passwordHash = password_hash($newPassword, PASSWORD_DEFAULT);

        // Update the user's password and clear the token
        $stmt = $pdo->prepare("UPDATE users SET password = ?, reset_token = NULL, reset_expires = NULL WHERE id = ?");
        $stmt->execute([$passwordHash, $user['id']]);

        echo "Your password has been reset successfully.";
        exit;
    }
}

echo "Failed to reset password. Please try again.";
?>
