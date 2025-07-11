<?php
// scripts/notificar_expiracion.php (ACTUALIZADO CON PLANTILLA DE CORREO)
// Este script debe ser ejecutado por un Cron Job una vez al día.
// Comando Cron: php /ruta/completa/a/tu/proyecto/scripts/notificar_expiracion.php

// Ignorar límite de tiempo de ejecución
set_time_limit(0); 

// Usamos dirname(__DIR__) para asegurar que la ruta a los includes sea correcta
require_once dirname(__DIR__) . '/includes/config.php';
require_once dirname(__DIR__) . '/includes/database.php';
require_once dirname(__DIR__) . '/includes/functions.php';

echo "Iniciando script de notificación de expiración...\n";

try {
    $db = new Database();
    $conn = $db->connect();

    $dias_antes = defined('NOTIFICACION_DIAS_ANTES_EXPIRAR') ? NOTIFICACION_DIAS_ANTES_EXPIRAR : 7;
    $fecha_limite = new DateTime();
    $fecha_limite->add(new DateInterval("P{$dias_antes}D"));
    $fecha_limite_str = $fecha_limite->format('Y-m-d');

    // Buscar suscripciones que expiran exactamente en N días
    $query = "SELECT u.nombre, u.correo, s.tipo, s.fecha_termino
              FROM suscripciones s
              JOIN usuarios u ON s.usuario_id = u.id
              WHERE s.estado = 'activa' AND DATE(s.fecha_termino) = :fecha_limite";

    $stmt = $conn->prepare($query);
    $stmt->execute([':fecha_limite' => $fecha_limite_str]);
    $suscripciones_a_notificar = $stmt->fetchAll(PDO::FETCH_ASSOC);

    if (count($suscripciones_a_notificar) === 0) {
        echo "No hay suscripciones que expiren en {$dias_antes} días. Saliendo.\n";
        exit;
    }

    echo "Encontradas " . count($suscripciones_a_notificar) . " suscripciones para notificar.\n";

    foreach ($suscripciones_a_notificar as $suscripcion) {
        $nombre_usuario = $suscripcion['nombre'];
        $correo_usuario = $suscripcion['correo'];
        $fecha_termino_display = date('d/m/Y', strtotime($suscripcion['fecha_termino']));
        
        // ### INICIO DE BLOQUE MODIFICADO ###
        // Preparamos los datos para la nueva función enviarCorreo con plantilla
        
        $asunto_email = "⚠️ Tu suscripción de KekaGamer está por expirar";
        $titulo_mensaje = "Recordatorio de Expiración";
        $cuerpo_mensaje = "Hola " . htmlspecialchars($nombre_usuario) . ",<br><br>Este es un recordatorio amistoso de que tu suscripción al plan <strong>" . htmlspecialchars($suscripcion['tipo']) . "</strong> está a punto de expirar el día <strong>" . $fecha_termino_display . "</strong>.<br><br>Para no perderte ninguna transmisión, te recomendamos renovar tu plan haciendo clic en el botón de abajo.";
        $texto_boton = "Renovar Mi Suscripción";
        $url_boton = SITE_URL . "usuario/renovar.php";

        if (enviarCorreo($correo_usuario, $asunto_email, $titulo_mensaje, $cuerpo_mensaje, $texto_boton, $url_boton)) {
            echo "Correo enviado a: {$correo_usuario}\n";
        } else {
            echo "FALLO al enviar correo a: {$correo_usuario}\n";
        }
        // ### FIN DE BLOQUE MODIFICADO ###
    }
    
    echo "Script de notificación finalizado.\n";

} catch (Exception $e) {
    error_log("Error en script de notificación de expiración: " . $e->getMessage());
    echo "ERROR: " . $e->getMessage() . "\n";
}
?>