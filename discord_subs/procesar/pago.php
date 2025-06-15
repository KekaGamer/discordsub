<?php
require_once '../includes/config.php';
require_once '../includes/auth.php';
require_once '../includes/database.php'; // <-- LÍNEA FALTANTE AÑADIDA
require_once '../includes/functions.php';

requireLogin();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ../planes.php');
    exit();
}

try {
    $tipo = limpiarEntrada($_POST['tipo']);
    $monto = filter_var($_POST['monto'], FILTER_VALIDATE_FLOAT);
    $usuario_id = $_SESSION['usuario_id'];

    if (!$tipo || !$monto) {
        throw new Exception("Datos de plan inválidos.");
    }
    
    // Usamos la función calcularFechaTermino que ya existe
    $fecha_inicio = date('Y-m-d H:i:s');
    $fecha_termino = calcularFechaTermino($tipo, $fecha_inicio);

    if (!$fecha_termino) {
        throw new Exception("Tipo de suscripción no válido.");
    }

    $db = new Database();
    $conn = $db->connect();

    // Revisar si ya existe una suscripción pendiente para evitar duplicados
    $query_check = "SELECT id FROM suscripciones WHERE usuario_id = :usuario_id AND estado = 'pendiente'";
    $stmt_check = $conn->prepare($query_check);
    $stmt_check->bindParam(':usuario_id', $usuario_id);
    $stmt_check->execute();
    if($stmt_check->fetch()) {
        $_SESSION['error'] = "Ya tienes una solicitud de suscripción pendiente. Por favor, sube el comprobante o espera la aprobación.";
        header('Location: ../usuario/perfil.php');
        exit();
    }
    
    // Crear la nueva suscripción con estado 'pendiente'
    $query = "INSERT INTO suscripciones (usuario_id, tipo, monto, fecha_inicio, fecha_termino, estado, metodo_pago) 
              VALUES (:usuario_id, :tipo, :monto, :fecha_inicio, :fecha_termino, 'pendiente', 'transferencia')";
    
    $stmt = $conn->prepare($query);
    $stmt->bindParam(':usuario_id', $usuario_id);
    $stmt->bindParam(':tipo', $tipo);
    $stmt->bindParam(':monto', $monto);
    $stmt->bindParam(':fecha_inicio', $fecha_inicio);
    $stmt->bindParam(':fecha_termino', $fecha_termino);
    
    if ($stmt->execute()) {
        $suscripcion_id = $conn->lastInsertId();
        
        // Usamos la función registrarActividad que ya existe
        registrarActividad($conn, $usuario_id, 'Solicitud de suscripción creada', "Plan: $tipo, ID: $suscripcion_id");
        
        $_SESSION['success'] = "Tu solicitud ha sido creada. Ahora, por favor, sube el comprobante de pago.";
        header('Location: ../usuario/subir_comprobante.php?id=' . $suscripcion_id);
        exit();
    } else {
        throw new Exception("No se pudo crear la solicitud de suscripción.");
    }

} catch (Exception $e) {
    // Si algo falla, se registra el error y se muestra el mensaje genérico
    error_log("Error en pago.php: " . $e->getMessage());
    $_SESSION['error'] = "Ocurrió un error al procesar tu solicitud. Inténtalo de nuevo.";
    header('Location: ../planes.php');
    exit();
}
?>