<?php
require_once '../includes/config.php';
require_once '../includes/auth.php';
require_once '../includes/database.php';
require_once '../includes/functions.php';

requireAdmin();

$db = new Database();
$conn = $db->connect();

// Estadísticas existentes
$total_usuarios = $conn->query("SELECT COUNT(*) FROM usuarios")->fetchColumn();
$suscripciones_activas = $conn->query("SELECT COUNT(*) FROM suscripciones WHERE estado = 'activa' AND fecha_termino > NOW()")->fetchColumn();
$ingresos_mes = $conn->query("SELECT SUM(monto) FROM suscripciones WHERE estado = 'activa' AND MONTH(fecha_inicio) = MONTH(CURRENT_DATE()) AND YEAR(fecha_inicio) = YEAR(CURRENT_DATE())")->fetchColumn() ?? 0;
$pendientes_aprobacion = $conn->query("SELECT COUNT(*) FROM suscripciones WHERE estado = 'pendiente'")->fetchColumn();

// --- NUEVAS ESTADÍSTICAS DE TICKETS ---
$tickets_abiertos = $conn->query("SELECT COUNT(*) FROM tickets WHERE estado = 'Abierto'")->fetchColumn();
$tickets_en_proceso = $conn->query("SELECT COUNT(*) FROM tickets WHERE estado = 'En Proceso'")->fetchColumn();
$tickets_cerrados = $conn->query("SELECT COUNT(*) FROM tickets WHERE estado = 'Cerrado'")->fetchColumn();

$actividades = $conn->query("SELECT a.*, u.discord FROM actividades a LEFT JOIN usuarios u ON a.usuario_id = u.id ORDER BY a.fecha DESC LIMIT 5")->fetchAll(PDO::FETCH_ASSOC);
$suscripciones_expirar = $conn->query("SELECT s.*, u.discord FROM suscripciones s JOIN usuarios u ON s.usuario_id = u.id WHERE s.estado = 'activa' AND s.fecha_termino BETWEEN NOW() AND DATE_ADD(NOW(), INTERVAL 7 DAY) ORDER BY s.fecha_termino ASC")->fetchAll(PDO::FETCH_ASSOC);

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
        
        <h2 class="text-xl font-bold text-gray-300 mb-4">Métricas Generales</h2>
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
            <div class="bg-gray-800 rounded-lg p-6 shadow-lg"><h3 class="font-semibold text-gray-300">Usuarios Registrados</h3><p class="text-4xl font-bold text-purple-500"><?= $total_usuarios ?></p></div>
            <div class="bg-gray-800 rounded-lg p-6 shadow-lg"><h3 class="font-semibold text-gray-300">Suscripciones Activas</h3><p class="text-4xl font-bold text-indigo-500"><?= $suscripciones_activas ?></p></div>
            <div class="bg-gray-800 rounded-lg p-6 shadow-lg"><h3 class="font-semibold text-gray-300">Ingresos del Mes</h3><p class="text-4xl font-bold text-green-500">$<?= number_format($ingresos_mes, 0, ',', '.') ?></p></div>
            <div class="bg-gray-800 rounded-lg p-6 shadow-lg"><h3 class="font-semibold text-gray-300">Pagos Pendientes</h3><p class="text-4xl font-bold text-yellow-500"><?= $pendientes_aprobacion ?></p><a href="suscripciones.php?estado=pendiente" class="text-yellow-400 hover:text-yellow-300 text-sm mt-1 inline-block">Revisar</a></div>
        </div>

        <h2 class="text-xl font-bold text-gray-300 mb-4">Métricas de Soporte</h2>
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
             <div class="bg-gray-800 rounded-lg p-6 shadow-lg"><h3 class="font-semibold text-gray-300">Tickets Abiertos</h3><p class="text-4xl font-bold text-blue-500"><?= $tickets_abiertos ?></p><a href="tickets.php?estado=Abierto" class="text-blue-400 hover:text-blue-300 text-sm mt-1 inline-block">Gestionar</a></div>
            <div class="bg-gray-800 rounded-lg p-6 shadow-lg"><h3 class="font-semibold text-gray-300">Tickets En Proceso</h3><p class="text-4xl font-bold text-yellow-500"><?= $tickets_en_proceso ?></p><a href="tickets.php?estado=En Proceso" class="text-yellow-400 hover:text-yellow-300 text-sm mt-1 inline-block">Gestionar</a></div>
            <div class="bg-gray-800 rounded-lg p-6 shadow-lg"><h3 class="font-semibold text-gray-300">Tickets Cerrados</h3><p class="text-4xl font-bold text-gray-500"><?= $tickets_cerrados ?></p><a href="tickets.php?estado=Cerrado" class="text-gray-400 hover:text-gray-300 text-sm mt-1 inline-block">Ver Historial</a></div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
            </div>
    </div>
</body>
</html>