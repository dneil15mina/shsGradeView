<?php
require_once 'includes/auth.php';

if (isLoggedIn()) {
    logoutUser();
}

header('Location: login.php');
exit;
?>
