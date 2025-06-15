<?php
// Configuración de sesión
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// --- CONFIGURACIÓN PRINCIPAL (EDITAR ESTOS VALORES) ---

// URL de tu sitio web (IMPORTANTE: terminar con /)
define('SITE_URL', 'http://localhost/');

// Email del administrador para recibir notificaciones
define('ADMIN_EMAIL', 'tu-admin-email@example.com');

// Configuración de la base de datos
define('DB_HOST', 'localhost');
define('DB_NAME', 'discord_subs');
define('DB_USER', 'root');
define('DB_PASS', '');

// Configuración para el envío de correos (PHPMailer con SMTP)
define('SMTP_HOST', 'smtp.example.com');
define('SMTP_USER', 'tu-email@example.com');
define('SMTP_PASS', 'tu-contraseña-email');
define('SMTP_PORT', 587);
define('SMTP_SECURE', 'tls');

// Nombre del remitente para los correos
define('MAIL_FROM_NAME', 'Discord Subs');

// --- NUEVA CONFIGURACIÓN PARA SMS ---
// Aquí deberás poner las credenciales del servicio de SMS que contrates
define('SMS_API_KEY', 'TU_API_KEY_DE_SMS');
define('SMS_API_SECRET', 'TU_API_SECRET_DE_SMS');


// Precios de suscripción (CLP)
define('PRECIO_MES', 2500);
define('PRECIO_6MESES', 13500);
define('PRECIO_ANIO', 28500);

// Notificar a usuarios X días antes de que expire su suscripción
define('NOTIFICACION_DIAS_ANTES_EXPIRAR', 7);

// --- FIN DE LA CONFIGURACIÓN PRINCIPAL ---

// Configuración de entorno (desarrollo o producción)
define('ENVIRONMENT', 'development');

if (ENVIRONMENT === 'development') {
    ini_set('display_errors', 1);
    error_reporting(E_ALL);
} else {
    ini_set('display_errors', 0);
    ini_set('log_errors', 1);
}
?>