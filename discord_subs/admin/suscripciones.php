<?php
require_once '../includes/config.php';
require_once '../includes/auth.php';
require_once '../includes/database.php'; // <-- LÍNEA AÑADIDA
require_once '../includes/functions.php';

requireAdmin();
$db = new Database();
$conn = $db->connect();

$filtro_estado = $_GET['estado'] ?? 'todas';
$busqueda = trim($_GET['busqueda'] ?? '');

$params = [];
$query_base = "SELECT s.*, u.discord FROM suscripciones s JOIN usuarios u ON s.usuario_id = u.id WHERE 1=1";
if ($filtro_estado !== 'todas') {
    $query_base .= " AND s.estado = ?";
    $params[] = $filtro_estado;
}
if (!empty($busqueda)) {
    $query_base .= " AND u.discord LIKE ?";
    $params[] = "%$busqueda%";
}
$query_base .= " ORDER BY s.fecha_creacion DESC";
$stmt = $conn->prepare($query_base);
$stmt->execute($params);
$suscripciones = $stmt->fetchAll(PDO::FETCH_ASSOC);

function getEstadoBadge($estado) {
    $classes = ['activa' => 'bg-green-600', 'pendiente' => 'bg-yellow-600', 'expirada' => 'bg-gray-600', 'rechazada' => 'bg-red-600'];
    return $classes[$estado] ?? 'bg-gray-500';
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1.0"><title>Suscripciones - Admin</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet"><link rel="stylesheet" href="../assets/css/style.css">
</head>
<body class="bg-gray-900 text-white">
    <?php include '../includes/navbar.php'; ?>
    <div class="container mx-auto px-4 py-8">
        <h1 class="text-3xl font-bold text-purple-500 mb-6">Gestión de Suscripciones</h1>
        <form method="GET" class="mb-6 bg-gray-800 p-4 rounded-lg flex flex-col md:flex-row md:space-x-4 space-y-4 md:space-y-0">
            <select name="estado" class="w-full md:w-auto mt-1 block px-4 py-2 bg-gray-700 border border-gray-600 rounded-md focus:ring-purple-500 focus:border-purple-500 text-white flex-1" onchange="this.form.submit()">
                <option value="todas" <?= $filtro_estado == 'todas' ? 'selected' : '' ?>>Todas</option>
                <option value="pendiente" <?= $filtro_estado == 'pendiente' ? 'selected' : '' ?>>Pendientes</option>
                <option value="activa" <?= $filtro_estado == 'activa' ? 'selected' : '' ?>>Activas</option>
                <option value="expirada" <?= $filtro_estado == 'expirada' ? 'selected' : '' ?>>Expiradas</option>
                <option value="rechazada" <?= $filtro_estado == 'rechazada' ? 'selected' : '' ?>>Rechazadas</option>
            </select>
            <input type="text" name="busqueda" value="<?= htmlspecialchars($busqueda) ?>" placeholder="Buscar por Discord..." class="w-full md:w-auto mt-1 block px-4 py-2 bg-gray-700 border border-gray-600 rounded-md focus:ring-purple-500 focus:border-purple-500 text-white flex-2">
            <button type="submit" class="btn-primary w-full md:w-auto">Filtrar</button>
        </form>
        
        <div class="bg-gray-800 rounded-lg shadow-lg overflow-x-auto">
            <table class="min-w-full">
                <thead class="bg-gray-700">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-300 uppercase tracking-wider">Usuario</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-300 uppercase tracking-wider">Plan</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-300 uppercase tracking-wider">Fechas</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-300 uppercase tracking-wider">Estado</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-300 uppercase tracking-wider">Comprobante</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-300 uppercase tracking-wider">Acciones</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-700">
                <?php foreach($suscripciones as $s): ?>
                    <tr>
                        <td class="px-6 py-4 whitespace-nowrap"><?= htmlspecialchars($s['discord']) ?></td>
                        <td class="px-6 py-4 whitespace-nowrap"><?= htmlspecialchars($s['tipo']) ?><br><small class="text-gray-400">$<?= number_format($s['monto'], 0) ?></small></td>
                        <td class="px-6 py-4 whitespace-nowrap"><?= date('d/m/y', strtotime($s['fecha_inicio'])) ?> - <?= date('d/m/y', strtotime($s['fecha_termino'])) ?></td>
                        <td class="px-6 py-4 whitespace-nowrap"><span class="px-2 py-1 text-xs font-semibold rounded-full text-white <?= getEstadoBadge($s['estado']) ?>"><?= ucfirst($s['estado']) ?></span></td>
                        <td class="px-6 py-4 whitespace-nowrap"><?= $s['comprobante_pago'] ? "<a href='../uploads/comprobantes/{$s['comprobante_pago']}' target='_blank' class='text-blue-400 hover:underline'>Ver</a>" : 'No subido' ?></td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <?php if($s['estado'] === 'pendiente' && $s['comprobante_pago']): ?>
                                <form action="../procesar/admin_suscripciones.php" method="POST" class="inline-block"><input type="hidden" name="accion" value="aprobar"><input type="hidden" name="id" value="<?= $s['id'] ?>"><button type="submit" class="text-green-400 hover:text-green-300 mr-2">Aprobar</button></form>
                                <form action="../procesar/admin_suscripciones.php" method="POST" class="inline-block"><input type="hidden" name="accion" value="rechazar"><input type="hidden" name="id" value="<?= $s['id'] ?>"><button type="submit" class="text-yellow-400 hover:text-yellow-300 mr-2">Rechazar</button></form>
                            <?php endif; ?>
                             <button onclick='mostrarModalEditarSusc(<?= json_encode($s) ?>)' class="text-blue-400 hover:text-blue-300 mr-2">Editar</button>
                             <button onclick='confirmarEliminarSusc(<?= $s['id'] ?>)' class="text-red-400 hover:text-red-300">Eliminar</button>
                        </td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
    
    <div id="modal-editar-suscripcion" class="modal">
        <div class="modal-content">
            <h2 class="text-2xl font-bold text-purple-500 mb-4">Editar Suscripción</h2>
            <form id="form-editar-susc" action="../procesar/admin_suscripciones.php" method="POST" class="space-y-4">
                <input type="hidden" name="accion" value="editar"><input type="hidden" id="editar-susc-id" name="id">
                <div>
                    <label class="block text-sm font-medium text-gray-300 mb-1">Tipo</label>
                    <select id="editar-susc-tipo" name="tipo" class="mt-1 block w-full px-4 py-2 bg-gray-700 border border-gray-600 rounded-md focus:ring-purple-500 focus:border-purple-500 text-white">
                        <option value="1 mes">1 Mes</option><option value="6 meses">6 Meses</option><option value="1 año">1 Año</option>
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-300 mb-1">Monto (CLP)</label>
                    <input type="number" id="editar-susc-monto" name="monto" required class="mt-1 block w-full px-4 py-2 bg-gray-700 border border-gray-600 rounded-md focus:ring-purple-500 focus:border-purple-500 text-white">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-300 mb-1">Fecha Inicio</label>
                    <input type="date" id="editar-susc-inicio" name="fecha_inicio" required class="mt-1 block w-full px-4 py-2 bg-gray-700 border border-gray-600 rounded-md focus:ring-purple-500 focus:border-purple-500 text-white">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-300 mb-1">Fecha Término</label>
                    <input type="date" id="editar-susc-termino" name="fecha_termino" required class="mt-1 block w-full px-4 py-2 bg-gray-700 border border-gray-600 rounded-md focus:ring-purple-500 focus:border-purple-500 text-white">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-300 mb-1">Estado</label>
                    <select id="editar-susc-estado" name="estado" class="mt-1 block w-full px-4 py-2 bg-gray-700 border border-gray-600 rounded-md focus:ring-purple-500 focus:border-purple-500 text-white">
                        <option value="pendiente">Pendiente</option><option value="activa">Activa</option><option value="expirada">Expirada</option><option value="rechazada">Rechazada</option>
                    </select>
                </div>
                <div class="flex justify-end space-x-2">
                    <button type="button" onclick="cerrarModal('modal-editar-suscripcion')" class="btn-secondary">Cancelar</button>
                    <button type="submit" class="btn-primary">Guardar</button>
                </div>
            </form>
        </div>
    </div>
    
    <div id="modal-confirmar-eliminar-susc" class="modal">
        <div class="modal-content">
            <h2 class="text-2xl font-bold text-red-500 mb-4">Confirmar Eliminación</h2>
            <p>¿Estás seguro de que deseas eliminar esta suscripción? Esta acción no se puede deshacer.</p>
            <form action="../procesar/admin_suscripciones.php" method="POST" class="mt-6 flex justify-end space-x-2">
                <input type="hidden" name="accion" value="eliminar"><input type="hidden" id="eliminar-susc-id" name="id">
                <button type="button" onclick="cerrarModal('modal-confirmar-eliminar-susc')" class="btn-secondary">Cancelar</button>
                <button type="submit" class="bg-red-600 hover:bg-red-700 text-white font-bold py-2 px-4 rounded">Eliminar</button>
            </form>
        </div>
    </div>
    <script src="../assets/js/script.js"></script>
</body>
</html>