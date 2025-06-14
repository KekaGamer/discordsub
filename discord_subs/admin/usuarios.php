<?php
require_once '../includes/config.php';
require_once '../includes/auth.php';
require_once '../includes/functions.php';

requireAdmin();

$db = new Database();
$conn = $db->connect();

// Manejar búsqueda
$busqueda = isset($_GET['busqueda']) ? trim($_GET['busqueda']) : '';

$query = "SELECT * FROM usuarios WHERE 1=1";
$params = [];

if(!empty($busqueda)) {
    $query .= " AND (nombre LIKE :busqueda OR apellido LIKE :busqueda OR discord LIKE :busqueda OR correo LIKE :busqueda)";
    $params[':busqueda'] = "%$busqueda%";
}

$query .= " ORDER BY fecha_registro DESC";

$stmt = $conn->prepare($query);
foreach($params as $key => $value) {
    $stmt->bindValue($key, $value);
}
$stmt->execute();
$usuarios = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Usuarios - Admin</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body class="bg-gray-900 text-white">
    <?php include '../includes/navbar.php'; ?>
    
    <div class="container mx-auto px-4 py-8">
        <div class="flex justify-between items-center mb-8">
            <h1 class="text-3xl font-bold text-purple-500">Gestión de Usuarios</h1>
            <a href="javascript:void(0)" onclick="mostrarModal('modal-agregar-usuario')" class="bg-green-600 hover:bg-green-700 text-white font-bold py-2 px-4 rounded">
                Agregar Usuario
            </a>
        </div>
        
        <!-- Buscador -->
        <form method="GET" class="mb-6">
            <div class="flex">
                <input type="text" name="busqueda" value="<?= htmlspecialchars($busqueda) ?>" placeholder="Buscar usuarios..." 
                       class="flex-grow px-4 py-2 bg-gray-700 border border-gray-600 rounded-l-md focus:ring-purple-500 focus:border-purple-500">
                <button type="submit" class="bg-purple-600 hover:bg-purple-700 text-white font-bold py-2 px-4 rounded-r-md">
                    Buscar
                </button>
            </div>
        </form>
        
        <!-- Tabla de usuarios -->
        <div class="bg-gray-800 rounded-lg shadow-lg overflow-hidden">
            <div class="overflow-x-auto">
                <table class="min-w-full">
                    <thead class="bg-gray-700">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-300 uppercase tracking-wider">ID</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-300 uppercase tracking-wider">Nombre</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-300 uppercase tracking-wider">Discord</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-300 uppercase tracking-wider">Correo</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-300 uppercase tracking-wider">Rol</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-300 uppercase tracking-wider">Registro</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-300 uppercase tracking-wider">Acciones</th>
                        </tr>
                    </thead>
                    <tbody class="bg-gray-800 divide-y divide-gray-700">
                        <?php foreach($usuarios as $usuario): ?>
                        <tr>
                            <td class="px-6 py-4 whitespace-nowrap"><?= $usuario['id'] ?></td>
                            <td class="px-6 py-4 whitespace-nowrap"><?= htmlspecialchars($usuario['nombre'] . ' ' . $usuario['apellido']) ?></td>
                            <td class="px-6 py-4 whitespace-nowrap"><?= htmlspecialchars($usuario['discord']) ?></td>
                            <td class="px-6 py-4 whitespace-nowrap"><?= htmlspecialchars($usuario['correo']) ?></td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full <?= $usuario['rol'] === 'admin' ? 'bg-purple-600 text-white' : 'bg-gray-600 text-gray-300' ?>">
                                    <?= ucfirst($usuario['rol']) ?>
                                </span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap"><?= date('d/m/Y', strtotime($usuario['fecha_registro'])) ?></td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <button onclick="mostrarModalEditar(<?= htmlspecialchars(json_encode($usuario), ENT_QUOTES, 'UTF-8') ?>)" 
                                        class="text-blue-400 hover:text-blue-300 mr-3">Editar</button>
                                <button onclick="confirmarEliminar(<?= $usuario['id'] ?>)" 
                                        class="text-red-400 hover:text-red-300">Eliminar</button>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    
    <!-- Modal Agregar Usuario -->
    <div id="modal-agregar-usuario" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center hidden">
        <div class="bg-gray-800 rounded-lg p-6 w-full max-w-md">
            <h2 class="text-2xl font-bold text-purple-500 mb-4">Agregar Usuario</h2>
            
            <form id="form-agregar-usuario" action="../procesar/admin_usuarios.php" method="POST">
                <input type="hidden" name="accion" value="agregar">
                
                <div class="grid grid-cols-2 gap-4 mb-4">
                    <div>
                        <label for="nombre" class="block text-sm font-medium text-gray-300 mb-1">Nombre</label>
                        <input type="text" id="nombre" name="nombre" required class="w-full px-3 py-2 bg-gray-700 border border-gray-600 rounded-md">
                    </div>
                    <div>
                        <label for="apellido" class="block text-sm font-medium text-gray-300 mb-1">Apellido</label>
                        <input type="text" id="apellido" name="apellido" required class="w-full px-3 py-2 bg-gray-700 border border-gray-600 rounded-md">
                    </div>
                </div>
                
                <div class="mb-4">
                    <label for="correo" class="block text-sm font-medium text-gray-300 mb-1">Correo</label>
                    <input type="email" id="correo" name="correo" required class="w-full px-3 py-2 bg-gray-700 border border-gray-600 rounded-md">
                </div>
                
                <div class="mb-4">
                    <label for="discord" class="block text-sm font-medium text-gray-300 mb-1">Discord</label>
                    <input type="text" id="discord" name="discord" required class="w-full px-3 py-2 bg-gray-700 border border-gray-600 rounded-md">
                </div>
                
                <div class="mb-4">
                    <label for="password" class="block text-sm font-medium text-gray-300 mb-1">Contraseña</label>
                    <input type="password" id="password" name="password" required class="w-full px-3 py-2 bg-gray-700 border border-gray-600 rounded-md">
                </div>
                
                <div class="mb-4">
                    <label for="rol" class="block text-sm font-medium text-gray-300 mb-1">Rol</label>
                    <select id="rol" name="rol" class="w-full px-3 py-2 bg-gray-700 border border-gray-600 rounded-md">
                        <option value="usuario">Usuario</option>
                        <option value="admin">Administrador</option>
                    </select>
                </div>
                
                <div class="flex justify-end space-x-3">
                    <button type="button" onclick="cerrarModal('modal-agregar-usuario')" class="px-4 py-2 bg-gray-600 hover:bg-gray-700 rounded-md">
                        Cancelar
                    </button>
                    <button type="submit" class="px-4 py-2 bg-purple-600 hover:bg-purple-700 rounded-md">
                        Guardar
                    </button>
                </div>
            </form>
        </div>
    </div>
    
    <!-- Modal Editar Usuario -->
    <div id="modal-editar-usuario" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center hidden">
        <div class="bg-gray-800 rounded-lg p-6 w-full max-w-md">
            <h2 class="text-2xl font-bold text-purple-500 mb-4">Editar Usuario</h2>
            
            <form id="form-editar-usuario" action="../procesar/admin_usuarios.php" method="POST">
                <input type="hidden" name="accion" value="editar">
                <input type="hidden" id="editar-id" name="id">
                
                <div class="grid grid-cols-2 gap-4 mb-4">
                    <div>
                        <label for="editar-nombre" class="block text-sm font-medium text-gray-300 mb-1">Nombre</label>
                        <input type="text" id="editar-nombre" name="nombre" required class="w-full px-3 py-2 bg-gray-700 border border-gray-600 rounded-md">
                    </div>
                    <div>
                        <label for="editar-apellido" class="block text-sm font-medium text-gray-300 mb-1">Apellido</label>
                        <input type="text" id="editar-apellido" name="apellido" required class="w-full px-3 py-2 bg-gray-700 border border-gray-600 rounded-md">
                    </div>
                </div>
                
                <div class="mb-4">
                    <label for="editar-correo" class="block text-sm font-medium text-gray-300 mb-1">Correo</label>
                    <input type="email" id="editar-correo" name="correo" required class="w-full px-3 py-2 bg-gray-700 border border-gray-600 rounded-md">
                </div>
                
                <div class="mb-4">
                    <label for="editar-discord" class="block text-sm font-medium text-gray-300 mb-1">Discord</label>
                    <input type="text" id="editar-discord" name="discord" required class="w-full px-3 py-2 bg-gray-700 border border-gray-600 rounded-md">
                </div>
                
                <div class="mb-4">
                    <label for="editar-password" class="block text-sm font-medium text-gray-300 mb-1">Nueva Contraseña (opcional)</label>
                    <input type="password" id="editar-password" name="password" class="w-full px-3 py-2 bg-gray-700 border border-gray-600 rounded-md">
                </div>
                
                <div class="mb-4">
                    <label for="editar-rol" class="block text-sm font-medium text-gray-300 mb-1">Rol</label>
                    <select id="editar-rol" name="rol" class="w-full px-3 py-2 bg-gray-700 border border-gray-600 rounded-md">
                        <option value="usuario">Usuario</option>
                        <option value="admin">Administrador</option>
                    </select>
                </div>
                
                <div class="flex justify-end space-x-3">
                    <button type="button" onclick="cerrarModal('modal-editar-usuario')" class="px-4 py-2 bg-gray-600 hover:bg-gray-700 rounded-md">
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
            <p class="mb-6">¿Estás seguro que deseas eliminar este usuario? Esta acción no se puede deshacer.</p>
            
            <form id="form-eliminar-usuario" action="../procesar/admin_usuarios.php" method="POST">
                <input type="hidden" name="accion" value="eliminar">
                <input type="hidden" id="eliminar-id" name="id">
                
                <div class="flex justify-end space-x-3">
                    <button type="button" onclick="cerrarModal('modal-confirmar-eliminar')" class="px-4 py-2 bg-gray-600 hover:bg-gray-700 rounded-md">
                        Cancelar
                    </button>
                    <button type="submit" class="px-4 py-2 bg-red-600 hover:bg-red-700 rounded-md">
                        Eliminar
                    </button>
                </div>
            </form>
        </div>
    </div>
    
    <script src="../assets/js/script.js"></script>
    <script>
    function mostrarModalEditar(usuario) {
        document.getElementById('editar-id').value = usuario.id;
        document.getElementById('editar-nombre').value = usuario.nombre;
        document.getElementById('editar-apellido').value = usuario.apellido;
        document.getElementById('editar-correo').value = usuario.correo;
        document.getElementById('editar-discord').value = usuario.discord;
        document.getElementById('editar-rol').value = usuario.rol;
        
        mostrarModal('modal-editar-usuario');
    }
    
    function confirmarEliminar(id) {
        document.getElementById('eliminar-id').value = id;
        mostrarModal('modal-confirmar-eliminar');
    }
    </script>
</body>
</html>