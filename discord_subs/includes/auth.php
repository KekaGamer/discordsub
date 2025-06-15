<?php
// Este archivo asume que config.php ya ha sido incluido y la sesión iniciada.

function isLoggedIn() {
    return isset($_SESSION['usuario_id']);
}

function isAdmin() {
    return isset($_SESSION['usuario_rol']) && $_SESSION['usuario_rol'] === 'admin';
}

function requireLogin() {
    if (!isLoggedIn()) {
        $_SESSION['error'] = "Debes iniciar sesión para acceder a esta página.";
        header('Location: ' . SITE_URL . 'login.php');
        exit();
    }
}

function requireAdmin() {
    if (!isLoggedIn()) {
        $_SESSION['error'] = "Debes iniciar sesión para acceder a esta página.";
        header('Location: ' . SITE_URL . 'login.php');
        exit();
    }
    if (!isAdmin()) {
        $_SESSION['error'] = "Acceso denegado. No tienes permisos de administrador.";
        header('Location: ' . SITE_URL . 'usuario/perfil.php');
        exit();
    }
}
?>