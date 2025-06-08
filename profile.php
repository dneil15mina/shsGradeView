<?php
require_once 'includes/auth.php';
require_once 'config/db_connect.php';

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Process photo upload if exists
    if (isset($_FILES['profile_photo']) && $_FILES['profile_photo']['error'] === UPLOAD_ERR_OK) {
        $allowed_types = ['image/jpeg', 'image/png'];
        $max_size = 2 * 1024 * 1024; // 2MB
        
        if (in_array($_FILES['profile_photo']['type'], $allowed_types) && 
            $_FILES['profile_photo']['size'] <= $max_size) {
            
            $upload_dir = 'uploads/profile_photos/';
            if (!is_dir($upload_dir)) {
                mkdir($upload_dir, 0755, true);
            }
            
            $filename = uniqid() . '_' . basename($_FILES['profile_photo']['name']);
            $target_path = $upload_dir . $filename;
            
            if (move_uploaded_file($_FILES['profile_photo']['tmp_name'], $target_path)) {
                // Update database with photo path
                $stmt = $pdo->prepare("UPDATE users SET profile_photo = ? WHERE user_id = ?");
                $stmt->execute([$target_path, $_SESSION['user_id']]);
                $_SESSION['success'] = "Profile photo updated successfully!";
            }
        } else {
            $_SESSION['error'] = "Invalid file type or size exceeds 2MB limit";
        }
    }
    
    // Process other profile updates
    if ($_SESSION['role'] === 'student') {
        $stmt = $pdo->prepare("UPDATE users SET username = ?, email = ? WHERE user_id = ?");
        $stmt->execute([
            $_POST['username'],
            $_POST['email'],
            $_SESSION['user_id']
        ]);
    } else {
        $stmt = $pdo->prepare("UPDATE users SET username = ?, first_name = ?, last_name = ?, email = ? WHERE user_id = ?");
        $stmt->execute([
            $_POST['username'],
            $_POST['first_name'],
            $_POST['last_name'],
            $_POST['email'],
            $_SESSION['user_id']
        ]);
    }
    $_SESSION['success'] = "Profile updated successfully!";
    header("Location: profile.php");
    exit;
}

    // Get current user data
    $stmt = $pdo->prepare("SELECT username, first_name, last_name, email, profile_photo FROM users WHERE user_id = ?");
    $stmt->execute([$_SESSION['user_id']]);
    $user = $stmt->fetch();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Profile</title>
    <link href="assets/css/bootstrap.min.css" rel="stylesheet">
    <style>
        .profile-photo {
            width: 150px;
            height: 150px;
            object-fit: cover;
            border-radius: 50%;
        }
        .upload-btn {
            cursor: pointer;
        }
    </style>
</head>
<body>
    <?php include 'includes/navbar.php'; ?>
    
    <div class="container mt-4">
        <div class="row">
            <div class="col-md-4">
                <div class="card">
                    <div class="card-body text-center">
                        <img src="<?= $user['profile_photo'] ?? 'assets/images/default-avatar.jpg' ?>" 
                             class="profile-photo mb-3" 
                             alt="Profile Photo">
                    </div>
                </div>
            </div>
            <div class="col-md-8">
                <div class="card">
                    <div class="card-header">
                        <h4>Profile Information</h4>
                    </div>
                    <div class="card-body">
                        <form method="post" enctype="multipart/form-data">
                            <input type="file" name="profile_photo" id="profile_photo" class="d-none" 
                                   accept="image/jpeg, image/png">
                            <label for="profile_photo" class="btn btn-primary upload-btn">
                                Change Photo
                            </label>
                        <?php if (isset($_SESSION['success'])): ?>
                            <div class="alert alert-success"><?= $_SESSION['success'] ?></div>
                            <?php unset($_SESSION['success']); ?>
                        <?php endif; ?>
                        <?php if (isset($_SESSION['error'])): ?>
                            <div class="alert alert-danger"><?= $_SESSION['error'] ?></div>
                            <?php unset($_SESSION['error']); ?>
                        <?php endif; ?>
                        
                        <div class="form-group mb-3">
                            <label>Username</label>
                            <input type="text" class="form-control" name="username" 
                                   value="<?= htmlspecialchars($user['username']) ?>">
                        </div>

                        <div class="form-group mb-3">
                            <label for="first_name">First Name</label>
                            <input type="text" class="form-control" name="first_name" id="first_name" 
                                   value="<?= htmlspecialchars($user['first_name'] ?? '') ?>"
                                   <?= $_SESSION['role'] === 'student' ? 'readonly' : '' ?>>
                        </div>
                        
                        <div class="form-group mb-3">
                            <label for="last_name">Last Name</label>
                            <input type="text" class="form-control" name="last_name" id="last_name" 
                                   value="<?= htmlspecialchars($user['last_name'] ?? '') ?>"
                                   <?= $_SESSION['role'] === 'student' ? 'readonly' : '' ?>>
                        </div>
                        
                        <div class="form-group mb-3">
                            <label for="email">Email</label>
                            <input type="email" class="form-control" name="email" id="email" 
                                   value="<?= htmlspecialchars($user['email'] ?? '') ?>">
                        </div>

                        <div class="mb-3">
                            <a href="password_change.php" class="btn btn-secondary">Change Password</a>
                        </div>
                        
                        <button type="submit" class="btn btn-primary">Save Changes</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="assets/js/bootstrap.bundle.min.js"></script>
    <script>
        // Preview image before upload
        document.getElementById('profile_photo').addEventListener('change', function(e) {
            if (this.files && this.files[0]) {
                const reader = new FileReader();
                reader.onload = function(e) {
                    document.querySelector('.profile-photo').src = e.target.result;
                }
                reader.readAsDataURL(this.files[0]);
            }
        });
    </script>
</body>
</html>
