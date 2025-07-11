<?php
// procesar/ticket.php (VERSIÓN FINAL CORREGIDA)
require_once '../includes/config.php';
require_once '../includes/auth.php';
require_once '../includes/database.php';
requireLogin();

$db = new Database();
$conn = $db->connect();
$usuario_id = $_SESSION['usuario_id'];

// --- ACCIÓN: CREAR UN NUEVO TICKET ---
if (isset($_POST['crear_ticket'])) {
    $asunto = trim($_POST['asunto']);
    $mensaje = trim($_POST['mensaje']);
    $prioridad = $_POST['prioridad'];
    
    // CORRECCIÓN: Inicializar la variable de adjunto como null
    $nombre_adjunto = null;

    // Lógica para manejar el archivo adjunto
    if (isset($_FILES['adjunto']) && $_FILES['adjunto']['error'] == UPLOAD_ERR_OK) {
        $file = $_FILES['adjunto'];
        $max_size = 2 * 1024 * 1024; // 2MB
        $allowed_types = ['image/jpeg', 'image/png', 'application/pdf', 'text/plain'];

        if ($file['size'] > $max_size) {
            $_SESSION['error'] = "El archivo es demasiado grande. Máximo 2MB.";
            header('Location: ../ticket.php');
            exit();
        }

        if (!in_array($file['type'], $allowed_types)) {
            $_SESSION['error'] = "Tipo de archivo no permitido (solo jpg, png, pdf, txt).";
            header('Location: ../ticket.php');
            exit();
        }

        $upload_dir = '../uploads/tickets/';
        if (!is_dir($upload_dir)) {
            mkdir($upload_dir, 0755, true);
        }
        $file_ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        // Sanitizar nombre de archivo
        $safe_filename = preg_replace('/[^A-Za-z0-9_\-]/', '', pathinfo($file['name'], PATHINFO_FILENAME));
        $nombre_adjunto = 'ticket_' . $usuario_id . '_' . time() . '_' . $safe_filename . '.' . $file_ext;
        
        if (!move_uploaded_file($file['tmp_name'], $upload_dir . $nombre_adjunto)) {
            $_SESSION['error'] = "Error al subir el archivo adjunto.";
            header('Location: ../ticket.php');
            exit();
        }
    }

    if (!empty($asunto) && !empty($mensaje)) {
        $stmt = $conn->prepare("INSERT INTO tickets (usuario_id, asunto, mensaje, adjunto, prioridad, estado) VALUES (:uid, :asunto, :msg, :adjunto, :prio, 'Abierto')");
        $stmt->execute([
            ':uid' => $usuario_id,
            ':asunto' => $asunto,
            ':msg' => $mensaje,
            ':adjunto' => $nombre_adjunto,
            ':prio' => $prioridad
        ]);
        $_SESSION['success'] = "¡Ticket creado exitosamente! Recibirás una respuesta pronto.";
    } else {
        $_SESSION['error'] = "El asunto y el mensaje no pueden estar vacíos.";
    }
    
    header('Location: ../ticket.php');
    exit();
}

// --- ACCIÓN: AÑADIR RESPUESTA A UN TICKET EXISTENTE ---
if (isset($_POST['responder_ticket'])) {
    $ticket_id = filter_input(INPUT_POST, 'ticket_id', FILTER_VALIDATE_INT);
    $mensaje = trim($_POST['mensaje']);

    if ($ticket_id && !empty($mensaje)) {
        $stmt_check = $conn->prepare("SELECT id FROM tickets WHERE id = :tid AND usuario_id = :uid");
        $stmt_check->execute([':tid' => $ticket_id, ':uid' => $usuario_id]);
        
        if ($stmt_check->fetch()) {
            $stmt_insert = $conn->prepare("INSERT INTO ticket_respuestas (ticket_id, usuario_id, mensaje) VALUES (:tid, :uid, :msg)");
            $stmt_insert->execute([':tid' => $ticket_id, ':uid' => $usuario_id, ':msg' => $mensaje]);
            $stmt_update = $conn->prepare("UPDATE tickets SET estado = 'Abierto', ultima_actualizacion = NOW() WHERE id = :tid");
            $stmt_update->execute([':tid' => $ticket_id]);
            $_SESSION['success'] = "Respuesta enviada correctamente.";
        } else {
            $_SESSION['error'] = "No tienes permiso para responder a este ticket.";
        }
    } else {
        $_SESSION['error'] = "La respuesta no puede estar vacía.";
    }
    header('Location: ../ver_ticket_usuario.php?id=' . $ticket_id);
    exit();
}

header('Location: ../ticket.php');
exit();