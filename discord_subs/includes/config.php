<?php
// Configuración de sesión
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Configuración básica
define('SITE_URL', 'http://localhost/');
define('ADMIN_EMAIL', 'gonzalez.cerpa.rodrigo.a@gmail.com');

// Configuración de la base de datos
define('DB_HOST', 'localhost');
define('DB_NAME', 'discord_subs');
define('DB_USER', 'root');
define('DB_PASS', '');

// Configuración de PHPMailer
define('SMTP_HOST', 'smtp.gmail.com');
define('SMTP_USER', 'tucorreo@gmail.com');
define('SMTP_PASS', 'tucontraseña');
define('SMTP_PORT', 587);
define('SMTP_SECURE', 'tls');

// Precios de suscripción
define('PRECIO_MES', 2500);
define('PRECIO_6MESES', 13500);
define('PRECIO_ANIO', 28500);

// Configuración de tiempo para notificaciones
define('NOTIFICACION_ANTES_EXPIRAR', 7);

// Configuración de errores (solo en desarrollo)
if (defined('ENVIRONMENT') && ENVIRONMENT === 'development') {
    ini_set('display_errors', 1);
    ini_set('display_startup_errors', 1);
    error_reporting(E_ALL);
} else {
    ini_set('display_errors', 0);
    ini_set('display_startup_errors', 0);
    error_reporting(E_ERROR | E_WARNING | E_PARSE);
}