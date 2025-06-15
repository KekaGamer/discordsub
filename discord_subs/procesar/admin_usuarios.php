<?php
require_once '../includes/config.php';
require_once '../includes/auth.php';
require_once '../includes/database.php';
require_once '../includes/functions.php';

requireAdmin();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ../admin/usuarios.php');
    exit();
}

$accion = $_POST['accion'] ?? '';
$redirect_url = '../admin/usuarios.php';

try {
    $db = new Database();
    $conn = $db->connect();

    switch ($accion) {
        case 'agregar':
            $nombre = limpiarEntrada($_POST['nombre']);
            $apellido = limpiarEntrada($_POST['apellido']);
            $correo = filter_var(trim($_POST['correo']), FILTER_SANITIZE_EMAIL);
            $discord = limpiarEntrada($_POST['discord']);
            $password = $_POST['password'];
            $rol = $_POST['rol'] === 'admin' ? 'admin' : 'usuario';
            
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);
            
            $query = "INSERT INTO usuarios (nombre, apellido, correo, discord, password, rol) VALUES (?, ?, ?, ?, ?, ?)";
            $stmt = $conn->prepare($query);
            $stmt->execute([$nombre, $apellido, $correo, $discord, $hashed_password, $rol]);
            
            registrarActividad($conn, $_SESSION['usuario_id'], 'Admin: Usuario agregado', "Discord: $discord");
            $_SESSION['success'] = "Usuario agregado correctamente.";
            break;

        case 'editar':
            $id = filter_input(INPUT_POST, 'id', FILTER_VALIDATE_INT);
            $nombre = limpiarEntrada($_POST['nombre']);
            $apellido = limpiarEntrada($_POST['apellido']);
            $discord = limpiarEntrada($_POST['discord']);
            $rol = $_POST['rol'] === 'admin' ? 'admin' : 'usuario';
            $estado = $_POST['estado'] === 'bloqueado' ? 'bloqueado' : 'activo';

            $query = "UPDATE usuarios SET nombre = ?, apellido = ?, discord = ?, rol = ?, estado = ? WHERE id = ?";
            $stmt = $conn->prepare($query);
            $stmt->execute([$nombre, $apellido, $discord, $rol, $estado, $id]);

            if (!empty($_POST['password'])) {
                $hashed_password = password_hash($_POST['password'], PASSWORD_DEFAULT);
                $pass_query = "UPDATE usuarios SET password = ? WHERE id = ?";
                $pass_stmt = $conn->prepare($pass_query);
                $pass_stmt->execute([$hashed_password, $id]);
            }
            
            registrarActividad($conn, $_SESSION['usuario_id'], 'Admin: Usuario editado', "ID Usuario: $id");
            $_SESSION['success'] = "Usuario actualizado correctamente.";
            break;

        case 'eliminar':
            $id = filter_input(INPUT_POST, 'id', FILTER_VALIDATE_INT);
            $query = "DELETE FROM usuarios WHERE id = ?";
            $stmt = $conn->prepare($query);
            $stmt->execute([$id]);
            
            registrarActividad($conn, $_SESSION['usuario_id'], 'Admin: Usuario eliminado', "ID Usuario: $id");
            $_SESSION['success'] = "Usuario eliminado correctamente.";
            break;

        default:
            $_SESSION['error'] = "Acción no válida.";
    }
} catch (PDOException $e) {
    error_log("Error en admin_usuarios.php: " . $e->getMessage());
    $_SESSION['error'] = "Ocurrió un error en la base de datos. Es posible que el correo o Discord ya exista.";
} catch (Exception $e) {
    error_log("Error en admin_usuarios.php: " . $e->getMessage());
    $_SESSION['error'] = "Ocurrió un error en el servidor.";
}

header('Location: ' . $redirect_url);
exit();