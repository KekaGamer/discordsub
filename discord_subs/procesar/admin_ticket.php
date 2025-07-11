<?php
// procesar/admin_ticket.php (NUEVO ARCHIVO)
require_once '../includes/config.php';
require_once '../includes/auth.php';
require_once '../includes/database.php';
require_once '../includes/functions.php';
requireAdmin();

if (isset($_POST['actualizar_ticket'])) {
    $ticket_id = filter_input(INPUT_POST, 'ticket_id', FILTER_VALIDATE_INT);
    $mensaje = trim($_POST['mensaje']);
    $estado = $_POST['estado'];
    $prioridad = $_POST['prioridad'];
    $grupo_asignado = $_POST['grupo_asignado'];
    $sla_horas = filter_input(INPUT_POST, 'sla_horas', FILTER_VALIDATE_INT);
    $admin_id = $_SESSION['usuario_id'];

    if (!$ticket_id) {
        header('Location: ../admin/tickets.php');
        exit();
    }

    $db = new Database();
    $conn = $db->connect();

    // Actualizar el ticket principal
    $stmt_update = $conn->prepare("UPDATE tickets SET estado = :estado, prioridad = :prioridad, grupo_asignado = :grupo, sla_horas = :sla WHERE id = :id");
    $stmt_update->execute([
        ':estado' => $estado,
        ':prioridad' => $prioridad,
        ':grupo' => $grupo_asignado,
        ':sla' => $sla_horas,
        ':id' => $ticket_id
    ]);

    // Añadir respuesta si el admin escribió una
    if (!empty($mensaje)) {
        $stmt_insert = $conn->prepare("INSERT INTO ticket_respuestas (ticket_id, usuario_id, mensaje) VALUES (:tid, :uid, :msg)");
        $stmt_insert->execute([
            ':tid' => $ticket_id,
            ':uid' => $admin_id,
            ':msg' => $mensaje
        ]);
        // Aquí podrías enviar un email de notificación al usuario informando de la respuesta
    }
    
    $_SESSION['success'] = "Ticket #" . $ticket_id . " actualizado correctamente.";
    header('Location: ../admin/ver_ticket.php?id=' . $ticket_id);
    exit();
}

header('Location: ../admin/tickets.php');
exit();
?>