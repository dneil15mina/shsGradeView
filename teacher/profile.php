<?php
require_once '../includes/auth.php';
requireRole('teacher');
require_once '../config/db_connect.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Handle profile updates
    $email = filter_input(INPUT_POST, 'email', FILTER_SANITIZE_EMAIL);
    $currentPassword = $_POST['current_password'] ?? '';
    $newPassword = $_POST['new_password'] ?? '';
    
    // Update email
    if (!empty($email)) {
        $stmt = $pdo->prepare("UPDATE users SET email = ? WHERE user_id = ?");
        $stmt->execute([$email, $_SESSION['user_id']]);
    }

    // Update password if provided
    if (!empty($currentPassword) && !empty($newPassword)) {
        require_once '../includes/password.php';
        if (verifyPassword($_SESSION['user_id'], $currentPassword)) {
            updatePassword($_SESSION['user_id'], $newPassword);
            $_SESSION['password_changed'] = true;
        } else {
            $_SESSION['password_error'] = 'Current password is incorrect';
        }
    }

    // Handle file upload
    if (!empty($_FILES['profile_photo']['name'])) {
        $uploadDir = '../uploads/profile_photos/';
        $fileName = 'teacher_' . $_SESSION['user_id'] . '_' . time() . '.' . pathinfo($_FILES['profile_photo']['name'], PATHINFO_EXTENSION);
        $targetPath = $uploadDir . $fileName;

        if (move_uploaded_file($_FILES['profile_photo']['tmp_name'], $targetPath)) {
            $stmt = $pdo->prepare("UPDATE users SET profile_photo = ? WHERE user_id = ?");
            $stmt->execute([$fileName, $_SESSION['user_id']]);
        }
    }

    header("Location: profile.php?success=1");
    exit;
}

// Get current user data
$stmt = $pdo->prepare("SELECT username, email, profile_photo FROM users WHERE user_id = ?");
$stmt->execute([$_SESSION['user_id']]);
$user = $stmt->fetch();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Teacher Profile</title>
    <link href="../assets/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.8.0/font/bootstrap-icons.css" rel="stylesheet">
</head>
<body>
    <?php include '../includes/navbar.php'; ?>
    
    <div class="container mt-4">
        <div class="row">
            <div class="col-md-8 mx-auto">
                <div class="card">
                    <div class="card-header">
                        <h4>Teacher Profile</h4>
                    </div>
                    <div class="card-body">
                        <?php if (isset($_GET['success'])): ?>
                            <div class="alert alert-success">Profile updated successfully!</div>
                        <?php endif; ?>
                        <?php if (isset($_SESSION['password_error'])): ?>
                            <div class="alert alert-danger"><?= $_SESSION['password_error'] ?></div>
                            <?php unset($_SESSION['password_error']); ?>
                        <?php endif; ?>
                        <?php if (isset($_SESSION['password_changed'])): ?>
                            <div class="alert alert-success">Password changed successfully!</div>
                            <?php unset($_SESSION['password_changed']); ?>
                        <?php endif; ?>

                        <form method="post" enctype="multipart/form-data">
                            <div class="row mb-3">
                                <div class="col-md-4 text-center">
                                    <img src="<?= !empty($user['profile_photo']) ? '../uploads/profile_photos/' . htmlspecialchars($user['profile_photo']) : '../assets/images/default-profile.png' ?>" 
                                         class="img-thumbnail mb-2" style="width: 150px; height: 150px; object-fit: cover;">
                                    <input type="file" name="profile_photo" class="form-control">
                                </div>
                                <div class="col-md-8">
                                    <div class="mb-3">
                                        <label class="form-label">Username</label>
                                        <input type="text" class="form-control" value="<?= htmlspecialchars($user['username']) ?>" readonly>
                                    </div>
                                    <div class="mb-3">
                                        <label class="form-label">Email</label>
                                        <input type="email" name="email" class="form-control" value="<?= htmlspecialchars($user['email'] ?? '') ?>">
                                    </div>
                                </div>
                            </div>

                            <h5 class="mt-4">Change Password</h5>
                            <div class="mb-3">
                                <label class="form-label">Current Password</label>
                                <input type="password" name="current_password" class="form-control">
                            </div>
                            <div class="mb-3">
                                <label class="form-label">New Password</label>
                                <input type="password" name="new_password" class="form-control">
                            </div>

                            <button type="submit" class="btn btn-primary">Save Changes</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="../assets/js/bootstrap.bundle.min.js"></script>
</body>
</html>
