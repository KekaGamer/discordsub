<?php
require_once 'config.php';
require_once 'database.php';

function enviarCorreo($destinatario, $asunto, $cuerpo) {
    try {
        require_once dirname(__DIR__) . '/vendor/autoload.php';
        
        $mail = new PHPMailer\PHPMailer\PHPMailer(true);
        $mail->isSMTP();
        $mail->Host = SMTP_HOST;
        $mail->SMTPAuth = true;
        $mail->Username = SMTP_USER;
        $mail->Password = SMTP_PASS;
        $mail->SMTPSecure = SMTP_SECURE;
        $mail->Port = SMTP_PORT;
        $mail->CharSet = 'UTF-8';

        $mail->setFrom(SMTP_USER, 'Sistema de Suscripciones Discord');
        $mail->addAddress($destinatario);
        $mail->addBCC(ADMIN_EMAIL);
        
        $mail->isHTML(true);
        $mail->Subject = $asunto;
        $mail->Body = $cuerpo;
        
        return $mail->send();
    } catch (Exception $e) {
        error_log("Error al enviar correo: {$e->getMessage()}");
        return false;
    }
}

function calcularFechaTermino($tipo_suscripcion, $fecha_inicio_str = 'now') {
    $fecha = new DateTime($fecha_inicio_str);
    
    switch($tipo_suscripcion) {
        case '1 mes': $fecha->add(new DateInterval('P1M')); break;
        case '6 meses': $fecha->add(new DateInterval('P6M')); break;
        case '1 año': $fecha->add(new DateInterval('P1Y')); break;
        default: return null;
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
        $stmt->bindValue(':usuario_id', $usuario_id ?: null, PDO::PARAM_INT);
        $stmt->bindParam(':accion', $accion, PDO::PARAM_STR);
        $stmt->bindParam(':detalles', $detalles, PDO::PARAM_STR);
        
        return $stmt->execute();
    } catch(PDOException $e) {
        error_log("Error al registrar actividad: " . $e->getMessage());
        return false;
    }
}

function obtenerUsuario($conn, $id) {
    try {
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

function obtenerSuscripcionActiva($conn, $usuario_id) {
    $query = "SELECT * FROM suscripciones WHERE usuario_id = :usuario_id AND estado = 'activa' AND fecha_termino > NOW() ORDER BY fecha_termino DESC LIMIT 1";
    $stmt = $conn->prepare($query);
    $stmt->bindParam(':usuario_id', $usuario_id, PDO::PARAM_INT);
    $stmt->execute();
    return $stmt->fetch(PDO::FETCH_ASSOC);
}


// --- NUEVAS FUNCIONES AÑADIDAS ---

function limpiarEntrada($data) {
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data);
    return $data;
}

/**
 * Envía un SMS usando un servicio externo (API).
 * ESTA ES UNA FUNCIÓN DE EJEMPLO. Debes reemplazarla con la lógica de tu proveedor de SMS.
 *
 * @param string $numeroDestino El número de teléfono del destinatario.
 * @param string $mensaje El mensaje a enviar.
 * @return bool True si el envío fue exitoso (simulado), false en caso contrario.
 */
function enviarSMS($numeroDestino, $mensaje) {
    // ** IMPORTANTE **
    // Aquí es donde debes integrar la API de tu proveedor de SMS (Twilio, Vonage, etc.)
    // El siguiente código es un ejemplo conceptual de cómo podría ser con cURL.

    /*
    $apiKey = SMS_API_KEY;
    $apiSecret = SMS_API_SECRET;
    $endpoint = 'https://api.proveedorsms.com/send'; // URL del proveedor

    $postData = [
        'key' => $apiKey,
        'secret' => $apiSecret,
        'to' => $numeroDestino,
        'message' => $mensaje
    ];

    $ch = curl_init($endpoint);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($postData));
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    if ($httpCode == 200) {
        // El SMS se envió correctamente según la API
        // Es una buena idea registrar la actividad, pero necesitamos la conexión a la BD
        // registrarActividad($conn, null, 'Envío de SMS exitoso', "A: $numeroDestino");
        return true;
    } else {
        // Hubo un error
        error_log("Error al enviar SMS a $numeroDestino. Respuesta: $response");
        return false;
    }
    */

    // Por ahora, para simular que funciona, siempre devolveremos true.
    // En un entorno real, esta línea debe ser eliminada.
    error_log("SIMULACIÓN: SMS enviado a $numeroDestino con mensaje: \"$mensaje\"");
    return true;
}