<?php
require_once '../includes/auth.php';
requireRole('admin');

$userId = isset($_GET['id']) ? (int)$_GET['id'] : 0;

// Prevent deleting yourself
if ($userId === $_SESSION['user_id']) {
    $_SESSION['error'] = 'You cannot delete your own account';
    header('Location: users.php');
    exit;
}

// Check if user exists
$stmt = $pdo->prepare("SELECT * FROM users WHERE user_id = ?");
$stmt->execute([$userId]);
$user = $stmt->fetch();

if (!$user) {
    $_SESSION['error'] = 'User not found';
    header('Location: users.php');
    exit;
}

// Delete the user
$stmt = $pdo->prepare("DELETE FROM users WHERE user_id = ?");
if ($stmt->execute([$userId])) {
    $_SESSION['success'] = 'User deleted successfully';
} else {
    $_SESSION['error'] = 'Failed to delete user';
}

header('Location: users.php');
exit;
?>
