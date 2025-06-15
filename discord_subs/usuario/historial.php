<?php
require_once '../includes/config.php';
require_once '../includes/auth.php';
require_once '../includes/database.php'; // <-- LÍNEA AÑADIDA
require_once '../includes/functions.php';

requireLogin();
$db = new Database();
$conn = $db->connect();

$query = "SELECT * FROM suscripciones WHERE usuario_id = ? ORDER BY fecha_creacion DESC";
$stmt = $conn->prepare($query);
$stmt->execute([$_SESSION['usuario_id']]);
$historial = $stmt->fetchAll(PDO::FETCH_ASSOC);

function getEstadoBadge($estado) {
    switch ($estado) {
        case 'activa': return 'bg-green-600 text-white';
        case 'pendiente': return 'bg-yellow-600 text-white';
        case 'expirada': return 'bg-gray-600 text-gray-200';
        case 'rechazada': return 'bg-red-600 text-white';
        default: return 'bg-gray-500';
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Historial de Suscripciones - Discord Subs</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body class="bg-gray-900 text-white">
    <?php include '../includes/navbar.php'; ?>
    
    <div class="container mx-auto px-4 py-8">
        <h1 class="text-3xl font-bold text-purple-500 mb-8">Historial de Suscripciones</h1>
        
        <div class="bg-gray-800 rounded-lg shadow-lg overflow-hidden">
            <div class="overflow-x-auto">
                <table class="min-w-full">
                    <thead class="bg-gray-700">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-300 uppercase tracking-wider">Plan</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-300 uppercase tracking-wider">Monto</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-300 uppercase tracking-wider">Fecha de Inicio</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-300 uppercase tracking-wider">Fecha de Término</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-300 uppercase tracking-wider">Estado</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-300 uppercase tracking-wider">Comprobante</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-700">
                        <?php if(count($historial) > 0): ?>
                            <?php foreach($historial as $item): ?>
                            <tr>
                                <td class="px-6 py-4 whitespace-nowrap"><?= htmlspecialchars($item['tipo']) ?></td>
                                <td class="px-6 py-4 whitespace-nowrap">$<?= number_format($item['monto'], 0, ',', '.') ?> CLP</td>
                                <td class="px-6 py-4 whitespace-nowrap"><?= date('d/m/Y', strtotime($item['fecha_inicio'])) ?></td>
                                <td class="px-6 py-4 whitespace-nowrap"><?= date('d/m/Y', strtotime($item['fecha_termino'])) ?></td>
                                <td class="px-6 py-4 whitespace-nowrap"><span class="px-2 py-1 text-xs font-semibold rounded-full <?= getEstadoBadge($item['estado']) ?>"><?= ucfirst($item['estado']) ?></span></td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <?php if($item['comprobante_pago']): ?>
                                        <a href="../uploads/comprobantes/<?= htmlspecialchars($item['comprobante_pago']) ?>" target="_blank" class="text-blue-400 hover:underline">Ver</a>
                                    <?php elseif($item['estado'] === 'pendiente'): ?>
                                         <a href="subir_comprobante.php?id=<?= $item['id'] ?>" class="text-yellow-400 hover:underline">Subir</a>
                                    <?php else: echo 'N/A'; endif; ?>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr><td colspan="6" class="text-center py-8 text-gray-400">No tienes suscripciones en tu historial.</td></tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</body>
</html>