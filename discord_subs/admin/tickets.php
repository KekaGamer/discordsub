<?php
// admin/tickets.php (VERSIÓN CORREGIDA CON LEFT JOIN)
require_once '../includes/config.php';
require_once '../includes/auth.php';
require_once '../includes/database.php';
require_once '../includes/functions.php';
requireAdmin();

$db = new Database();
$conn = $db->connect();

// Obtener todos los usuarios para el formulario de creación
$usuarios_stmt = $conn->query("SELECT id, discord FROM usuarios ORDER BY discord ASC");
$usuarios = $usuarios_stmt->fetchAll(PDO::FETCH_ASSOC);

// Lógica de filtros
$filtro_estado = $_GET['estado'] ?? 'Abierto';
$where_clause = '';
$params = [];
if ($filtro_estado !== 'todos') {
    $where_clause .= 'WHERE t.estado = :estado';
    $params[':estado'] = $filtro_estado;
}

// ### CORRECCIÓN IMPORTANTE: Se cambió JOIN por LEFT JOIN ###
// Esto asegura que se muestren los tickets aunque el usuario haya sido eliminado.
$query = "SELECT t.*, u.discord FROM tickets t LEFT JOIN usuarios u ON t.usuario_id = u.id $where_clause ORDER BY t.ultima_actualizacion DESC";
$stmt = $conn->prepare($query);
$stmt->execute($params);
$tickets = $stmt->fetchAll(PDO::FETCH_ASSOC);

function getPrioridadColor($prioridad) {
    switch ($prioridad) {
        case 'Alta': return 'bg-red-500';
        case 'Normal': return 'bg-yellow-500';
        case 'Baja': return 'bg-green-500';
        default: return 'bg-gray-500';
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Administrar Tickets - KekaGamer</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body class="bg-gray-900 text-white">
    <?php include '../includes/navbar.php'; ?>
    <div class="container mx-auto px-4 py-8">
        <div class="flex justify-between items-center mb-6">
            <h1 class="text-3xl font-bold text-purple-400">Administración de Tickets</h1>
            <button onclick="document.getElementById('modal-crear-ticket').style.display='flex'" class="bg-purple-600 hover:bg-purple-700 text-white font-bold py-2 px-4 rounded">Crear Ticket</button>
        </div>
        
        <form method="GET" class="mb-4 bg-gray-800 p-4 rounded-lg">
            <label for="estado" class="font-bold">Filtrar por estado:</label>
            <select name="estado" onchange="this.form.submit()" class="bg-gray-700 p-2 rounded ml-2 text-white">
                <option value="Abierto" <?= $filtro_estado == 'Abierto' ? 'selected' : '' ?>>Abiertos</option>
                <option value="En Proceso" <?= $filtro_estado == 'En Proceso' ? 'selected' : '' ?>>En Proceso</option>
                <option value="Cerrado" <?= $filtro_estado == 'Cerrado' ? 'selected' : '' ?>>Cerrados</option>
                <option value="todos" <?= $filtro_estado == 'todos' ? 'selected' : '' ?>>Todos</option>
            </select>
        </form>

        <div class="bg-gray-800 rounded-lg shadow-lg overflow-x-auto">
            <table class="min-w-full">
                <thead class="bg-gray-700">
                    <tr>
                        <th class="p-3 text-left text-xs font-medium uppercase text-gray-300">ID</th>
                        <th class="p-3 text-left text-xs font-medium uppercase text-gray-300">Asunto</th>
                        <th class="p-3 text-left text-xs font-medium uppercase text-gray-300">Usuario</th>
                        <th class="p-3 text-left text-xs font-medium uppercase text-gray-300">Prioridad</th>
                        <th class="p-3 text-left text-xs font-medium uppercase text-gray-300">Grupo</th>
                        <th class="p-3 text-left text-xs font-medium uppercase text-gray-300">Actualizado</th>
                        <th class="p-3 text-left text-xs font-medium uppercase text-gray-300">Acción</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-700">
                    <?php if(!empty($tickets)): ?>
                        <?php foreach($tickets as $ticket): ?>
                            <tr>
                                <td class="p-3">#<?= $ticket['id'] ?></td>
                                <td class="p-3 font-semibold"><?= htmlspecialchars($ticket['asunto']) ?></td>
                                <td class="p-3 text-gray-400"><?= htmlspecialchars($ticket['discord'] ?? '[Usuario Eliminado]') ?></td>
                                <td class="p-3"><span class="px-2 py-1 text-xs font-bold rounded-full text-white <?= getPrioridadColor($ticket['prioridad']) ?>"><?= htmlspecialchars($ticket['prioridad']) ?></span></td>
                                <td class="p-3 text-gray-400"><?= htmlspecialchars($ticket['grupo_asignado']) ?></td>
                                <td class="p-3 text-gray-400"><?= date('d/m/Y H:i', strtotime($ticket['ultima_actualizacion'])) ?></td>
                                <td class="p-3"><a href="ver_ticket.php?id=<?= $ticket['id'] ?>" class="text-purple-400 hover:underline font-bold">Ver/Gestionar</a></td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr><td colspan="7" class="text-center p-4 text-gray-400">No hay tickets con el estado seleccionado.</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

    <div id="modal-crear-ticket" class="modal" style="display:none; align-items:center; justify-content:center; position:fixed; top:0; left:0; width:100%; height:100%; background-color:rgba(0,0,0,0.7);">
        </div>
</body>
</html>