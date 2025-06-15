<?php
require_once '../includes/config.php';
require_once '../includes/database.php';
require_once '../includes/functions.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ' . SITE_URL . 'registro.php');
    exit();
}

$nombre = limpiarEntrada($_POST['nombre']);
$apellido = limpiarEntrada($_POST['apellido']);
$correo = filter_var(trim($_POST['correo']), FILTER_SANITIZE_EMAIL);
$telefono = limpiarEntrada($_POST['telefono']);
$instagram = limpiarEntrada($_POST['instagram']);
$discord = limpiarEntrada($_POST['discord']);
$password = $_POST['password'];
$confirm_password = $_POST['confirm_password'];

if (empty($nombre) || empty($apellido) || empty($correo) || empty($telefono) || empty($discord) || empty($password)) {
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

    $query = "SELECT id FROM usuarios WHERE correo = :correo OR discord = :discord LIMIT 1";
    $stmt = $conn->prepare($query);
    $stmt->bindParam(':correo', $correo);
    $stmt->bindParam(':discord', $discord);
    $stmt->execute();

    if ($stmt->rowCount() > 0) {
        $_SESSION['error'] = "El correo o usuario Discord ya está registrado.";
        header('Location: ../registro.php');
        exit();
    }

    $hashed_password = password_hash($password, PASSWORD_DEFAULT);

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

    if ($stmt->execute()) {
        $usuario_id = $conn->lastInsertId();
        registrarActividad($conn, $usuario_id, 'Registro de cuenta exitoso');

        $_SESSION['usuario_id'] = $usuario_id;
        $_SESSION['usuario_discord'] = $discord;
        $_SESSION['usuario_rol'] = 'usuario';

        $_SESSION['success'] = "¡Registro exitoso! Ahora elige un plan para comenzar.";
        header('Location: ../planes.php');
        exit();
    } else {
        throw new Exception("Error al insertar el usuario en la base de datos.");
    }
} catch (Exception $e) {
    error_log("Error en registro.php: " . $e->getMessage());
    $_SESSION['error'] = "Error al registrar el usuario. Por favor, inténtalo de nuevo.";
    header('Location: ../registro.php');
    exit();
}