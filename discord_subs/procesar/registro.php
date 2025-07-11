<?php
require_once '../includes/config.php';
require_once '../includes/database.php';
require_once '../includes/functions.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ../registro.php');
    exit();
}

$nombre = limpiarEntrada($_POST['nombre'] ?? '');
$apellido = limpiarEntrada($_POST['apellido'] ?? '');
$correo = trim($_POST['correo'] ?? '');
$telefono = limpiarEntrada($_POST['telefono'] ?? '');
$instagram = limpiarEntrada($_POST['instagram'] ?? '');
$discord = limpiarEntrada($_POST['discord'] ?? '');
$password = $_POST['password'] ?? '';
$confirm_password = $_POST['confirm_password'] ?? '';

if (empty($nombre) || empty($apellido) || empty($correo) || empty($telefono) || empty($discord) || empty($password) || empty($confirm_password)) {
    $_SESSION['error'] = "Todos los campos (excepto Instagram) son obligatorios.";
    header('Location: ../registro.php');
    exit();
}
if ($password !== $confirm_password) {
    $_SESSION['error'] = "Las contraseñas no coinciden.";
    header('Location: ../registro.php');
    exit();
}
if (!filter_var($correo, FILTER_VALIDATE_EMAIL)) {
    $_SESSION['error'] = "El formato del correo electrónico no es válido.";
    header('Location: ../registro.php');
    exit();
}

try {
    $db = new Database();
    $conn = $db->connect();
    
    $query_check = "SELECT id FROM usuarios WHERE correo = :correo OR discord = :discord LIMIT 1";
    $stmt_check = $conn->prepare($query_check);
    $stmt_check->bindParam(':correo', $correo);
    $stmt_check->bindParam(':discord', $discord);
    $stmt_check->execute();
    
    if ($stmt_check->rowCount() > 0) {
        $_SESSION['error'] = "El correo o usuario Discord ya está registrado.";
        header('Location: ../registro.php');
        exit();
    }
    
    $hashed_password = password_hash($password, PASSWORD_DEFAULT);
    
    $query_insert = "INSERT INTO usuarios (nombre, apellido, correo, telefono, instagram, discord, password) VALUES (:nombre, :apellido, :correo, :telefono, :instagram, :discord, :password)";
    $stmt_insert = $conn->prepare($query_insert);
    $stmt_insert->bindParam(':nombre', $nombre);
    $stmt_insert->bindParam(':apellido', $apellido);
    $stmt_insert->bindParam(':correo', $correo);
    $stmt_insert->bindParam(':telefono', $telefono);
    $stmt_insert->bindParam(':instagram', $instagram);
    $stmt_insert->bindParam(':discord', $discord);
    $stmt_insert->bindParam(':password', $hashed_password);
    
    if ($stmt_insert->execute()) {
        $usuario_id = $conn->lastInsertId();
        registrarActividad($conn, $usuario_id, 'Registro de cuenta exitoso');
        
        $_SESSION['usuario_id'] = $usuario_id;
        $_SESSION['usuario_discord'] = $discord;
        $_SESSION['usuario_rol'] = 'usuario';
        
        $_SESSION['success'] = "¡Registro exitoso! Ahora elige un plan para comenzar.";
        header('Location: ../planes.php');
        exit();
    } else {
        throw new Exception("Error en la base de datos al ejecutar la inserción.");
    }
} catch (Exception $e) {
    error_log("Error en procesar/registro.php: " . $e->getMessage());
    $_SESSION['error'] = "Ocurrió un error en el servidor. Inténtalo de nuevo más tarde.";
    header('Location: ../registro.php');
    exit();
}
?>