<?php
require_once 'config.php';
require_once 'database.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

function enviarCorreo($destinatario, $nombre_destinatario, $asunto, $cuerpo_html) {
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
        $mail->setFrom(SMTP_USER, MAIL_FROM_NAME);
        $mail->addAddress($destinatario, $nombre_destinatario);
        $mail->isHTML(true);
        $mail->Subject = $asunto;
        $mail->Body    = $cuerpo_html;
        $mail->AltBody = strip_tags(str_replace("<br>", "\n", $cuerpo_html));
        $mail->send();
        return true;
    } catch (Exception $e) {
        error_log("Error al enviar correo a {$destinatario}: {$mail->ErrorInfo}");
        return false;
    }
}

function generarCuerpoCorreoHTML($titulo, $subtitulo, $cuerpoMensaje, $textoBoton = '', $urlBoton = '') {
    $logoUrl = SITE_URL . 'assets/images/logo.png';
    $year = date("Y");
    $htmlBoton = '';
    if (!empty($textoBoton) && !empty($urlBoton)) {
        $htmlBoton = '<tr><td align="center" style="padding: 20px 0;"><table border="0" cellspacing="0" cellpadding="0"><tr><td align="center" style="border-radius: 8px;" bgcolor="#7c3aed"><a href="' . $urlBoton . '" target="_blank" style="font-size: 16px; font-weight: bold; color: #ffffff; text-decoration: none; border-radius: 8px; padding: 14px 28px; border: 1px solid #7c3aed; display: inline-block;">' . $textoBoton . '</a></td></tr></table></td></tr>';
    }
    return '<!DOCTYPE html><html lang="es"><head><meta charset="UTF-8"><title>' . htmlspecialchars($titulo) . '</title></head><body style="margin: 0; padding: 0; font-family: Arial, sans-serif; background-color: #111827; color: #e5e7eb;"><center><table width="600" style="background-color: #1f2937; margin: 20px auto; border-radius: 8px; overflow: hidden;"><tr><td align="center" style="padding: 20px 0; background-color: #111827;"><img src="' . $logoUrl . '" alt="Logo" style="max-height: 50px;"></td></tr><tr><td style="padding: 40px 30px;"><h1 style="color: #a78bfa; font-size: 24px;">' . htmlspecialchars($titulo) . '</h1><h2 style="color: #d1d5db; font-size: 20px; font-weight: normal;">' . htmlspecialchars($subtitulo) . '</h2><p style="font-size: 16px; line-height: 1.6;">' . $cuerpoMensaje . '</p></td></tr>' . $htmlBoton . '<tr><td align="center" style="padding: 20px 30px; background-color: #111827; font-size: 12px; color: #6b7280;">&copy; ' . $year . ' KekaGamer. Todos los derechos reservados.</td></tr></table></center></body></html>';
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