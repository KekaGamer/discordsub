<?php
require_once '../includes/config.php';
require_once '../includes/auth.php';
require_once '../includes/database.php';
require_once '../includes/functions.php';

requireLogin();
$db = new Database();
$conn = $db->connect();

$suscripcion_id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
if (!$suscripcion_id) {
    header('Location: perfil.php');
    exit();
}

// Procesamiento del formulario
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['comprobante'])) {
    // ... (El código de validación y subida del archivo no cambia)
    $suscripcion_post_id = filter_input(INPUT_POST, 'suscripcion_id', FILTER_VALIDATE_INT);
    $usuario_id = $_SESSION['usuario_id'];
    $query_check = "SELECT usuario_id FROM suscripciones WHERE id = :id";
    $stmt_check = $conn->prepare($query_check);
    $stmt_check->execute([':id' => $suscripcion_post_id]);
    $suscripcion = $stmt_check->fetch();
    if (!$suscripcion || $suscripcion['usuario_id'] != $usuario_id) {
        $_SESSION['error'] = "Acción no permitida.";
        header('Location: perfil.php');
        exit();
    }
    $file = $_FILES['comprobante'];
    if ($file['error'] === UPLOAD_ERR_OK) {
        $target_dir = "../uploads/comprobantes/";
        if (!is_dir($target_dir)) { mkdir($target_dir, 0755, true); }
        $file_ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        $new_filename = "comp_" . $suscripcion_post_id . "_" . time() . "." . $file_ext;
        $target_file = $target_dir . $new_filename;
        if (move_uploaded_file($file['tmp_name'], $target_file)) {
            $update_query = "UPDATE suscripciones SET comprobante_pago = :comprobante WHERE id = :id";
            $update_stmt = $conn->prepare($update_query);
            $update_stmt->execute([':comprobante' => $new_filename, ':id' => $suscripcion_post_id]);
            registrarActividad($conn, $usuario_id, 'Subida de comprobante', "Suscripción ID: $suscripcion_post_id");
            
            // =======================================================================
            // INICIO: LÓGICA DE ENVÍO DE CORREOS CON NUEVO DISEÑO
            // =======================================================================
            try {
                $usuario_info = obtenerUsuario($conn, $usuario_id);
                $nombre_usuario = $usuario_info['nombre'];
                $discord_usuario = $usuario_info['discord'];
                $correo_usuario = $usuario_info['correo'];

                // --- 1. Preparar y ENVIAR CORREO AL ADMINISTRADOR ---
                $asunto_admin = "🔔 [Alerta] Nuevo Comprobante de Pago Recibido";
                $subtitulo_admin = "Usuario: " . htmlspecialchars($discord_usuario);
                $mensaje_admin = "Se ha recibido un nuevo comprobante para una suscripción. Aquí están los detalles:<br><br><ul style='list-style: none; padding: 0;'><li><b>Nombre:</b> " . htmlspecialchars($nombre_usuario) . "</li><li><b>Correo:</b> " . htmlspecialchars($correo_usuario) . "</li></ul><br>Por favor, revisa la solicitud pendiente en el panel.";
                $url_boton_admin = SITE_URL . "admin/suscripciones.php?estado=pendiente";
                $cuerpo_admin = generarCuerpoCorreoHTML('Nuevo Comprobante Recibido', $subtitulo_admin, $mensaje_admin, "Revisar Suscripción", $url_boton_admin);
                
                enviarCorreo(COMPROBANTE_EMAIL, 'Admin Comprobantes', $asunto_admin, $cuerpo_admin);

                // --- 2. Preparar y ENVIAR CORREO DE CONFIRMACIÓN AL USUARIO ---
                $asunto_usuario = "✅ Hemos Recibido tu Comprobante | Suscripción KekaGamer";
                $subtitulo_usuario = "¡Gracias por tu pago, " . htmlspecialchars($nombre_usuario) . "!";
                $mensaje_usuario = "Te confirmamos que hemos recibido tu comprobante. Nuestro equipo lo revisará a la brevedad y activará tu suscripción. Recibirás otro correo cuando tu plan esté activo.<br><br><b>Próximos pasos:</b><br>1. Revisión interna del comprobante.<br>2. Aprobación y activación de tu plan.<br>3. ¡A disfrutar del contenido!";
                $url_boton_usuario = SITE_URL . "usuario/perfil.php";
                $cuerpo_usuario = generarCuerpoCorreoHTML('Comprobante Recibido', $subtitulo_usuario, $mensaje_usuario, "Ver Estado en Mi Perfil", $url_boton_usuario);
                
                enviarCorreo($correo_usuario, $nombre_usuario, $asunto_usuario, $cuerpo_usuario);

            } catch (Exception $e) {
                error_log("Fallo al enviar correos de notificación de comprobante: " . $e->getMessage());
            }
            // =======================================================================
            // FIN: LÓGICA DE ENVÍO DE CORREOS
            // =======================================================================

            $_SESSION['success'] = "Comprobante subido correctamente. Será revisado por un administrador pronto.";
            header('Location: perfil.php');
            exit();
        }
    }
    $_SESSION['error'] = "Hubo un error al subir el archivo.";
}

// El resto del archivo (la parte HTML) no necesita cambios.
$pageTitle = "Subir Comprobante";
include '../includes/header.php'; 
?>
<body class="bg-gray-900 text-white">
    <?php include '../includes/navbar.php'; ?>
    <div class="container mx-auto px-4 py-8">
        <div class="max-w-2xl mx-auto bg-gray-800 rounded-lg shadow-lg p-8">
            <h1 class="text-3xl font-bold text-purple-500 mb-4">Subir Comprobante de Pago</h1>
            <p class="text-gray-400 mb-6">Para activar tu suscripción, realiza la transferencia y sube una foto o PDF del comprobante.</p>

            <div class="bg-gray-700 p-6 rounded-lg mb-6">
                <h3 class="font-bold text-lg mb-2 text-white">Datos para Transferencia:</h3>
                <ul class="space-y-2 text-gray-300">
                    <li><span class="font-semibold text-gray-100">Banco:</span> Mercado Pago</li>
                        <li><span class="font-semibold text-gray-100">Tipo de cuenta:</span> Cuenta Vista</li>
                        <li><span class="font-semibold text-gray-100">Número de cuenta:</span> 1087525538</li>
                        <li><span class="font-semibold text-gray-100">Titular:</span> Rodrigo Gonzalez Cerpa</li>
                        <li><span class="font-semibold text-gray-100">RUT:</span> 17.877.920-6</li>
                        <li><span class="font-semibold text-gray-100">Email para confirmación:</span> comprobante@kekagamer.cl</li>
                </ul>
            </div>

            <form action="" method="POST" enctype="multipart/form-data">
                <input type="hidden" name="suscripcion_id" value="<?= $suscripcion_id ?>">
                <div class="mb-4">
                    <label for="comprobante" class="block text-sm font-medium text-gray-300 mb-2">Selecciona tu archivo (JPG, PNG, PDF)</label>
                    <input type="file" name="comprobante" id="comprobante" required class="block w-full text-sm text-gray-400 file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-sm file:font-semibold file:bg-purple-500 file:text-white hover:file:bg-purple-600">
                </div>
                <button type="submit" class="w-full bg-green-600 hover:bg-green-700 text-white font-bold py-2 px-4 rounded">
                    Subir y Finalizar
                </button>
            </form>
        </div>
    </div>
</body>
</html>