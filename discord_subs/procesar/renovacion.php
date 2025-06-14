<?php
session_start();
require_once '../includes/config.php';
require_once '../includes/auth.php';
require_once '../includes/functions.php';

requireLogin();

if($_SERVER['REQUEST_METHOD'] === 'POST') {
    $tipo = $_POST['tipo'];
    $monto = $_POST['monto'];
    $usuario_id = $_SESSION['usuario_id'];
    
    // Obtener usuario
    $db = new Database();
    $conn = $db->connect();
    
    $query = "SELECT * FROM usuarios WHERE id = :usuario_id LIMIT 1";
    $stmt = $conn->prepare($query);
    $stmt->bindParam(':usuario_id', $usuario_id);
    $stmt->execute();
    
    $usuario = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if(!$usuario) {
        $_SESSION['error'] = "Usuario no encontrado.";
        header('Location: ../login.php');
        exit();
    }
    
    // Calcular fechas
    $fecha_inicio = date('Y-m-d H:i:s');
    $fecha_termino = calcularFechaTermino($tipo);
    
    // Crear suscripción pendiente
    $query = "INSERT INTO suscripciones (usuario_id, tipo, monto, fecha_inicio, fecha_termino, estado, metodo_pago) 
              VALUES (:usuario_id, :tipo, :monto, :fecha_inicio, :fecha_termino, 'pendiente', 'transferencia')";
    
    $stmt = $conn->prepare($query);
    $stmt->bindParam(':usuario_id', $usuario_id);
    $stmt->bindParam(':tipo', $tipo);
    $stmt->bindParam(':monto', $monto);
    $stmt->bindParam(':fecha_inicio', $fecha_inicio);
    $stmt->bindParam(':fecha_termino', $fecha_termino);
    
    if($stmt->execute()) {
        $suscripcion_id = $conn->lastInsertId();
        
        // Registrar actividad
        registrarActividad($usuario_id, 'Renovación de suscripción', "Tipo: $tipo, Monto: $monto CLP");
        
        // Enviar correo de confirmación
        $asunto = "Solicitud de renovación de suscripción";
        $cuerpo = "<h2>¡Gracias por renovar tu suscripción!</h2>
                  <p>Has solicitado renovar tu suscripción con el plan <strong>$tipo</strong> por <strong>$monto CLP</strong>.</p>
                  <p>Por favor, realiza el pago según las instrucciones y sube tu comprobante para activar tu suscripción.</p>
                  <p>Fecha de inicio: $fecha_inicio</p>
                  <p>Fecha de término: $fecha_termino</p>
                  <a href='".SITE_URL."/usuario/perfil.php'>Ver mi perfil</a>";
        
        enviarCorreo($usuario['correo'], $asunto, $cuerpo);
        
        // Redirigir a perfil con mensaje de éxito
        $_SESSION['success'] = "Solicitud de renovación enviada. Por favor, sube tu comprobante de pago.";
        header('Location: ../usuario/perfil.php');
        exit();
    } else {
        $_SESSION['error'] = "Error al procesar la renovación. Por favor, inténtalo de nuevo.";
        header('Location: ../usuario/renovar.php');
        exit();
    }
} else {
    header('Location: ../usuario/renovar.php');
    exit();
}
?>