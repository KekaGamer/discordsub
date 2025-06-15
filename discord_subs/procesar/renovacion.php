<?php
require_once '../includes/config.php';
require_once '../includes/auth.php';
require_once '../includes/database.php';
require_once '../includes/functions.php';

requireLogin();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ../usuario/renovar.php');
    exit();
}

try {
    $tipo = limpiarEntrada($_POST['tipo']);
    $monto = filter_var($_POST['monto'], FILTER_VALIDATE_FLOAT);
    $usuario_id = $_SESSION['usuario_id'];

    if (!$tipo || !$monto) {
        throw new Exception("Datos de plan inválidos para la renovación.");
    }
    
    $db = new Database();
    $conn = $db->connect();
    
    // Obtener la fecha de término de la suscripción activa actual
    $suscripcion_activa = obtenerSuscripcionActiva($conn, $usuario_id);
    $fecha_inicio_nueva = $suscripcion_activa ? $suscripcion_activa['fecha_termino'] : date('Y-m-d H:i:s');
    
    // Calcular la nueva fecha de término basándose en el final de la actual
    $fecha_termino_nueva = calcularFechaTermino($fecha_inicio_nueva, $tipo);

    if (!$fecha_termino_nueva) {
        throw new Exception("Tipo de suscripción no válido para la renovación.");
    }
    
    // Crear la nueva suscripción como pendiente
    $query = "INSERT INTO suscripciones (usuario_id, tipo, monto, fecha_inicio, fecha_termino, estado, metodo_pago) 
              VALUES (:usuario_id, :tipo, :monto, :fecha_inicio, :fecha_termino, 'pendiente', 'transferencia')";
    
    $stmt = $conn->prepare($query);
    $stmt->execute([
        ':usuario_id' => $usuario_id,
        ':tipo' => $tipo,
        ':monto' => $monto,
        ':fecha_inicio' => $fecha_inicio_nueva,
        ':fecha_termino' => $fecha_termino_nueva
    ]);
    
    $suscripcion_id = $conn->lastInsertId();
    registrarActividad($conn, $usuario_id, 'Solicitud de renovación creada', "Plan: $tipo, ID: $suscripcion_id");
    
    $_SESSION['success'] = "Tu solicitud de renovación ha sido creada. Por favor, sube el comprobante de pago.";
    header('Location: ../usuario/subir_comprobante.php?id=' . $suscripcion_id);
    exit();

} catch (Exception $e) {
    error_log("Error en renovacion.php: " . $e->getMessage());
    $_SESSION['error'] = "Ocurrió un error al procesar tu renovación.";
    header('Location: ../usuario/renovar.php');
    exit();
}