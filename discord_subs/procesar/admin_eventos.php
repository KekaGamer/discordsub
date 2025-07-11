<?php
// procesar/admin_eventos.php (ACTUALIZADO CON PLANTILLA DE CORREO)
require_once '../includes/config.php';
require_once '../includes/auth.php';
require_once '../includes/database.php';
require_once '../includes/functions.php'; // Requerido para la nueva función enviarCorreo()
requireAdmin();

$accion = $_POST['accion'] ?? '';
$redirect_url = '../admin/eventos.php';

try {
    $db = new Database();
    $conn = $db->connect();
    $admin_id = $_SESSION['usuario_id'];

    switch ($accion) {
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
                $usuarios = $usuarios_query->fetchAll(PDO::FETCH_ASSOC);
                
                $asunto_email = "📢 Notificación de Evento: " . $evento['titulo'];
                $link_notificacion = SITE_URL . "stream.php";
                
                $notificacion_stmt = $conn->prepare("INSERT INTO notificaciones (usuario_id, mensaje, link) VALUES (?, ?, ?)");
                $mensaje_notificacion_corta = "¡Recordatorio de Evento! '".htmlspecialchars($evento['titulo'])."' pronto comenzará.";
                
                foreach ($usuarios as $usuario) {
                    // 1. Crear notificación en la bandeja de entrada
                    $notificacion_stmt->execute([$usuario['id'], $mensaje_notificacion_corta, $link_notificacion]);

                    // 2. Preparar y enviar correo usando la nueva función con plantilla
                    $titulo_email = "📢 Recordatorio de Evento";
                    $cuerpo_mensaje_email = "¡Hola " . htmlspecialchars($usuario['nombre']) . "!<br><br>Te recordamos nuestro próximo evento: <strong>" . htmlspecialchars($evento['titulo']) . "</strong>.<br>Se realizará el día " . date('d/m/Y \a \l\a\s H:i', strtotime($evento['fecha_evento'])) . "hs. ¡Te esperamos en la transmisión!";
                    
                    enviarCorreo(
                        $usuario['correo'],
                        $asunto_email,
                        $titulo_email,
                        $cuerpo_mensaje_email,
                        "Ir al Stream",
                        $link_notificacion
                    );
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