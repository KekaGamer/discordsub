<?php
require_once '../includes/config.php';
require_once '../includes/database.php';
require_once '../includes/functions.php';

if (isset($_SESSION['usuario_id'])) {
    $db = new Database();
    $conn = $db->connect();
    registrarActividad($conn, $_SESSION['usuario_id'], 'Cierre de sesión');
}

session_destroy();
header('Location: ' . SITE_URL . 'login.php');
exit();