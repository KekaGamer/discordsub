<?php
// Iniciar sesión solo si no está activa
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Cargar dependencias
require_once '../includes/config.php';
require_once '../includes/database.php';
require_once '../includes/functions.php';

// Verificar método de solicitud
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    $_SESSION['error'] = "Método no permitido";
    header('Location: ../login.php');
    exit();
}

// Procesamiento del formulario con manejo de errores
try {
    // Validar datos de entrada
    $discord = trim($_POST['discord'] ?? '');
    $password = $_POST['password'] ?? '';

    if (empty($discord) || empty($password)) {
        throw new Exception("Todos los campos son requeridos");
    }

    // Establecer conexión a la base de datos
    $db = new Database();
    $conn = $db->connect();
    
    if (!$conn) {
        throw new Exception("Error de conexión con la base de datos");
    }

    // Consulta preparada para seguridad
    $query = "SELECT id, discord, password, rol FROM usuarios WHERE discord = :discord LIMIT 1";
    $stmt = $conn->prepare($query);
    
    if (!$stmt) {
        throw new Exception("Error al preparar la consulta SQL");
    }

    $stmt->bindParam(':discord', $discord, PDO::PARAM_STR);
    
    if (!$stmt->execute()) {
        throw new Exception("Error al ejecutar la consulta");
    }

    $usuario = $stmt->fetch(PDO::FETCH_ASSOC);

    // Verificar credenciales
    if (!$usuario || !password_verify($password, $usuario['password'])) {
        registrarActividad(0, 'Intento de inicio fallido', "Usuario: $discord");
        throw new Exception("Credenciales incorrectas");
    }

    // Establecer sesión
    $_SESSION['usuario_id'] = $usuario['id'];
    $_SESSION['usuario_discord'] = $usuario['discord'];
    $_SESSION['usuario_rol'] = $usuario['rol'];
    
    // Registrar actividad exitosa
    registrarActividad($usuario['id'], 'Inicio de sesión exitoso');

    // Redirigir según rol
    $redirect = ($usuario['rol'] === 'admin') ? '../admin/dashboard.php' : '../usuario/perfil.php';
    header("Location: $redirect");
    exit();

} catch (PDOException $e) {
    // Errores específicos de base de datos
    error_log("Error PDO en login: " . $e->getMessage());
    $_SESSION['error'] = "Error del sistema. Por favor intente más tarde.";
    header('Location: ../login.php');
    exit();

} catch (Exception $e) {
    // Otros errores
    error_log("Error en login: " . $e->getMessage());
    $_SESSION['error'] = $e->getMessage();
    header('Location: ../login.php');
    exit();
}