<?php
// procesar/admin_anuncios.php (ACTUALIZADO CON NOTIFICACIONES Y PLANTILLA DE CORREO)
require_once '../includes/config.php';
require_once '../includes/auth.php';
require_once '../includes/database.php';
require_once '../includes/functions.php'; // Requerido para la nueva función enviarCorreo()
requireAdmin();

$db = new Database();
$conn = $db->connect();
$accion = $_POST['accion'] ?? '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    
    // --- ACCIÓN: CREAR UN ANUNCIO Y NOTIFICAR ---
    if ($accion === 'crear' && !empty($_POST['titulo']) && !empty($_POST['mensaje'])) {
        $titulo_anuncio = $_POST['titulo'];
        $mensaje_anuncio = $_POST['mensaje'];

        // 1. Guardar el anuncio en la base de datos
        $stmt_anuncio = $conn->prepare("INSERT INTO anuncios (titulo, mensaje) VALUES (:titulo, :mensaje)");
        $stmt_anuncio->execute([':titulo' => $titulo_anuncio, ':mensaje' => $mensaje_anuncio]);

        // --- INICIO: LÓGICA DE NOTIFICACIÓN MASIVA CON PLANTILLA ---

        // 2. Obtener todos los usuarios activos para notificarles
        $usuarios_stmt = $conn->query("SELECT id, correo, nombre FROM usuarios WHERE estado = 'activo'");
        $usuarios_a_notificar = $usuarios_stmt->fetchAll(PDO::FETCH_ASSOC);

        $asunto_email = "📢 Nuevo Anuncio de KekaGamer: " . $titulo_anuncio;
        $link_notificacion = SITE_URL . 'comunidad.php';
        $mensaje_notificacion_corta = "Nuevo anuncio: '" . $titulo_anuncio . "'. ¡Entérate de las novedades!";

        // Preparamos la consulta para la notificación interna una vez
        $notificacion_stmt = $conn->prepare("INSERT INTO notificaciones (usuario_id, mensaje, link) VALUES (?, ?, ?)");

        foreach ($usuarios_a_notificar as $usuario) {
            // 3. Crear notificación en la bandeja de entrada del sitio
            $notificacion_stmt->execute([$usuario['id'], $mensaje_notificacion_corta, $link_notificacion]);

            // 4. Preparar y enviar correo usando la nueva función con plantilla
            $cuerpo_mensaje_email = "Hola " . htmlspecialchars($usuario['nombre']) . ",<br><br>Tenemos un nuevo anuncio importante en la comunidad:<br><br>" . nl2br(htmlspecialchars($mensaje_anuncio));

            enviarCorreo(
                $usuario['correo'],
                $asunto_email,
                $titulo_anuncio,          // El título principal dentro del correo
                $cuerpo_mensaje_email,   // El cuerpo del mensaje
                "Ver Anuncios",           // El texto del botón
                $link_notificacion        // La URL del botón
            );
        }
        // --- FIN: LÓGICA DE NOTIFICACIÓN MASIVA ---
    }

    // --- ACCIÓN: ELIMINAR UN ANUNCIO ---
    if ($accion === 'eliminar' && !empty($_POST['id'])) {
        $stmt = $conn->prepare("DELETE FROM anuncios WHERE id = :id");
        $stmt->execute([':id' => $_POST['id']]);
    }
}

header('Location: ../admin/anuncios.php');
exit();
?>