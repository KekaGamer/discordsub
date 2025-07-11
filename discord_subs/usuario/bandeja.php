<?php
require_once '../includes/config.php';
require_once '../includes/auth.php';
require_once '../includes/database.php';
require_once '../includes/functions.php';

requireLogin();
$pageTitle = "Bandeja de Entrada";

$db = new Database();
$conn = $db->connect();
$usuario_id = $_SESSION['usuario_id'];

// Obtener notificaciones de la base de datos
$stmt = $conn->prepare("SELECT * FROM notificaciones WHERE usuario_id = :usuario_id ORDER BY fecha_creacion DESC");
$stmt->execute([':usuario_id' => $usuario_id]);
$notificaciones = $stmt->fetchAll();

// Generar notificación dinámica: Recordatorio de expiración
$suscripcion_activa = obtenerSuscripcionActiva($conn, $usuario_id);
if ($suscripcion_activa) {
    $fecha_termino = new DateTime($suscripcion_activa['fecha_termino']);
    $hoy = new DateTime();
    $diferencia = $hoy->diff($fecha_termino);
    $dias_restantes = $diferencia->days;

    if ($dias_restantes <= NOTIFICACION_DIAS_ANTES_EXPIRAR && !$diferencia->invert) {
        $notificacion_expiracion = [
            'mensaje' => "Tu suscripción vence en {$dias_restantes} días. Renueva ahora para no perderte nada.",
            'url' => SITE_URL . 'usuario/renovar.php',
            'fecha_creacion' => date('Y-m-d H:i:s'),
            'leido' => 1 // Para que no se muestre como "nueva" siempre
        ];
        array_unshift($notificaciones, $notificacion_expiracion);
    }
}
// NOTA: La lógica para "directo en curso" se añadiría aquí, consultando un estado global.

// Marcar todas las notificaciones como leídas al visitar la página
$stmt_leido = $conn->prepare("UPDATE notificaciones SET leido = 1 WHERE usuario_id = :usuario_id AND leido = 0");
$stmt_leido->execute([':usuario_id' => $usuario_id]);

include '../includes/header.php';
?>
<body class="bg-gray-900 text-white">
    <?php include '../includes/navbar.php'; ?>

    <div class="container mx-auto px-4 py-8">
        <h1 class="text-3xl font-bold text-purple-500 mb-8">Bandeja de Entrada</h1>
        
        <div class="bg-gray-800 rounded-lg shadow-lg">
            <ul class="divide-y divide-gray-700">
                <?php if (empty($notificaciones)): ?>
                    <li class="p-6 text-center text-gray-400">No tienes notificaciones.</li>
                <?php else: ?>
                    <?php foreach ($notificaciones as $notif): ?>
                        <li class="p-4 md:p-6 transition-colors duration-200 <?= empty($notif['leido']) ? 'bg-purple-900/20' : 'hover:bg-gray-700/50' ?>">
                            <a href="<?= htmlspecialchars($notif['url'] ?? '#') ?>" class="flex items-center space-x-4 group">
                                <div class="flex-1">
                                    <p class="text-gray-300 group-hover:text-white <?= empty($notif['leido']) ? 'font-bold text-white' : '' ?>">
                                        <?= htmlspecialchars($notif['mensaje']) ?>
                                    </p>
                                    <span class="text-xs text-gray-500 group-hover:text-gray-400">
                                        <?= date('d/m/Y H:i', strtotime($notif['fecha_creacion'])) ?>
                                    </span>
                                </div>
                                <div class="text-purple-400 opacity-0 group-hover:opacity-100 transition-opacity">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path></svg>
                                </div>
                            </a>
                        </li>
                    <?php endforeach; ?>
                <?php endif; ?>
            </ul>
        </div>
    </div>
</body>
</html>