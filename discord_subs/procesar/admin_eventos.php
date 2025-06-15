<?php
require_once '../includes/config.php';
require_once '../includes/auth.php';
require_once '../includes/database.php';
require_once '../includes/functions.php';

requireAdmin();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ../admin/eventos.php');
    exit();
}

$accion = $_POST['accion'] ?? '';
$redirect_url = '../admin/eventos.php';

try {
    $db = new Database();
    $conn = $db->connect();
    $admin_id = $_SESSION['usuario_id'];

    switch ($accion) {
        // ... (casos 'agregar', 'editar', 'eliminar' sin cambios)

        case 'agregar':
            $titulo = limpiarEntrada($_POST['titulo']);
            $descripcion = limpiarEntrada($_POST['descripcion']);
            $fecha_evento = $_POST['fecha_evento'];
            $query = "INSERT INTO eventos (titulo, descripcion, fecha_evento) VALUES (?, ?, ?)";
            $stmt = $conn->prepare($query);
            $stmt->execute([$titulo, $descripcion, $fecha_evento]);
            registrarActividad($conn, $admin_id, 'Admin: Evento agregado', "Título: $titulo");
            $_SESSION['success'] = "Evento agregado correctamente.";
            break;

        case 'editar':
            $id = filter_input(INPUT_POST, 'id', FILTER_VALIDATE_INT);
            $titulo = limpiarEntrada($_POST['titulo']);
            $descripcion = limpiarEntrada($_POST['descripcion']);
            $fecha_evento = $_POST['fecha_evento'];
            $query = "UPDATE eventos SET titulo = ?, descripcion = ?, fecha_evento = ? WHERE id = ?";
            $stmt = $conn->prepare($query);
            $stmt->execute([$titulo, $descripcion, $fecha_evento, $id]);
            registrarActividad($conn, $admin_id, 'Admin: Evento editado', "ID Evento: $id");
            $_SESSION['success'] = "Evento actualizado correctamente.";
            break;

        case 'eliminar':
            $id = filter_input(INPUT_POST, 'id', FILTER_VALIDATE_INT);
            $query = "DELETE FROM eventos WHERE id = ?";
            $stmt = $conn->prepare($query);
            $stmt->execute([$id]);
            registrarActividad($conn, $admin_id, 'Admin: Evento eliminado', "ID Evento: $id");
            $_SESSION['success'] = "Evento eliminado correctamente.";
            break;

        case 'notificar':
            $id = filter_input(INPUT_POST, 'id', FILTER_VALIDATE_INT);
            $evento_query = $conn->prepare("SELECT * FROM eventos WHERE id = ?");
            $evento_query->execute([$id]);
            $evento = $evento_query->fetch();

            if ($evento) {
                $usuarios_query = $conn->query("SELECT id, correo, nombre FROM usuarios WHERE estado = 'activo'");
                $usuarios = $usuarios_query->fetchAll();

                $asunto_email = "📢 Notificación de Evento: " . $evento['titulo'];
                $cuerpo_email = "<h1>Hola!</h1><p>Te recordamos nuestro próximo evento: <strong>".htmlspecialchars($evento['titulo'])."</strong> el día ".date('d/m/Y \a \l\a\s H:i', strtotime($evento['fecha_evento']))." hs. ¡Te esperamos en la transmisión!</p>";
                
                // --- NUEVA LÓGICA PARA NOTIFICACIONES EN LA PÁGINA ---
                $mensaje_notificacion = "¡Nuevo evento! '".htmlspecialchars($evento['titulo'])."' ha sido programado. ¡No te lo pierdas!";
                $link_notificacion = SITE_URL . "stream.php";
                $notificacion_stmt = $conn->prepare("INSERT INTO notificaciones (usuario_id, mensaje, link) VALUES (?, ?, ?)");
                // --- FIN NUEVA LÓGICA ---

                foreach ($usuarios as $usuario) {
                    // 1. Enviar correo (como antes)
                    // La función enviarCorreo ahora requiere el nombre del destinatario
                    enviarCorreo($usuario['correo'], $usuario['nombre'], $asunto_email, $cuerpo_email);

                    // 2. Guardar notificación en la base de datos (NUEVO)
                    $notificacion_stmt->execute([$usuario['id'], $mensaje_notificacion, $link_notificacion]);
                }
                
                $conn->prepare("UPDATE eventos SET notificado = 1 WHERE id = ?")->execute([$id]);
                registrarActividad($conn, $admin_id, 'Admin: Notificación de evento enviada', "ID Evento: $id");
                $_SESSION['success'] = "Notificación enviada a todos los usuarios activos.";
            } else {
                 $_SESSION['error'] = "Evento no encontrado.";
            }
            break;

        default:
            $_SESSION['error'] = "Acción no válida.";
    }
} catch (Exception $e) {
    error_log("Error en admin_eventos.php: " . $e->getMessage());
    $_SESSION['error'] = "Ocurrió un error en el servidor.";
}

header('Location: ' . $redirect_url);
exit();