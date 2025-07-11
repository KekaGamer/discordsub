<?php
// procesar/recuperar.php (ACTUALIZADO CON PLANTILLA DE CORREO)
require_once '../includes/config.php';
require_once '../includes/database.php';
require_once '../includes/functions.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ' . SITE_URL . 'login.php');
    exit();
}

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$action = $_POST['action'] ?? 'request';

try {
    $db = new Database();
    $conn = $db->connect();

    if ($action === 'request') {
        // --- Lógica para solicitar la recuperación ---
        $identificador = limpiarEntrada($_POST['identificador']);
        $usuario = null;
        $es_email = filter_var($identificador, FILTER_VALIDATE_EMAIL);

        if ($es_email) {
            $stmt = $conn->prepare("SELECT id, nombre, correo FROM usuarios WHERE correo = ?");
            $stmt->execute([$identificador]);
            $usuario = $stmt->fetch();
        } else {
            // Asumimos que es un teléfono
            $stmt = $conn->prepare("SELECT id, nombre, correo, telefono FROM usuarios WHERE telefono = ?");
            $stmt->execute([$identificador]);
            $usuario = $stmt->fetch();
        }

        if ($usuario) {
            $expira = new DateTime('+1 hour');
            $fecha_expiracion = $expira->format('Y-m-d H:i:s');
            
            if ($es_email) {
                // ### INICIO DE BLOQUE MODIFICADO ###
                // Ahora usamos la nueva función enviarCorreo con la plantilla profesional.
                
                $token = bin2hex(random_bytes(50));
                $stmt_insert = $conn->prepare("INSERT INTO password_resets (correo, token, fecha_expiracion) VALUES (?, ?, ?)");
                $stmt_insert->execute([$usuario['correo'], $token, $fecha_expiracion]);
                
                $reset_link = SITE_URL . 'reset_password.php?token=' . $token;
                $asunto = 'Recuperación de Contraseña - KekaGamer';
                $titulo_mensaje = 'Solicitud de Recuperación de Contraseña';
                $cuerpo_mensaje = "Hola " . htmlspecialchars($usuario['nombre']) . ",<br><br>Recibimos una solicitud para restablecer tu contraseña. Haz clic en el botón de abajo para continuar. Si no lo solicitaste, puedes ignorar este correo.<br><br>El enlace expira en 1 hora.";

                enviarCorreo(
                    $usuario['correo'],
                    $asunto,
                    $titulo_mensaje,
                    $cuerpo_mensaje,
                    "Restablecer Contraseña",
                    $reset_link
                );
                // ### FIN DE BLOQUE MODIFICADO ###

                $_SESSION['success'] = "Si tu correo está registrado, recibirás un enlace para restablecer tu contraseña.";
                header('Location: ../olvido_password.php');
                exit();

            } else {
                // --- Flujo por Teléfono (SMS) - sin cambios ---
                $codigo = random_int(100000, 999999);
                $stmt_insert = $conn->prepare("INSERT INTO password_resets (correo, token, fecha_expiracion) VALUES (?, ?, ?)");
                $stmt_insert->execute([$usuario['correo'], $codigo, $fecha_expiracion]);

                $mensaje = "Tu código de recuperación para Discord Subs es: " . $codigo;
                // La función enviarSMS no existe en tu functions.php, asegúrate de que esté implementada si la usas.
                // enviarSMS($usuario['telefono'], $mensaje); 

                $_SESSION['success'] = "Hemos enviado un código a tu teléfono (función de SMS simulada).";
                header('Location: ../verificar_codigo.php?email=' . urlencode($usuario['correo']));
                exit();
            }
        }
        
        $_SESSION['success'] = "Si tu correo o teléfono están registrados, recibirás instrucciones para restablecer tu contraseña.";
        header('Location: ../olvido_password.php');
        exit();

    } elseif ($action === 'verify_code') {
        // --- Lógica para verificar el código SMS (sin cambios) ---
        // ...
        
    } elseif ($action === 'reset') {
        // --- Lógica para restablecer la contraseña (sin cambios) ---
        // ...
    }

} catch (Exception $e) {
    error_log("Error en recuperar.php: " . $e->getMessage());
    $_SESSION['error'] = "Ocurrió un error en el servidor.";
    header('Location: ../olvido_password.php');
    exit();
}
?>