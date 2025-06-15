<?php
require_once '../includes/config.php';
require_once '../includes/database.php';
require_once '../includes/functions.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ' . SITE_URL . 'login.php');
    exit();
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
                // --- Flujo por Correo ---
                $token = bin2hex(random_bytes(50));
                $stmt_insert = $conn->prepare("INSERT INTO password_resets (correo, token, fecha_expiracion) VALUES (?, ?, ?)");
                $stmt_insert->execute([$usuario['correo'], $token, $fecha_expiracion]);
                
                $reset_link = SITE_URL . 'reset_password.php?token=' . $token;
                $asunto = 'Recuperación de Contraseña - Discord Subs';
                $cuerpo_html = "<p>Hola " . htmlspecialchars($usuario['nombre']) . ", haz clic en el siguiente enlace para restablecer tu contraseña: <a href='{$reset_link}'>Restablecer Contraseña</a>. El enlace expira en 1 hora.</p>";
                enviarCorreo($usuario['correo'], $usuario['nombre'], $asunto, $cuerpo_html);

                $_SESSION['success'] = "Si tu correo está registrado, recibirás un enlace para restablecer tu contraseña.";
                header('Location: ../olvido_password.php');
                exit();

            } else {
                // --- Flujo por Teléfono (SMS) ---
                $codigo = random_int(100000, 999999); // Código de 6 dígitos
                $stmt_insert = $conn->prepare("INSERT INTO password_resets (correo, token, fecha_expiracion) VALUES (?, ?, ?)");
                $stmt_insert->execute([$usuario['correo'], $codigo, $fecha_expiracion]);

                $mensaje = "Tu código de recuperación para Discord Subs es: " . $codigo;
                enviarSMS($usuario['telefono'], $mensaje); // Usamos la nueva función

                $_SESSION['success'] = "Hemos enviado un código a tu teléfono.";
                header('Location: ../verificar_codigo.php?email=' . urlencode($usuario['correo']));
                exit();
            }
        }
        
        $_SESSION['success'] = "Si tu correo o teléfono están registrados, recibirás instrucciones para restablecer tu contraseña.";
        header('Location: ../olvido_password.php');
        exit();

    } elseif ($action === 'verify_code') {
        // --- Lógica para verificar el código SMS ---
        $codigo = limpiarEntrada($_POST['codigo']);
        $correo = limpiarEntrada($_POST['correo']);

        $stmt = $conn->prepare("SELECT * FROM password_resets WHERE correo = ? AND token = ? AND fecha_expiracion > NOW()");
        $stmt->execute([$correo, $codigo]);
        $reset_request = $stmt->fetch();
        
        if ($reset_request) {
            // El código es correcto. Generar un token seguro para el link de reseteo final.
            $token_final = bin2hex(random_bytes(50));
            $expira = new DateTime('+15 minutes'); // Dar 15 minutos para cambiar la contraseña
            
            $stmt_update = $conn->prepare("UPDATE password_resets SET token = ? WHERE id = ?");
            $stmt_update->execute([$token_final, $reset_request['id']]);

            header('Location: ../reset_password.php?token=' . $token_final);
            exit();
        } else {
            $_SESSION['error'] = "El código es incorrecto o ha expirado.";
            header('Location: ../verificar_codigo.php?email=' . urlencode($correo));
            exit();
        }
        
    } elseif ($action === 'reset') {
        // --- Lógica para restablecer la contraseña (sin cambios) ---
        $token = $_POST['token'] ?? '';
        $password = $_POST['password'] ?? '';
        $confirm_password = $_POST['confirm_password'] ?? '';

        if (empty($token) || empty($password) || $password !== $confirm_password) {
            $_SESSION['error'] = "Datos inválidos o las contraseñas no coinciden.";
            header('Location: ../reset_password.php?token=' . urlencode($token));
            exit();
        }

        $query = "SELECT correo FROM password_resets WHERE token = :token AND fecha_expiracion > NOW()";
        $stmt = $conn->prepare($query);
        $stmt->execute([':token' => $token]);
        $reset_request = $stmt->fetch();

        if (!$reset_request) {
            $_SESSION['error'] = "El token es inválido o ha expirado.";
            header('Location: ../olvido_password.php');
            exit();
        }

        $correo = $reset_request['correo'];
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);
        
        $conn->prepare("UPDATE usuarios SET password = ? WHERE correo = ?")->execute([$hashed_password, $correo]);
        $conn->prepare("DELETE FROM password_resets WHERE correo = ?")->execute([$correo]);
        
        registrarActividad($conn, null, 'Contraseña restablecida', "Correo: $correo");

        $_SESSION['success'] = "Tu contraseña ha sido actualizada exitosamente. Ahora puedes iniciar sesión.";
        header('Location: ../login.php');
        exit();
    }

} catch (Exception $e) {
    error_log("Error en recuperar.php: " . $e->getMessage());
    $_SESSION['error'] = "Ocurrió un error en el servidor.";
    header('Location: ../olvido_password.php');
    exit();
}