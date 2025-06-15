<?php
require_once '../includes/config.php';
require_once '../includes/auth.php';
require_once '../includes/database.php';
require_once '../includes/functions.php';

requireLogin();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ../usuario/perfil.php');
    exit();
}

try {
    $db = new Database();
    $conn = $db->connect();
    $usuario_id = $_SESSION['usuario_id'];

    // Datos a actualizar (solo los permitidos)
    $instagram = limpiarEntrada($_POST['instagram']);
    $discord = limpiarEntrada($_POST['discord']);
    
    // Validar que el nuevo discord no esté en uso por otro usuario
    $check_query = "SELECT id FROM usuarios WHERE discord = :discord AND id != :id";
    $check_stmt = $conn->prepare($check_query);
    $check_stmt->execute([':discord' => $discord, ':id' => $usuario_id]);
    if($check_stmt->fetch()) {
        $_SESSION['error'] = "Esa cuenta de Discord ya está en uso por otro usuario.";
        header('Location: ../usuario/perfil.php');
        exit();
    }
    
    // Actualizar solo Instagram y Discord
    $query = "UPDATE usuarios SET instagram = :instagram, discord = :discord WHERE id = :id";
    $stmt = $conn->prepare($query);
    $stmt->execute([
        ':instagram' => $instagram,
        ':discord' => $discord,
        ':id' => $usuario_id
    ]);
    
    registrarActividad($conn, $usuario_id, 'Actualización de perfil (Instagram/Discord)');

    // Actualizar contraseña si se proporcionó una nueva
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];

    if (!empty($password)) {
        if ($password !== $confirm_password) {
            $_SESSION['error'] = "Las nuevas contraseñas no coinciden.";
            header('Location: ../usuario/perfil.php');
            exit();
        }
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);
        $pass_query = "UPDATE usuarios SET password = :password WHERE id = :id";
        $pass_stmt = $conn->prepare($pass_query);
        $pass_stmt->execute([':password' => $hashed_password, ':id' => $usuario_id]);
        registrarActividad($conn, $usuario_id, 'Cambio de contraseña');
    }

    $_SESSION['usuario_discord'] = $discord; // Actualizar el discord en la sesión
    $_SESSION['success'] = "Perfil actualizado correctamente.";
    header('Location: ../usuario/perfil.php');
    exit();

} catch (Exception $e) {
    error_log("Error en perfil.php: " . $e->getMessage());
    $_SESSION['error'] = "Ocurrió un error al actualizar tu perfil.";
    header('Location: ../usuario/perfil.php');
    exit();
}