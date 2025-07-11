<?php
require_once '../includes/config.php';
require_once '../includes/auth.php';
require_once '../includes/database.php';
require_once '../includes/functions.php';

requireAdmin();

$accion = $_POST['accion'] ?? '';
$redirect_url = '../admin/dashboard.php'; // Por defecto redirige al dashboard

try {
    $db = new Database();
    $conn = $db->connect();

    // Obtenemos la lista de todos los usuarios activos a quienes notificar
    $usuarios_stmt = $conn->query("SELECT id FROM usuarios WHERE estado = 'activo' AND rol = 'usuario'");
    $usuarios = $usuarios_stmt->fetchAll(PDO::FETCH_ASSOC);

    // Preparamos la consulta para insertar notificaciones, la reusaremos en el bucle
    $stmt_notif = $conn->prepare("INSERT INTO notificaciones (usuario_id, mensaje, link, tipo) VALUES (?, ?, ?, ?)");

    switch ($accion) {
        case 'notify_event':
            $evento_id = filter_input(INPUT_POST, 'id', FILTER_VALIDATE_INT);
            if (!$evento_id) { throw new Exception("ID de evento no válido."); }

            // Obtenemos los detalles del evento
            $evento_stmt = $conn->prepare("SELECT titulo, fecha_evento FROM eventos WHERE id = ?");
            $evento_stmt->execute([$evento_id]);
            $evento = $evento_stmt->fetch();

            if ($evento) {
                $fecha_formateada = date('d/m/Y \a \l\a\s H:i', strtotime($evento['fecha_evento']));
                $mensaje = "¡Evento programado! No te pierdas '" . htmlspecialchars($evento['titulo']) . "' el día " . $fecha_formateada . " hs.";
                $url = SITE_URL . "stream.php";

                // Insertamos una notificación para cada usuario
                foreach ($usuarios as $usuario) {
                    $stmt_notif->execute([$usuario['id'], $mensaje, $url, 'evento']);
                }

                $_SESSION['success'] = "Notificación del evento enviada a todos los usuarios.";
            }
            $redirect_url = '../admin/eventos.php'; // Redirigir de vuelta a la página de eventos
            break;

        case 'go_live':
            $mensaje = "¡Estamos en directo ahora mismo! Entra ya para no perderte la acción.";
            $url = SITE_URL . "stream.php";

            // Insertamos una notificación para cada usuario
            foreach ($usuarios as $usuario) {
                $stmt_notif->execute([$usuario['id'], $mensaje, $url, 'stream']);
            }

            $_SESSION['success'] = "¡Notificación de 'Directo en Curso' enviada a todos los usuarios!";
            break;
            
        default:
            $_SESSION['error'] = "Acción de notificación no válida.";
    }

} catch (Exception $e) {
     // --- CÓDIGO DE DEPURACIÓN QUE MUESTRA EL ERROR REAL ---
    echo "<h1>Error Detallado del Servidor:</h1>";
    echo "<pre style='background-color: #ffcccc; padding: 15px; border: 1px solid red; color: #333; font-family: monospace; white-space: pre-wrap; word-wrap: break-word;'>";
    echo "<strong>Mensaje:</strong> " . htmlspecialchars($e->getMessage()) . "\n\n";
    echo "<strong>Archivo:</strong> " . $e->getFile() . "\n";
    echo "<strong>Línea:</strong> " . $e->getLine() . "\n\n";
    echo "<strong>Traza del Error:</strong>\n" . htmlspecialchars($e->getTraceAsString());
    echo "</pre>";
    die("--- Fin de la depuración. Por favor, envía este mensaje de error completo. ---");
    // --- FIN DEL CÓDIGO DE DEPURACIÓN ---
    error_log("Error en admin_notificaciones.php: " . $e->getMessage());
    $_SESSION['error'] = "Ocurrió un error al enviar las notificaciones.";
}

header('Location: ' . $redirect_url);
exit();