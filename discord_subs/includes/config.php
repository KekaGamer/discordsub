<?php
// Configuración de sesión
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// --- CONFIGURACIÓN PRINCIPAL PARA PRODUCCIÓN ---

// URL de tu sitio web (IMPORTANTE: terminar con /)
define('SITE_URL', 'https://kekagamer.cl/');

// Correos electrónicos de la aplicación
define('ADMIN_EMAIL', 'admin@kekagamer.cl');
define('COMPROBANTE_EMAIL', 'comprobante@kekagamer.cl'); // Correo para recibir notificaciones de pagos

// Configuración de la base de datos
define('DB_HOST', 'localhost');
define('DB_NAME', 'cke110157_discord_subs');
define('DB_USER', 'cke110157_root');
define('DB_PASS', '%R0dr1g03009%');

// Configuración para el envío de correos (PHPMailer con SMTP)
define('SMTP_HOST', 'mail.kekagamer.cl');
define('SMTP_USER', 'admin@kekagamer.cl'); // El correo desde el que se enviarán los emails
define('SMTP_PASS', '%R0dr1g03009%');   // La contraseña de la cuenta de correo admin@kekagamer.cl
define('SMTP_PORT', 587);
define('SMTP_SECURE', 'tls');

// Nombre del remitente que verán los usuarios en los correos
define('MAIL_FROM_NAME', 'Suscripciones KekaGamer');

// --- CONFIGURACIÓN PARA SMS (CON placeholders para el servicio que elijas) ---
define('SMS_API_KEY', 'TU_API_KEY_DE_TWILIO_U_OTRO');
define('SMS_API_SECRET', 'TU_API_SECRET_DE_TWILIO_U_OTRO');
define('SMS_TWILIO_NUMBER', 'TU_NUMERO_DE_TELEFONO_DE_TWILIO'); // Necesario si usas el ejemplo de Twilio

// Precios de suscripción (CLP)
define('PRECIO_MES', 2500);
define('PRECIO_6MESES', 13500);
define('PRECIO_ANIO', 28500);

// Notificar a usuarios X días antes de que expire su suscripción
define('NOTIFICACION_DIAS_ANTES_EXPIRAR', 7);

// --- FIN DE LA CONFIGURACIÓN PRINCIPAL ---

// Configuración de entorno: 'production' para el sitio en vivo. Oculta errores detallados.
define('ENVIRONMENT', 'production');

if (ENVIRONMENT === 'development') {
    ini_set('display_errors', 1);
    error_reporting(E_ALL);
} else {
    ini_set('display_errors', 0);
    ini_set('log_errors', 1);
}
?>