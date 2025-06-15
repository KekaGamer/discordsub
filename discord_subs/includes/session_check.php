<?php
// Este script se debe incluir en cada página protegida después de la conexión a la BD.

// Si no hay un token en la sesión, no hacemos nada aquí (requireLogin se encargará)
if (!isset($_SESSION['usuario_id'], $_SESSION['session_token'])) {
    return;
}

// Comprobar si el token de la sesión actual coincide con el de la base de datos.
try {
    // Necesitamos la conexión a la BD. Asumimos que $conn ya existe.
    // Si no, la creamos.
    if (!isset($conn)) {
        $db_check = new Database();
        $conn_check = $db_check->connect();
    } else {
        $conn_check = &$conn;
    }
    
    $stmt = $conn_check->prepare("SELECT session_token FROM usuarios WHERE id = ?");
    $stmt->execute([$_SESSION['usuario_id']]);
    $db_token = $stmt->fetchColumn();

    // Si los tokens no coinciden, significa que se ha iniciado sesión en otro lugar.
    if ($db_token !== $_SESSION['session_token']) {
        session_destroy();
        header('Location: ' . SITE_URL . 'login.php?error=session_expired');
        exit();
    }
} catch (Exception $e) {
    error_log("Error en session_check: " . $e->getMessage());
}