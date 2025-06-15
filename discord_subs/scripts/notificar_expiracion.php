<?php
// Este script debe ser ejecutado por un Cron Job una vez al día.
// Comando Cron: php /ruta/completa/a/tu/proyecto/scripts/notificar_expiracion.php

// Ignorar límite de tiempo de ejecución
set_time_limit(0); 

require_once dirname(__DIR__) . '/includes/config.php';
require_once dirname(__DIR__) . '/includes/database.php';
require_once dirname(__DIR__) . '/includes/functions.php';

echo "Iniciando script de notificación...\n";

try {
    $db = new Database();
    $conn = $db->connect();

    $dias_antes = NOTIFICACION_DIAS_ANTES_EXPIRAR;
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
    $suscripciones_a_notificar = $stmt->fetchAll();

    if (count($suscripciones_a_notificar) === 0) {
        echo "No hay suscripciones que expiren hoy en {$dias_antes} días. Saliendo.\n";
        exit;
    }

    echo "Encontradas " . count($suscripciones_a_notificar) . " suscripciones para notificar.\n";

    foreach ($suscripciones_a_notificar as $suscripcion) {
        $nombre = $suscripcion['nombre'];
        $correo = $suscripcion['correo'];
        $fecha_termino_display = date('d/m/Y', strtotime($suscripcion['fecha_termino']));
        
        $asunto = "⚠️ Tu suscripción está por expirar";
        $cuerpo_html = "
            <h1>¡Hola, " . htmlspecialchars($nombre) . "!</h1>
            <p>Este es un recordatorio amistoso de que tu suscripción al plan <strong>" . htmlspecialchars($suscripcion['tipo']) . "</strong> está a punto de expirar.</p>
            <p><strong>Fecha de expiración:</strong> " . $fecha_termino_display . "</p>
            <p>Para no perderte ninguna transmisión, te recomendamos renovar tu plan. Puedes hacerlo desde tu perfil en nuestro sitio web.</p>
            <a href='" . SITE_URL . "usuario/renovar.php' style='padding:10px 15px; background-color:#8b5cf6; color:white; text-decoration:none; border-radius:5px;'>Renovar Ahora</a>
            <p>Gracias por ser parte de nuestra comunidad.</p>";

        if (enviarCorreo($correo, $nombre, $asunto, $cuerpo_html)) {
            echo "Correo enviado a: {$correo}\n";
        } else {
            echo "FALLO al enviar correo a: {$correo}\n";
        }
    }
    
    echo "Script de notificación finalizado.\n";

} catch (Exception $e) {
    error_log("Error en script de notificación: " . $e->getMessage());
    echo "ERROR: " . $e->getMessage() . "\n";
}
?>