<?php
require_once '../includes/config.php';
require_once '../includes/auth.php';
require_once '../includes/database.php'; // <-- LÍNEA AÑADIDA
require_once '../includes/functions.php';

requireAdmin();

// Obtener estadísticas
$db = new Database();
$conn = $db->connect();

// Total usuarios
$total_usuarios = $conn->query("SELECT COUNT(*) FROM usuarios")->fetchColumn();

// Suscripciones activas
$suscripciones_activas = $conn->query("SELECT COUNT(*) FROM suscripciones WHERE estado = 'activa' AND fecha_termino > NOW()")->fetchColumn();

// Ingresos del mes actual
$ingresos_mes = $conn->query("SELECT SUM(monto) FROM suscripciones WHERE estado = 'activa' AND MONTH(fecha_inicio) = MONTH(CURRENT_DATE()) AND YEAR(fecha_inicio) = YEAR(CURRENT_DATE())")->fetchColumn() ?? 0;

// Suscripciones pendientes de aprobación
$pendientes_aprobacion = $conn->query("SELECT COUNT(*) FROM suscripciones WHERE estado = 'pendiente'")->fetchColumn();

// Últimas 5 actividades
$actividades = $conn->query("SELECT a.*, u.discord FROM actividades a LEFT JOIN usuarios u ON a.usuario_id = u.id ORDER BY a.fecha DESC LIMIT 5")->fetchAll(PDO::FETCH_ASSOC);

// Suscripciones por expirar en los próximos 7 días
$query_expirar = "SELECT s.*, u.discord FROM suscripciones s JOIN usuarios u ON s.usuario_id = u.id WHERE s.estado = 'activa' AND s.fecha_termino BETWEEN NOW() AND DATE_ADD(NOW(), INTERVAL 7 DAY) ORDER BY s.fecha_termino ASC";
$suscripciones_expirar = $conn->query($query_expirar)->fetchAll(PDO::FETCH_ASSOC);

?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - Admin</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body class="bg-gray-900 text-white">
    <?php include '../includes/navbar.php'; ?>
    
    <div class="container mx-auto px-4 py-8">
        <h1 class="text-3xl font-bold text-purple-500 mb-8">Panel de Administración</h1>
        
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
            <div class="bg-gray-800 rounded-lg p-6 shadow-lg"><h3 class="font-semibold text-gray-300">Usuarios Registrados</h3><p class="text-4xl font-bold text-purple-500"><?= $total_usuarios ?></p></div>
            <div class="bg-gray-800 rounded-lg p-6 shadow-lg"><h3 class="font-semibold text-gray-300">Suscripciones Activas</h3><p class="text-4xl font-bold text-indigo-500"><?= $suscripciones_activas ?></p></div>
            <div class="bg-gray-800 rounded-lg p-6 shadow-lg"><h3 class="font-semibold text-gray-300">Ingresos del Mes</h3><p class="text-4xl font-bold text-green-500">$<?= number_format($ingresos_mes, 0, ',', '.') ?></p></div>
            <div class="bg-gray-800 rounded-lg p-6 shadow-lg"><h3 class="font-semibold text-gray-300">Pendientes de Revisión</h3><p class="text-4xl font-bold text-yellow-500"><?= $pendientes_aprobacion ?></p><a href="suscripciones.php?estado=pendiente" class="text-yellow-400 hover:text-yellow-300 text-sm mt-1 inline-block">Revisar ahora</a></div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
            <div class="bg-gray-800 rounded-lg p-6 shadow-lg">
                <h2 class="text-2xl font-bold text-purple-500 mb-4">Actividad Reciente</h2>
                <div class="overflow-x-auto">
                    <table class="min-w-full">
                        <tbody>
                        <?php foreach($actividades as $actividad): ?>
                            <tr class="border-b border-gray-700"><td class="py-2"><span class="font-semibold text-purple-400"><?= htmlspecialchars($actividad['discord'] ?? 'Sistema') ?></span> <?= htmlspecialchars($actividad['accion']) ?><br><small class="text-gray-400"><?= date('d/m/Y H:i', strtotime($actividad['fecha'])) ?></small></td></tr>
                        <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
            
            <div class="bg-gray-800 rounded-lg p-6 shadow-lg">
                <h2 class="text-2xl font-bold text-purple-500 mb-4">Suscripciones por Expirar (7 días)</h2>
                 <?php if(count($suscripciones_expirar) > 0): ?>
                    <div class="overflow-x-auto">
                        <table class="min-w-full">
                           <tbody>
                            <?php foreach($suscripciones_expirar as $suscripcion): ?>
                                <tr class="border-b border-gray-700"><td class="py-2"><?= htmlspecialchars($suscripcion['discord']) ?></td><td class="text-right text-red-400">Expira: <?= date('d/m/Y', strtotime($suscripcion['fecha_termino'])) ?></td></tr>
                            <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php else: ?>
                    <p class="text-gray-400">No hay suscripciones por expirar en los próximos 7 días.</p>
                <?php endif; ?>
            </div>
        </div>
    </div>
</body>
</html>