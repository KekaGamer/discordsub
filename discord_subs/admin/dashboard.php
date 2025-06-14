<?php
require_once '../includes/config.php';
require_once '../includes/auth.php';
require_once '../includes/functions.php';

requireAdmin();

// Obtener estadísticas
$db = new Database();
$conn = $db->connect();

// Total usuarios
$query = "SELECT COUNT(*) as total FROM usuarios";
$stmt = $conn->query($query);
$total_usuarios = $stmt->fetch(PDO::FETCH_ASSOC)['total'];

// Total suscripciones activas
$query = "SELECT COUNT(*) as total FROM suscripciones WHERE estado = 'activa' AND fecha_termino > NOW()";
$stmt = $conn->query($query);
$suscripciones_activas = $stmt->fetch(PDO::FETCH_ASSOC)['total'];

// Total ingresos este mes
$query = "SELECT SUM(monto) as total FROM suscripciones 
          WHERE MONTH(fecha_inicio) = MONTH(CURRENT_DATE()) 
          AND YEAR(fecha_inicio) = YEAR(CURRENT_DATE())";
$stmt = $conn->query($query);
$ingresos_mes = $stmt->fetch(PDO::FETCH_ASSOC)['total'] ?? 0;

// Últimas actividades
$query = "SELECT a.*, u.discord as usuario_discord FROM actividades a
          LEFT JOIN usuarios u ON a.usuario_id = u.id
          ORDER BY a.fecha DESC LIMIT 10";
$stmt = $conn->query($query);
$actividades = $stmt->fetchAll(PDO::FETCH_ASSOC);
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
        
        <div class="grid md:grid-cols-3 gap-6 mb-8">
            <!-- Tarjeta Usuarios -->
            <div class="bg-gray-800 rounded-lg p-6 shadow-lg">
                <h3 class="text-xl font-semibold text-gray-300 mb-2">Usuarios Registrados</h3>
                <p class="text-4xl font-bold text-purple-500"><?= $total_usuarios ?></p>
                <a href="usuarios.php" class="text-purple-400 hover:text-purple-300 text-sm mt-2 inline-block">Ver todos</a>
            </div>
            
            <!-- Tarjeta Suscripciones -->
            <div class="bg-gray-800 rounded-lg p-6 shadow-lg">
                <h3 class="text-xl font-semibold text-gray-300 mb-2">Suscripciones Activas</h3>
                <p class="text-4xl font-bold text-indigo-500"><?= $suscripciones_activas ?></p>
                <a href="suscripciones.php" class="text-indigo-400 hover:text-indigo-300 text-sm mt-2 inline-block">Ver todas</a>
            </div>
            
            <!-- Tarjeta Ingresos -->
            <div class="bg-gray-800 rounded-lg p-6 shadow-lg">
                <h3 class="text-xl font-semibold text-gray-300 mb-2">Ingresos del Mes</h3>
                <p class="text-4xl font-bold text-green-500">$<?= number_format($ingresos_mes, 0, ',', '.') ?> CLP</p>
            </div>
        </div>
        
        <!-- Últimas Actividades -->
        <div class="bg-gray-800 rounded-lg p-6 shadow-lg mb-8">
            <h2 class="text-2xl font-bold text-purple-500 mb-4">Últimas Actividades</h2>
            
            <div class="overflow-x-auto">
                <table class="min-w-full bg-gray-700 rounded-lg overflow-hidden">
                    <thead class="bg-gray-600">
                        <tr>
                            <th class="px-4 py-3 text-left">Usuario</th>
                            <th class="px-4 py-3 text-left">Acción</th>
                            <th class="px-4 py-3 text-left">Detalles</th>
                            <th class="px-4 py-3 text-left">Fecha</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-600">
                        <?php foreach($actividades as $actividad): ?>
                        <tr>
                            <td class="px-4 py-3"><?= $actividad['usuario_discord'] ?? 'Sistema' ?></td>
                            <td class="px-4 py-3"><?= $actividad['accion'] ?></td>
                            <td class="px-4 py-3"><?= $actividad['detalles'] ?? '-' ?></td>
                            <td class="px-4 py-3"><?= date('d/m/Y H:i', strtotime($actividad['fecha'])) ?></td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
        
        <!-- Suscripciones por expirar -->
        <div class="bg-gray-800 rounded-lg p-6 shadow-lg">
            <h2 class="text-2xl font-bold text-purple-500 mb-4">Suscripciones por Expirar</h2>
            
            <?php
            $query = "SELECT s.*, u.discord, u.correo 
                      FROM suscripciones s
                      JOIN usuarios u ON s.usuario_id = u.id
                      WHERE s.estado = 'activa'
                      AND s.fecha_termino BETWEEN NOW() AND DATE_ADD(NOW(), INTERVAL 7 DAY)
                      ORDER BY s.fecha_termino ASC";
            $stmt = $conn->query($query);
            $suscripciones_expirar = $stmt->fetchAll(PDO::FETCH_ASSOC);
            ?>
            
            <?php if(count($suscripciones_expirar) > 0): ?>
                <div class="overflow-x-auto">
                    <table class="min-w-full bg-gray-700 rounded-lg overflow-hidden">
                        <thead class="bg-gray-600">
                            <tr>
                                <th class="px-4 py-3 text-left">Usuario</th>
                                <th class="px-4 py-3 text-left">Tipo</th>
                                <th class="px-4 py-3 text-left">Expiración</th>
                                <th class="px-4 py-3 text-left">Correo</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-600">
                            <?php foreach($suscripciones_expirar as $suscripcion): ?>
                            <tr>
                                <td class="px-4 py-3"><?= $suscripcion['discord'] ?></td>
                                <td class="px-4 py-3"><?= $suscripcion['tipo'] ?></td>
                                <td class="px-4 py-3"><?= date('d/m/Y', strtotime($suscripcion['fecha_termino'])) ?></td>
                                <td class="px-4 py-3"><?= $suscripcion['correo'] ?></td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php else: ?>
                <p class="text-gray-400">No hay suscripciones por expirar en los próximos 7 días.</p>
            <?php endif; ?>
        </div>
    </div>
    
    <script src="../assets/js/script.js"></script>
</body>
</html>