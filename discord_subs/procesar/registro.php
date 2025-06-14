<?php
session_start();
require_once '../includes/database.php';
require_once '../includes/functions.php';

if($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Validar y sanitizar datos
    $nombre = trim($_POST['nombre']);
    $apellido = trim($_POST['apellido']);
    $correo = filter_var(trim($_POST['correo']), FILTER_SANITIZE_EMAIL);
    $telefono = trim($_POST['telefono']);
    $instagram = trim($_POST['instagram']);
    $discord = trim($_POST['discord']);
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];
    
    // Validaciones básicas
    if($password !== $confirm_password) {
        $_SESSION['error'] = "Las contraseñas no coinciden.";
        header('Location: ../registro.php');
        exit();
    }
    
    if(!filter_var($correo, FILTER_VALIDATE_EMAIL)) {
        $_SESSION['error'] = "El correo electrónico no es válido.";
        header('Location: ../registro.php');
        exit();
    }
    
    // Verificar si el usuario ya existe
    $db = new Database();
    $conn = $db->connect();
    
    $query = "SELECT id FROM usuarios WHERE correo = :correo OR discord = :discord LIMIT 1";
    $stmt = $conn->prepare($query);
    $stmt->bindParam(':correo', $correo);
    $stmt->bindParam(':discord', $discord);
    $stmt->execute();
    
    if($stmt->rowCount() > 0) {
        $_SESSION['error'] = "El correo o usuario Discord ya está registrado.";
        header('Location: ../registro.php');
        exit();
    }
    
    // Hash de la contraseña
    $hashed_password = password_hash($password, PASSWORD_DEFAULT);
    
    // Insertar nuevo usuario
    $query = "INSERT INTO usuarios (nombre, apellido, correo, telefono, instagram, discord, password) 
              VALUES (:nombre, :apellido, :correo, :telefono, :instagram, :discord, :password)";
    
    $stmt = $conn->prepare($query);
    $stmt->bindParam(':nombre', $nombre);
    $stmt->bindParam(':apellido', $apellido);
    $stmt->bindParam(':correo', $correo);
    $stmt->bindParam(':telefono', $telefono);
    $stmt->bindParam(':instagram', $instagram);
    $stmt->bindParam(':discord', $discord);
    $stmt->bindParam(':password', $hashed_password);
    
    if($stmt->execute()) {
        $usuario_id = $conn->lastInsertId();
        
        // Registrar actividad
        registrarActividad($usuario_id, 'Registro de cuenta');
        
        // Iniciar sesión automáticamente
        $_SESSION['usuario_id'] = $usuario_id;
        $_SESSION['usuario_discord'] = $discord;
        $_SESSION['usuario_rol'] = 'usuario';
        
        // Redirigir a selección de plan
        header('Location: ../planes.php');
        exit();
    } else {
        $_SESSION['error'] = "Error al registrar el usuario. Por favor, inténtalo de nuevo.";
        header('Location: ../registro.php');
        exit();
    }
} else {
    header('Location: ../registro.php');
    exit();
}
?>