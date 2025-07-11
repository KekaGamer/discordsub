<?php
require_once '../includes/config.php';
require_once '../includes/auth.php';
require_once '../includes/database.php'; // <-- LÍNEA AÑADIDA
require_once '../includes/functions.php';

requireAdmin();
$db = new Database();
$conn = $db->connect();

$busqueda = isset($_GET['busqueda']) ? trim($_GET['busqueda']) : '';
$query = "SELECT * FROM usuarios WHERE nombre LIKE ? OR apellido LIKE ? OR discord LIKE ? OR correo LIKE ? ORDER BY fecha_registro DESC";
$stmt = $conn->prepare($query);
$stmt->execute(["%$busqueda%", "%$busqueda%", "%$busqueda%", "%$busqueda%"]);
$usuarios = $stmt->fetchAll(PDO::FETCH_ASSOC);
$pageTitle = "Gestión de Usuarios";
include '../includes/header.php';
?>
<!DOCTYPE html>
<html lang="es">
<body class="bg-gray-900 text-white">
    <?php include '../includes/navbar.php'; ?>
    <div class="container mx-auto px-4 py-8">
        <div class="flex justify-between items-center mb-6">
            <h1 class="text-3xl font-bold text-purple-500">Gestión de Usuarios</h1>
            <button onclick="mostrarModal('modal-agregar-usuario')" class="btn-primary py-2 px-4 rounded">Agregar Usuario</button>
        </div>
        <form method="GET" class="mb-6">
            <input type="text" name="busqueda" value="<?= htmlspecialchars($busqueda) ?>" placeholder="Buscar por nombre, discord, correo..." class="w-full mt-1 block px-4 py-2 bg-gray-700 border border-gray-600 rounded-md focus:ring-purple-500 focus:border-purple-500 text-white">
        </form>
        
        <div class="bg-gray-800 rounded-lg shadow-lg overflow-x-auto">
            <table class="min-w-full">
                <thead class="bg-gray-700">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-300 uppercase tracking-wider">Nombre</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-300 uppercase tracking-wider">Discord</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-300 uppercase tracking-wider">Correo</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-300 uppercase tracking-wider">Rol/Estado</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-300 uppercase tracking-wider">Registro</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-300 uppercase tracking-wider">Acciones</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-700">
                <?php foreach($usuarios as $usuario): ?>
                    <tr>
                        <td class="px-6 py-4 whitespace-nowrap"><?= htmlspecialchars($usuario['nombre'] . ' ' . $usuario['apellido']) ?></td>
                        <td class="px-6 py-4 whitespace-nowrap"><?= htmlspecialchars($usuario['discord']) ?></td>
                        <td class="px-6 py-4 whitespace-nowrap"><?= htmlspecialchars($usuario['correo']) ?></td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <span class="px-2 py-1 text-xs font-semibold rounded-full text-white <?= $usuario['rol'] === 'admin' ? 'bg-purple-600' : 'bg-blue-600' ?>"><?= ucfirst($usuario['rol']) ?></span>
                            <span class="px-2 py-1 text-xs font-semibold rounded-full text-white <?= $usuario['estado'] === 'activo' ? 'bg-green-600' : 'bg-red-600' ?>"><?= ucfirst($usuario['estado']) ?></span>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap"><?= date('d/m/Y', strtotime($usuario['fecha_registro'])) ?></td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <button onclick='mostrarModalEditar(<?= json_encode($usuario) ?>)' class="text-blue-400 hover:text-blue-300 mr-3">Editar</button>
                            <button onclick='confirmarEliminar(<?= $usuario['id'] ?>)' class="text-red-400 hover:text-red-300">Eliminar</button>
                        </td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
    
    <div id="modal-agregar-usuario" class="modal">
        <div class="modal-content">
            <h2 class="text-2xl font-bold text-purple-500 mb-4">Agregar Usuario</h2>
            <form action="../procesar/admin_usuarios.php" method="POST" class="space-y-4">
                <input type="hidden" name="accion" value="agregar">
                <div>
                    <label class="block text-sm font-medium text-gray-300 mb-1">Nombre</label>
                    <input type="text" name="nombre" required class="mt-1 block w-full px-4 py-2 bg-gray-700 border border-gray-600 rounded-md focus:ring-purple-500 focus:border-purple-500 text-white">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-300 mb-1">Apellido</label>
                    <input type="text" name="apellido" required class="mt-1 block w-full px-4 py-2 bg-gray-700 border border-gray-600 rounded-md focus:ring-purple-500 focus:border-purple-500 text-white">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-300 mb-1">Correo</label>
                    <input type="email" name="correo" required class="mt-1 block w-full px-4 py-2 bg-gray-700 border border-gray-600 rounded-md focus:ring-purple-500 focus:border-purple-500 text-white">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-300 mb-1">Discord</label>
                    <input type="text" name="discord" required class="mt-1 block w-full px-4 py-2 bg-gray-700 border border-gray-600 rounded-md focus:ring-purple-500 focus:border-purple-500 text-white">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-300 mb-1">Contraseña</label>
                    <input type="password" name="password" required class="mt-1 block w-full px-4 py-2 bg-gray-700 border border-gray-600 rounded-md focus:ring-purple-500 focus:border-purple-500 text-white">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-300 mb-1">Rol</label>
                    <select name="rol" class="mt-1 block w-full px-4 py-2 bg-gray-700 border border-gray-600 rounded-md focus:ring-purple-500 focus:border-purple-500 text-white">
                        <option value="usuario">Usuario</option>
                        <option value="admin">Admin</option>
                    </select>
                </div>
                <div class="flex justify-end space-x-2">
                    <button type="button" onclick="cerrarModal('modal-agregar-usuario')" class="btn-secondary">Cancelar</button>
                    <button type="submit" class="btn-primary">Guardar</button>
                </div>
            </form>
        </div>
    </div>

    <div id="modal-editar-usuario" class="modal">
         <div class="modal-content">
            <h2 class="text-2xl font-bold text-purple-500 mb-4">Editar Usuario</h2>
            <form id="form-editar-usuario" action="../procesar/admin_usuarios.php" method="POST" class="space-y-4">
                <input type="hidden" name="accion" value="editar"><input type="hidden" id="editar-id" name="id">
                <div>
                    <label class="block text-sm font-medium text-gray-300 mb-1">Nombre</label>
                    <input type="text" id="editar-nombre" name="nombre" required class="mt-1 block w-full px-4 py-2 bg-gray-700 border border-gray-600 rounded-md focus:ring-purple-500 focus:border-purple-500 text-white">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-300 mb-1">Apellido</label>
                    <input type="text" id="editar-apellido" name="apellido" required class="mt-1 block w-full px-4 py-2 bg-gray-700 border border-gray-600 rounded-md focus:ring-purple-500 focus:border-purple-500 text-white">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-300 mb-1">Discord</label>
                    <input type="text" id="editar-discord" name="discord" required class="mt-1 block w-full px-4 py-2 bg-gray-700 border border-gray-600 rounded-md focus:ring-purple-500 focus:border-purple-500 text-white">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-300 mb-1">Nueva Contraseña (opcional)</label>
                    <input type="password" name="password" class="mt-1 block w-full px-4 py-2 bg-gray-700 border border-gray-600 rounded-md focus:ring-purple-500 focus:border-purple-500 text-white">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-300 mb-1">Rol</label>
                    <select id="editar-rol" name="rol" class="mt-1 block w-full px-4 py-2 bg-gray-700 border border-gray-600 rounded-md focus:ring-purple-500 focus:border-purple-500 text-white">
                        <option value="usuario">Usuario</option>
                        <option value="admin">Admin</option>
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-300 mb-1">Estado</label>
                    <select id="editar-estado" name="estado" class="mt-1 block w-full px-4 py-2 bg-gray-700 border border-gray-600 rounded-md focus:ring-purple-500 focus:border-purple-500 text-white">
                        <option value="activo">Activo</option>
                        <option value="bloqueado">Bloqueado</option>
                    </select>
                </div>
                <div class="flex justify-end space-x-2">
                    <button type="button" onclick="cerrarModal('modal-editar-usuario')" class="btn-secondary">Cancelar</button>
                    <button type="submit" class="btn-primary">Guardar Cambios</button>
                </div>
            </form>
        </div>
    </div>
    
    <div id="modal-confirmar-eliminar" class="modal">
        <div class="modal-content">
            <h2 class="text-2xl font-bold text-red-500 mb-4">Confirmar Eliminación</h2>
            <p>¿Estás seguro de que deseas eliminar este usuario? Se borrarán también todas sus suscripciones. Esta acción no se puede deshacer.</p>
            <form action="../procesar/admin_usuarios.php" method="POST" class="mt-6 flex justify-end space-x-2">
                <input type="hidden" name="accion" value="eliminar"><input type="hidden" id="eliminar-id" name="id">
                <button type="button" onclick="cerrarModal('modal-confirmar-eliminar')" class="btn-secondary">Cancelar</button>
                <button type="submit" class="bg-red-600 hover:bg-red-700 text-white font-bold py-2 px-4 rounded">Eliminar</button>
            </form>
        </div>
    </div>
    <script src="../assets/js/script.js"></script>
</body>
</html>