<?php
require_once 'includes/config.php';
require_once 'includes/auth.php';

if (isLoggedIn()) {
    if (isAdmin()) {
        header('Location: admin/dashboard.php');
    } else {
        header('Location: usuario/perfil.php');
    }
    exit();
} else {
    header('Location: login.php');
    exit();
}
?>