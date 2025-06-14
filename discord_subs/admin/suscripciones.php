<?php
require_once '../includes/config.php';
require_once '../includes/auth.php';
require_once '../includes/functions.php';

requireAdmin();

$db = new Database();
$conn = $db->connect();

// Manejar filtros
$filtro_estado = isset($_GET['estado']) ? $_GET['estado'] : 'todas';
$busqueda = isset($_GET['busqueda']) ? trim($_GET['busqueda']) : '';

$query = "SELECT s.*, u.discord as usuario_discord, u.correo as usuario_correo 
          FROM suscripciones s
          JOIN usuarios u ON s.usuario_id = u.id
          WHERE 1=1";

$params = [];

if($filtro_estado !== 'todas') {
    $query .= " AND s.estado = :estado";
    $params[':estado'] = $filtro_estado;
}

if(!empty($busqueda)) {
    $query .= " AND (u.discord LIKE :busqueda OR u.correo LIKE :busqueda)";
    $params[':busqueda'] = "%$busqueda%";
}

$query .= " ORDER BY s.fecha_inicio DESC";

$stmt = $conn->prepare($query);
foreach($params as $key => $value) {
    $stmt->bindValue($key, $value);
}
$stmt->execute();
$suscripciones = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Suscripciones - Admin</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body class="bg-gray-900 text-white">
    <?php include '../includes/navbar.php'; ?>
    
    <div class="container mx-auto px-4 py-8">
        <h1 class="text-3xl font-bold text-purple-500 mb-8">Gestión de Suscripciones</h1>
        
        <!-- Filtros y búsqueda -->
        <form method="GET" class="mb-6 bg-gray-800 p-4 rounded-lg">
            <div class="grid md:grid-cols-3 gap-4">
                <div>
                    <label for="estado" class="block text-sm font-medium text-gray-300 mb-1">Estado</label>
                    <select id="estado" name="estado" class="w-full px-3 py-2 bg-gray-700 border border-gray-600 rounded-md">
                        <option value="todas" <?= $filtro_estado === 'todas' ? 'selected' : '' ?>>Todas las suscripciones</option>
                        <option value="activa" <?= $filtro_estado === 'activa' ? 'selected' : '' ?>>Activas</option>
                        <option value="pendiente" <?= $filtro_estado === 'pendiente' ? 'selected' : '' ?>>Pendientes</option>
                        <option value="expirada" <?= $filtro_estado === 'expirada' ? 'selected' : '' ?>>Expiradas</option>
                    </select>
                </div>
                
                <div>
                    <label for="busqueda" class="block text-sm font-medium text-gray-300 mb-1">Buscar</label>
                    <input type="text" id="busqueda" name="busqueda" value="<?= htmlspecialchars($busqueda) ?>" 
                           placeholder="Buscar por Discord o correo..." class="w-full px-3 py-2 bg-gray-700 border border-gray-600 rounded-md">
                </div>
                
                <div class="flex items-end">
                    <button type="submit" class="w-full bg-purple-600 hover:bg-purple-700 text-white font-bold py-2 px-4 rounded">
                        Aplicar Filtros
                    </button>
                </div>
            </div>
        </form>
        
        <!-- Tabla de suscripciones -->
        <div class="bg-gray-800 rounded-lg shadow-lg overflow-hidden">
            <div class="overflow-x-auto">
                <table class="min-w-full">
                    <thead class="bg-gray-700">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-300 uppercase tracking-wider">ID</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-300 uppercase tracking-wider">Usuario</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-300 uppercase tracking-wider">Tipo</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-300 uppercase tracking-wider">Monto</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-300 uppercase tracking-wider">Inicio</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-300 uppercase tracking-wider">Término</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-300 uppercase tracking-wider">Estado</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-300 uppercase tracking-wider">Acciones</th>
                        </tr>
                    </thead>
                    <tbody class="bg-gray-800 divide-y divide-gray-700">
                        <?php foreach($suscripciones as $suscripcion): ?>
                        <tr>
                            <td class="px-6 py-4 whitespace-nowrap"><?= $suscripcion['id'] ?></td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div><?= htmlspecialchars($suscripcion['usuario_discord']) ?></div>
                                <div class="text-sm text-gray-400"><?= htmlspecialchars($suscripcion['usuario_correo']) ?></div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap"><?= $suscripcion['tipo'] ?></td>
                            <td class="px-6 py-4 whitespace-nowrap">$<?= number_format($suscripcion['monto'], 0, ',', '.') ?></td>
                            <td class="px-6 py-4 whitespace-nowrap"><?= date('d/m/Y', strtotime($suscripcion['fecha_inicio'])) ?></td>
                            <td class="px-6 py-4 whitespace-nowrap"><?= date('d/m/Y', strtotime($suscripcion['fecha_termino'])) ?></td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <?php 
                                $estado_clases = [
                                    'activa' => 'bg-green-600 text-white',
                                    'pendiente' => 'bg-yellow-600 text-white',
                                    'expirada' => 'bg-red-600 text-white'
                                ];
                                ?>
                                <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full <?= $estado_clases[$suscripcion['estado']] ?>">
                                    <?= ucfirst($suscripcion['estado']) ?>
                                </span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <?php if($suscripcion['estado'] === 'pendiente'): ?>
                                    <button onclick="aprobarSuscripcion(<?= $suscripcion['id'] ?>)" class="text-green-400 hover:text-green-300 mr-3">Aprobar</button>
                                <?php endif; ?>
                                <button onclick="mostrarModalEditar(<?= htmlspecialchars(json_encode($suscripcion), ENT_QUOTES, 'UTF-8') ?>)" 
                                        class="text-blue-400 hover:text-blue-300 mr-3">Editar</button>
                                <button onclick="confirmarEliminar(<?= $suscripcion['id'] ?>)" 
                                        class="text-red-400 hover:text-red-300">Eliminar</button>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    
    <!-- Modal Editar Suscripción -->
    <div id="modal-editar-suscripcion" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center hidden">
        <div class="bg-gray-800 rounded-lg p-6 w-full max-w-md">
            <h2 class="text-2xl font-bold text-purple-500 mb-4">Editar Suscripción</h2>
            
            <form id="form-editar-suscripcion" action="../procesar/admin_suscripciones.php" method="POST">
                <input type="hidden" name="accion" value="editar">
                <input type="hidden" id="editar-suscripcion-id" name="id">
                
                <div class="mb-4">
                    <label for="editar-tipo" class="block text-sm font-medium text-gray-300 mb-1">Tipo</label>
                    <select id="editar-tipo" name="tipo" class="w-full px-3 py-2 bg-gray-700 border border-gray-600 rounded-md">
                        <option value="1 mes">1 Mes</option>
                        <option value="6 meses">6 Meses</option>
                        <option value="1 año">1 Año</option>
                    </select>
                </div>
                
                <div class="mb-4">
                    <label for="editar-monto" class="block text-sm font-medium text-gray-300 mb-1">Monto (CLP)</label>
                    <input type="number" id="editar-monto" name="monto" required class="w-full px-3 py-2 bg-gray-700 border border-gray-600 rounded-md">
                </div>
                
                <div class="mb-4">
                    <label for="editar-fecha-inicio" class="block text-sm font-medium text-gray-300 mb-1">Fecha Inicio</label>
                    <input type="date" id="editar-fecha-inicio" name="fecha_inicio" required class="w-full px-3 py-2 bg-gray-700 border border-gray-600 rounded-md">
                </div>
                
                <div class="mb-4">
                    <label for="editar-fecha-termino" class="block text-sm font-medium text-gray-300 mb-1">Fecha Término</label>
                    <input type="date" id="editar-fecha-termino" name="fecha_termino" required class="w-full px-3 py-2 bg-gray-700 border border-gray-600 rounded-md">
                </div>
                
                <div class="mb-4">
                    <label for="editar-estado" class="block text-sm font-medium text-gray-300 mb-1">Estado</label>
                    <select id="editar-estado" name="estado" class="w-full px-3 py-2 bg-gray-700 border border-gray-600 rounded-md">
                        <option value="activa">Activa</option>
                        <option value="pendiente">Pendiente</option>
                        <option value="expirada">Expirada</option>
                    </select>
                </div>
                
                <div class="flex justify-end space-x-3">
                    <button type="button" onclick="cerrarModal('modal-editar-suscripcion')" class="px-4 py-2 bg-gray-600 hover:bg-gray-700 rounded-md">
                        Cancelar
                    </button>
                    <button type="submit" class="px-4 py-2 bg-purple-600 hover:bg-purple-700 rounded-md">
                        Guardar
                    </button>
                </div>
            </form>
        </div>
    </div>
    
    <!-- Modal Confirmar Eliminar -->
    <div id="modal-confirmar-eliminar" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center hidden">
        <div class="bg-gray-800 rounded-lg p-6 w-full max-w-md">
            <h2 class="text-2xl font-bold text-red-500 mb-4">Confirmar Eliminación</h2>
            <p class="mb-6">¿Estás seguro que deseas eliminar esta suscripción? Esta acción no se puede deshacer.</p>
            