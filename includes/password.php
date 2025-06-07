<?php
require_once 'auth.php';

function verifyPassword($userId, $password) {
    global $pdo;
    $stmt = $pdo->prepare("SELECT password FROM users WHERE user_id = ?");
    $stmt->execute([$userId]);
    $user = $stmt->fetch();
    return $user && password_verify($password, $user['password']);
}

function updatePassword($userId, $newPassword) {
    global $pdo;
    $hashedPassword = password_hash($newPassword, PASSWORD_DEFAULT);
    $stmt = $pdo->prepare("UPDATE users SET password = ? WHERE user_id = ?");
    return $stmt->execute([$hashedPassword, $userId]);
}

function changePassword($userId, $currentPassword, $newPassword) {
    global $pdo;
    
    // Verify current password
    $stmt = $pdo->prepare("SELECT password FROM users WHERE user_id = ?");
    $stmt->execute([$userId]);
    $user = $stmt->fetch();
    
    if (!$user || !password_verify($currentPassword, $user['password'])) {
        return false;
    }
    
    // Update password
    $hashedPassword = password_hash($newPassword, PASSWORD_DEFAULT);
    $stmt = $pdo->prepare("UPDATE users SET password = ? WHERE user_id = ?");
    return $stmt->execute([$hashedPassword, $userId]);
}
?>
