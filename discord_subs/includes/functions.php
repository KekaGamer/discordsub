<?php
require_once 'config.php';
require_once 'database.php';

function enviarCorreo($destinatario, $asunto, $cuerpo) {
    try {
        require_once 'vendor/autoload.php';
        
        $mail = new PHPMailer\PHPMailer\PHPMailer(true);
        $mail->isSMTP();
        $mail->Host = SMTP_HOST;
        $mail->SMTPAuth = true;
        $mail->Username = SMTP_USER;
        $mail->Password = SMTP_PASS;
        $mail->SMTPSecure = SMTP_SECURE;
        $mail->Port = SMTP_PORT;
        
        $mail->setFrom(SMTP_USER, 'Sistema de Suscripciones Discord');
        $mail->addAddress($destinatario);
        $mail->addAddress(ADMIN_EMAIL);
        
        $mail->isHTML(true);
        $mail->Subject = $asunto;
        $mail->Body = $cuerpo;
        
        return $mail->send();
    } catch (Exception $e) {
        error_log("Error al enviar correo: {$e->getMessage()}");
        return false;
    }
}

function calcularFechaTermino($tipo_suscripcion) {
    $fecha = new DateTime();
    
    switch($tipo_suscripcion) {
        case '1 mes': $fecha->add(new DateInterval('P1M')); break;
        case '6 meses': $fecha->add(new DateInterval('P6M')); break;
        case '1 año': $fecha->add(new DateInterval('P1Y')); break;
        default: throw new Exception("Tipo de suscripción inválido");
    }
    
    return $fecha->format('Y-m-d H:i:s');
}

function registrarActividad($usuario_id, $accion, $detalles = null) {
    try {
        $db = new Database();
        $conn = $db->connect();
        
        $query = "INSERT INTO actividades (usuario_id, accion, detalles) 
                 VALUES (:usuario_id, :accion, :detalles)";
        
        $stmt = $conn->prepare($query);
        $stmt->bindParam(':usuario_id', $usuario_id, PDO::PARAM_INT);
        $stmt->bindParam(':accion', $accion, PDO::PARAM_STR);
        $stmt->bindParam(':detalles', $detalles, PDO::PARAM_STR);
        
        return $stmt->execute();
    } catch(PDOException $e) {
        error_log("Error al registrar actividad: " . $e->getMessage());
        return false;
    }
}

function obtenerUsuario($id) {
    try {
        $db = new Database();
        $conn = $db->connect();
        
        $query = "SELECT * FROM usuarios WHERE id = :id LIMIT 1";
        $stmt = $conn->prepare($query);
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);
        $stmt->execute();
        
        return $stmt->fetch(PDO::FETCH_ASSOC);
    } catch(PDOException $e) {
        error_log("Error al obtener usuario: " . $e->getMessage());
        return false;
    }
}