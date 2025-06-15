<?php
require_once '../includes/config.php';
require_once '../includes/database.php';
require_once '../includes/functions.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ../login.php');
    exit();
}

try {
    $discord = trim($_POST['discord'] ?? '');
    $password = $_POST['password'] ?? '';

    if (empty($discord) || empty($password)) {
        $_SESSION['error'] = "Todos los campos son requeridos.";
        header('Location: ../login.php');
        exit();
    }

    $db = new Database();
    $conn = $db->connect();
    
    $query = "SELECT id, discord, password, rol, estado FROM usuarios WHERE discord = :discord LIMIT 1";
    $stmt = $conn->prepare($query);
    $stmt->bindParam(':discord', $discord, PDO::PARAM_STR);
    $stmt->execute();
    $usuario = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$usuario || !password_verify($password, $usuario['password'])) {
        registrarActividad($conn, 0, 'Intento de inicio de sesión fallido', "Usuario: $discord");
        $_SESSION['error'] = "Credenciales incorrectas.";
        header('Location: ../login.php');
        exit();
    }

    if ($usuario['estado'] === 'bloqueado') {
        registrarActividad($conn, $usuario['id'], 'Intento de inicio de sesión de cuenta bloqueada');
        $_SESSION['error'] = "Tu cuenta ha sido bloqueada. Contacta al administrador.";
        header('Location: ../login.php');
        exit();
    }
    
    // --- LÓGICA DE SESIÓN ÚNICA AÑADIDA ---
    $session_token = bin2hex(random_bytes(32)); // Generar un token aleatorio y seguro
    
    // Guardar el nuevo token en la base de datos para este usuario
    $update_token_stmt = $conn->prepare("UPDATE usuarios SET session_token = ? WHERE id = ?");
    $update_token_stmt->execute([$session_token, $usuario['id']]);
    
    // Guardar el token en la sesión del navegador
    $_SESSION['session_token'] = $session_token;
    // --- FIN DE LA LÓGICA ---

    $_SESSION['usuario_id'] = $usuario['id'];
    $_SESSION['usuario_discord'] = $usuario['discord'];
    $_SESSION['usuario_rol'] = $usuario['rol'];
    
    registrarActividad($conn, $usuario['id'], 'Inicio de sesión exitoso');

    header('Location: ../index.php');
    exit();

} catch (Exception $e) {
    error_log("Error en login.php: " . $e->getMessage());
    $_SESSION['error'] = "Ocurrió un error en el servidor. Inténtalo de nuevo más tarde.";
    header('Location: ../login.php');
    exit();
}