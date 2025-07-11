<?php
require_once 'config.php';
require_once 'database.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

/**
 * Envía un correo electrónico utilizando una plantilla HTML profesional.
 *
 * @param string $destinatario Email del destinatario.
 * @param string $asunto Asunto del correo.
 * @param string $titulo_mensaje Título principal que aparecerá dentro del correo.
 * @param string $cuerpo_mensaje El párrafo principal del mensaje.
 * @param string $texto_boton (Opcional) El texto que aparecerá en el botón.
 * @param string $url_boton (Opcional) La URL a la que enlazará el botón.
 * @return bool True si se envió, false si hubo un error.
 */
function enviarCorreo($destinatario, $asunto, $titulo_mensaje, $cuerpo_mensaje, $texto_boton = '', $url_boton = '') {
    // Validar que la plantilla exista para evitar errores fatales.
    $template_path = __DIR__ . '/email_template.php';
    if (!file_exists($template_path)) {
        error_log("Error crítico: La plantilla de correo 'email_template.php' no se encuentra.");
        return false;
    }
    
    // Iniciar el buffer de salida para capturar el HTML de la plantilla.
    ob_start();
    // Incluir la plantilla. Las variables ($titulo_mensaje, etc.) estarán disponibles dentro de ella.
    include $template_path;
    // Obtener el contenido del buffer (el HTML completo) y limpiarlo.
    $cuerpo_html_completo = ob_get_clean();

    // Configuración de PHPMailer
    require_once dirname(__DIR__) . '/vendor/autoload.php';
    $mail = new PHPMailer(true);
    try {
        $mail->isSMTP();
        $mail->Host       = SMTP_HOST;
        $mail->SMTPAuth   = true;
        $mail->Username   = SMTP_USER;
        $mail->Password   = SMTP_PASS;
        $mail->SMTPSecure = SMTP_SECURE;
        $mail->Port       = SMTP_PORT;
        $mail->CharSet    = 'UTF-8';

        $mail->setFrom(SMTP_USER, 'KekaGamer Notificaciones');
        $mail->addAddress($destinatario);
        $mail->addBCC(ADMIN_EMAIL); // Opcional: siempre enviar una copia al admin

        $mail->isHTML(true);
        $mail->Subject = $asunto;
        $mail->Body    = $cuerpo_html_completo; // Usamos el HTML de la plantilla
        $mail->AltBody = strip_tags($cuerpo_mensaje); // Cuerpo de texto plano como alternativa

        return $mail->send();
    } catch (Exception $e) {
        error_log("Error al enviar correo a {$destinatario}: {$mail->ErrorInfo}");
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

function registrarActividad($conn, $usuario_id, $accion, $detalles = null) {
    try {
        $query = "INSERT INTO actividades (usuario_id, accion, detalles) VALUES (:usuario_id, :accion, :detalles)";
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
    try {
        $query = "SELECT * FROM suscripciones WHERE usuario_id = :usuario_id AND estado = 'activa' AND fecha_termino > NOW() ORDER BY fecha_termino DESC LIMIT 1";
        $stmt = $conn->prepare($query);
        $stmt->bindParam(':usuario_id', $usuario_id, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    } catch(PDOException $e) {
        error_log("Error al obtener suscripción: " . $e->getMessage());
        return false;
    }
}

function limpiarEntrada($data) {
    return trim($data);
}
?>