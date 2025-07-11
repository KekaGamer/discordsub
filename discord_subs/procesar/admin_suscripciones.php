<?php
require_once '../includes/config.php';
require_once '../includes/auth.php';
require_once '../includes/database.php';
require_once '../includes/functions.php';

requireAdmin();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ../admin/suscripciones.php');
    exit();
}

$accion = $_POST['accion'] ?? '';
$redirect_url = '../admin/suscripciones.php';

try {
    $db = new Database();
    $conn = $db->connect();
    $admin_id = $_SESSION['usuario_id'];

    switch ($accion) {
        // ===================================================================
        // INICIO DE LA SECCIÓN MODIFICADA
        // ===================================================================
        case 'aprobar':
            $id = filter_input(INPUT_POST, 'id', FILTER_VALIDATE_INT);
            if (!$id) {
                throw new Exception("ID de suscripción inválido.");
            }

            // 1. Obtenemos el ID del usuario antes de actualizar, para saber a quién notificar.
            $stmt_get_user = $conn->prepare("SELECT usuario_id FROM suscripciones WHERE id = :id");
            $stmt_get_user->execute([':id' => $id]);
            $suscripcion_data = $stmt_get_user->fetch();

            if ($suscripcion_data) {
                $usuario_a_notificar = $suscripcion_data['usuario_id'];

                // 2. Actualizamos la suscripción a 'activa' (código que ya tenías)
                $query_update = "UPDATE suscripciones SET estado = 'activa' WHERE id = ?";
                $stmt_update = $conn->prepare($query_update);
                $stmt_update->execute([$id]);

                // 3. (NUEVO) Creamos la notificación para el usuario
                $mensaje_notif = "¡Tu suscripción ha sido activada! Ya puedes disfrutar de todos los beneficios y ver los directos.";
                $link_notif = SITE_URL . "usuario/perfil.php";
                $stmt_notif = $conn->prepare("INSERT INTO notificaciones (usuario_id, mensaje, url) VALUES (?, ?, ?)");
                $stmt_notif->execute([$usuario_a_notificar, $mensaje_notif, $url_notif]);

                // 4. Registramos la actividad del admin (código que ya tenías)
                registrarActividad($conn, $admin_id, 'Admin: Suscripción aprobada', "ID Suscripción: $id");
                $_SESSION['success'] = "Suscripción aprobada y usuario notificado.";

            } else {
                $_SESSION['error'] = "No se encontró la suscripción a aprobar.";
            }
            break;
        // ===================================================================
        // FIN DE LA SECCIÓN MODIFICADA
        // ===================================================================

        case 'rechazar':
            $id = filter_input(INPUT_POST, 'id', FILTER_VALIDATE_INT);
            $query = "UPDATE suscripciones SET estado = 'rechazada' WHERE id = ?";
            $stmt = $conn->prepare($query);
            $stmt->execute([$id]);

            registrarActividad($conn, $admin_id, 'Admin: Suscripción rechazada', "ID Suscripción: $id");
            $_SESSION['success'] = "Suscripción rechazada.";
            break;

        case 'editar':
            $id = filter_input(INPUT_POST, 'id', FILTER_VALIDATE_INT);
            $tipo = limpiarEntrada($_POST['tipo']);
            $monto = filter_var($_POST['monto'], FILTER_VALIDATE_FLOAT);
            $fecha_inicio = $_POST['fecha_inicio'];
            $fecha_termino = $_POST['fecha_termino'];
            $estado = $_POST['estado'];

            $query = "UPDATE suscripciones SET tipo = ?, monto = ?, fecha_inicio = ?, fecha_termino = ?, estado = ? WHERE id = ?";
            $stmt = $conn->prepare($query);
            $stmt->execute([$tipo, $monto, $fecha_inicio, $fecha_termino, $estado, $id]);
            
            registrarActividad($conn, $admin_id, 'Admin: Suscripción editada', "ID Suscripción: $id");
            $_SESSION['success'] = "Suscripción actualizada.";
            break;

        case 'eliminar':
            $id = filter_input(INPUT_POST, 'id', FILTER_VALIDATE_INT);
            $query = "DELETE FROM suscripciones WHERE id = ?";
            $stmt = $conn->prepare($query);
            $stmt->execute([$id]);
            
            registrarActividad($conn, $admin_id, 'Admin: Suscripción eliminada', "ID Suscripción: $id");
            $_SESSION['success'] = "Suscripción eliminada.";
            break;
            
        default:
            $_SESSION['error'] = "Acción no válida.";
    }

} catch (Exception $e) {
    error_log("Error en admin_suscripciones.php: " . $e->getMessage());
    $_SESSION['error'] = "Ocurrió un error en el servidor.";
}

header('Location: ' . $redirect_url);
exit();