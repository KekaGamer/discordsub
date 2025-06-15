<?php
require_once '../includes/config.php';
require_once '../includes/auth.php';
require_once '../includes/database.php';

requireLogin();

$accion = $_GET['accion'] ?? '';
$notificacion_id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
$usuario_id = $_SESSION['usuario_id'];
$redirect_url = '../usuario/perfil.php';

if ($accion === 'marcar_leido' && $notificacion_id) {
    try {
        $db = new Database();
        $conn = $db->connect();
        
        // Actualiza el estado a 'leido' SOLO si la notificación pertenece al usuario actual
        $query = "UPDATE notificaciones SET leido = 1 WHERE id = ? AND usuario_id = ?";
        $stmt = $conn->prepare($query);
        $stmt->execute([$notificacion_id, $usuario_id]);
        
    } catch (Exception $e) {
        error_log("Error al marcar notificación como leída: " . $e->getMessage());
        $_SESSION['error'] = "No se pudo actualizar la notificación.";
    }
}

header('Location: ' . $redirect_url);
exit();