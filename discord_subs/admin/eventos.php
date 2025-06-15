<?php
require_once '../includes/config.php';
require_once '../includes/auth.php';
require_once '../includes/database.php'; // <-- LÍNEA AÑADIDA
require_once '../includes/functions.php';

requireAdmin();
$db = new Database();
$conn = $db->connect();

$eventos = $conn->query("SELECT * FROM eventos ORDER BY fecha_evento DESC")->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1.0"><title>Eventos - Admin</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet"><link rel="stylesheet" href="../assets/css/style.css">
</head>
<body class="bg-gray-900 text-white">
    <?php include '../includes/navbar.php'; ?>
    <div class="container mx-auto px-4 py-8">
        <div class="flex justify-between items-center mb-6">
            <h1 class="text-3xl font-bold text-purple-500">Gestión de Eventos</h1>
            <button onclick="mostrarModal('modal-agregar-evento')" class="btn-primary py-2 px-4 rounded">Crear Evento</button>
        </div>
        
        <div class="bg-gray-800 rounded-lg shadow-lg overflow-x-auto">
            <table class="min-w-full">
                <thead class="bg-gray-700">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-300 uppercase tracking-wider">Título</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-300 uppercase tracking-wider">Fecha del Evento</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-300 uppercase tracking-wider">Estado Notificación</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-300 uppercase tracking-wider">Acciones</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-700">
                <?php foreach($eventos as $evento): ?>
                    <tr>
                        <td class="px-6 py-4 whitespace-nowrap"><?= htmlspecialchars($evento['titulo']) ?><p class="text-sm text-gray-400 max-w-md truncate"><?= htmlspecialchars($evento['descripcion']) ?></p></td>
                        <td class="px-6 py-4 whitespace-nowrap"><?= date('d/m/Y H:i', strtotime($evento['fecha_evento'])) ?></td>
                        <td class="px-6 py-4 whitespace-nowrap"><?= $evento['notificado'] ? '<span class="text-green-400">Enviada</span>' : '<span class="text-yellow-400">Pendiente</span>' ?></td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <form action="../procesar/admin_eventos.php" method="POST" class="inline-block"><input type="hidden" name="accion" value="notificar"><input type="hidden" name="id" value="<?= $evento['id'] ?>"><button type="submit" class="text-purple-400 hover:text-purple-300 mr-3">Notificar</button></form>
                            <button onclick='mostrarModalEditarEvento(<?= json_encode($evento) ?>)' class="text-blue-400 hover:text-blue-300 mr-3">Editar</button>
                            <button onclick='confirmarEliminarEvento(<?= $evento['id'] ?>)' class="text-red-400 hover:text-red-300">Eliminar</button>
                        </td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
    
    <div id="modal-agregar-evento" class="modal">
        <div class="modal-content">
            <h2 class="text-2xl font-bold text-purple-500 mb-4">Crear Nuevo Evento</h2>
            <form action="../procesar/admin_eventos.php" method="POST" class="space-y-4">
                <input type="hidden" name="accion" value="agregar">
                <div>
                    <label class="block text-sm font-medium text-gray-300 mb-1">Título del Evento</label>
                    <input type="text" name="titulo" required class="mt-1 block w-full px-4 py-2 bg-gray-700 border border-gray-600 rounded-md focus:ring-purple-500 focus:border-purple-500 text-white">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-300 mb-1">Descripción</label>
                    <textarea name="descripcion" required class="mt-1 block w-full px-4 py-2 bg-gray-700 border border-gray-600 rounded-md focus:ring-purple-500 focus:border-purple-500 text-white" rows="4"></textarea>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-300 mb-1">Fecha y Hora del Evento</label>
                    <input type="datetime-local" name="fecha_evento" required class="mt-1 block w-full px-4 py-2 bg-gray-700 border border-gray-600 rounded-md focus:ring-purple-500 focus:border-purple-500 text-white">
                </div>
                <div class="flex justify-end space-x-2">
                    <button type="button" onclick="cerrarModal('modal-agregar-evento')" class="btn-secondary">Cancelar</button>
                    <button type="submit" class="btn-primary">Crear</button>
                </div>
            </form>
        </div>
    </div>

    <div id="modal-editar-evento" class="modal">
         <div class="modal-content">
            <h2 class="text-2xl font-bold text-purple-500 mb-4">Editar Evento</h2>
            <form id="form-editar-evento" action="../procesar/admin_eventos.php" method="POST" class="space-y-4">
                <input type="hidden" name="accion" value="editar"><input type="hidden" id="editar-evento-id" name="id">
                <div>
                    <label class="block text-sm font-medium text-gray-300 mb-1">Título</label>
                    <input type="text" id="editar-evento-titulo" name="titulo" required class="mt-1 block w-full px-4 py-2 bg-gray-700 border border-gray-600 rounded-md focus:ring-purple-500 focus:border-purple-500 text-white">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-300 mb-1">Descripción</label>
                    <textarea id="editar-evento-descripcion" name="descripcion" required class="mt-1 block w-full px-4 py-2 bg-gray-700 border border-gray-600 rounded-md focus:ring-purple-500 focus:border-purple-500 text-white" rows="4"></textarea>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-300 mb-1">Fecha y Hora</label>
                    <input type="datetime-local" id="editar-evento-fecha" name="fecha_evento" required class="mt-1 block w-full px-4 py-2 bg-gray-700 border border-gray-600 rounded-md focus:ring-purple-500 focus:border-purple-500 text-white">
                </div>
                <div class="flex justify-end space-x-2">
                    <button type="button" onclick="cerrarModal('modal-editar-evento')" class="btn-secondary">Cancelar</button>
                    <button type="submit" class="btn-primary">Guardar</button>
                </div>
            </form>
        </div>
    </div>
    
    <div id="modal-confirmar-eliminar-evento" class="modal">
        <div class="modal-content">
            <h2 class="text-2xl font-bold text-red-500 mb-4">Confirmar Eliminación</h2>
            <p>¿Estás seguro de que deseas eliminar este evento? Esta acción no se puede deshacer.</p>
            <form action="../procesar/admin_eventos.php" method="POST" class="mt-6 flex justify-end space-x-2">
                <input type="hidden" name="accion" value="eliminar"><input type="hidden" id="eliminar-evento-id" name="id">
                <button type="button" onclick="cerrarModal('modal-confirmar-eliminar-evento')" class="btn-secondary">Cancelar</button>
                <button type="submit" class="bg-red-600 hover:bg-red-700 text-white font-bold py-2 px-4 rounded">Eliminar</button>
            </form>
        </div>
    </div>
    <script src="../assets/js/script.js"></script>
</body>
</html>