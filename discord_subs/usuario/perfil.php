<?php
require_once '../includes/config.php';
require_once '../includes/auth.php';
require_once '../includes/database.php'; 
require_once '../includes/functions.php';

requireLogin();
$db = new Database();
$conn = $db->connect();
$usuario_id = $_SESSION['usuario_id'];

$usuario = obtenerUsuario($conn, $usuario_id);
$suscripcion_activa = obtenerSuscripcionActiva($conn, $usuario_id);
$suscripcion_pendiente = $conn->prepare("SELECT * FROM suscripciones WHERE usuario_id = ? AND estado = 'pendiente'");
$suscripcion_pendiente->execute([$usuario_id]);
$pendiente = $suscripcion_pendiente->fetch();

// --- NUEVO: OBTENER NOTIFICACIONES ---
$notificaciones_stmt = $conn->prepare("SELECT * FROM notificaciones WHERE usuario_id = ? AND leido = 0 ORDER BY fecha_creacion DESC");
$notificaciones_stmt->execute([$usuario_id]);
$notificaciones = $notificaciones_stmt->fetchAll();
$pageTitle = "Mi Perfil";
include '../includes/header.php';
?>
<!DOCTYPE html>
<html lang="es">
<body class="bg-gray-900 text-white">
    <?php include '../includes/navbar.php'; ?>
    
    <div class="container mx-auto px-4 py-8">
        <?php if (!empty($notificaciones)): ?>
        <div class="mb-8 p-4 bg-indigo-900/50 border border-indigo-700 rounded-lg">
            <h2 class="text-xl font-bold text-indigo-300 mb-3">Notificaciones Pendientes</h2>
            <div class="space-y-3">
                <?php foreach ($notificaciones as $notificacion): ?>
                    <div class="flex justify-between items-center bg-gray-800 p-3 rounded-md">
                        <a href="<?= htmlspecialchars($notificacion['link']) ?>" class="text-gray-300 hover:text-white"><?= htmlspecialchars($notificacion['mensaje']) ?></a>
                        <a href="../procesar/notificacion.php?accion=marcar_leido&id=<?= $notificacion['id'] ?>" class="text-xs text-gray-400 hover:text-white ml-4 whitespace-nowrap">Marcar como leído</a>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
        <?php endif; ?>
        <?php if(isset($_SESSION['success'])): ?>
            <div class="bg-green-500 text-white p-3 rounded mb-6 text-center"><?= $_SESSION['success']; unset($_SESSION['success']); ?></div>
        <?php endif; ?>
        <?php if(isset($_SESSION['error'])): ?>
            <div class="bg-red-500 text-white p-3 rounded mb-6 text-center"><?= $_SESSION['error']; unset($_SESSION['error']); ?></div>
        <?php endif; ?>

        <div class="grid md:grid-cols-3 gap-8">
            <div class="md:col-span-2 bg-gray-800 rounded-lg p-6 shadow-lg">
                <h1 class="text-3xl font-bold text-purple-500 mb-6">Mi Perfil</h1>
                <form id="form-perfil" action="../procesar/perfil.php" method="POST" class="space-y-4">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label for="nombre" class="block text-sm font-medium text-gray-300 mb-1">Nombre</label>
                            <input type="text" name="nombre" value="<?= htmlspecialchars($usuario['nombre']) ?>" readonly class="mt-1 block w-full px-4 py-2 bg-gray-600 border border-gray-600 rounded-md cursor-not-allowed text-white">
                        </div>
                        <div>
                            <label for="apellido" class="block text-sm font-medium text-gray-300 mb-1">Apellido</label>
                            <input type="text" name="apellido" value="<?= htmlspecialchars($usuario['apellido']) ?>" readonly class="mt-1 block w-full px-4 py-2 bg-gray-600 border border-gray-600 rounded-md cursor-not-allowed text-white">
                        </div>
                    </div>
                    <div>
                        <label for="correo" class="block text-sm font-medium text-gray-300 mb-1">Correo Electrónico</label>
                        <input type="email" name="correo" value="<?= htmlspecialchars($usuario['correo']) ?>" readonly class="mt-1 block w-full px-4 py-2 bg-gray-600 border border-gray-600 rounded-md cursor-not-allowed text-white">
                    </div>
                    <div>
                        <label for="telefono" class="block text-sm font-medium text-gray-300 mb-1">Teléfono</label>
                        <input type="tel" name="telefono" value="<?= htmlspecialchars($usuario['telefono']) ?>" readonly class="mt-1 block w-full px-4 py-2 bg-gray-600 border border-gray-600 rounded-md cursor-not-allowed text-white">
                    </div>
                    <div>
                        <label for="instagram" class="block text-sm font-medium text-gray-300 mb-1">Cuenta Instagram (Editable)</label>
                        <input type="text" name="instagram" value="<?= htmlspecialchars($usuario['instagram']) ?>" class="mt-1 block w-full px-4 py-2 bg-gray-700 border border-gray-600 rounded-md focus:ring-purple-500 focus:border-purple-500 text-white">
                    </div>
                    <div>
                        <label for="discord" class="block text-sm font-medium text-gray-300 mb-1">Cuenta Discord (Editable)</label>
                        <input type="text" name="discord" value="<?= htmlspecialchars($usuario['discord']) ?>" required class="mt-1 block w-full px-4 py-2 bg-gray-700 border border-gray-600 rounded-md focus:ring-purple-500 focus:border-purple-500 text-white">
                    </div>
                    
                    <div class="pt-4 border-t border-gray-700">
                        <h3 class="text-lg font-medium text-gray-300 mb-2">Cambiar Contraseña</h3>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                             <div>
                                 <label for="password" class="block text-sm font-medium text-gray-300 mb-1">Nueva Contraseña</label>
                                 <input type="password" name="password" id="password" class="mt-1 block w-full px-4 py-2 bg-gray-700 border border-gray-600 rounded-md focus:ring-purple-500 focus:border-purple-500 text-white" placeholder="Dejar en blanco para no cambiar">
                            </div>
                             <div>
                                 <label for="confirm_password" class="block text-sm font-medium text-gray-300 mb-1">Confirmar Contraseña</label>
                                 <input type="password" name="confirm_password" id="confirm_password" class="mt-1 block w-full px-4 py-2 bg-gray-700 border border-gray-600 rounded-md focus:ring-purple-500 focus:border-purple-500 text-white">
                            </div>
                        </div>
                    </div>
                    <div class="pt-4"><button type="submit" class="btn-primary py-2 px-4 rounded">Guardar Cambios</button></div>
                </form>
            </div>
            
            <div class="bg-gray-800 rounded-lg p-6 shadow-lg h-fit">
                <h2 class="text-2xl font-bold text-purple-500 mb-4">Mi Suscripción</h2>
                
                <?php if($suscripcion_activa): ?>
                    <div class="bg-green-900/50 border border-green-700 rounded-lg p-4 mb-4">
                        <h3 class="font-bold text-green-400">Suscripción Activa</h3>
                        <p class="text-green-200"><?= htmlspecialchars($suscripcion_activa['tipo']) ?></p>
                        <p class="text-sm text-green-300 mt-2">Expira el <?= date('d/m/Y', strtotime($suscripcion_activa['fecha_termino'])) ?></p>
                    </div>
                    <a href="renovar.php" class="w-full bg-purple-600 hover:bg-purple-700 text-white font-bold py-2 px-4 rounded inline-block text-center">Renovar Suscripción</a>
                
                <?php elseif($pendiente): ?>
                    <div class="bg-yellow-900/50 border border-yellow-700 rounded-lg p-4 mb-4">
                        <h3 class="font-bold text-yellow-400">Suscripción Pendiente</h3>
                        <p class="text-yellow-200">Tu plan '<?= htmlspecialchars($pendiente['tipo']) ?>' está esperando aprobación.</p>
                        <?php if(empty($pendiente['comprobante_pago'])): ?>
                             <a href="subir_comprobante.php?id=<?= $pendiente['id'] ?>" class="mt-2 text-blue-400 hover:text-blue-300 underline">Sube tu comprobante aquí</a>
                        <?php else: ?>
                            <p class="text-sm text-gray-300 mt-2">Comprobante subido. Esperando revisión.</p>
                        <?php endif; ?>
                    </div>
                
                <?php else: ?>
                    <div class="bg-red-900/50 border border-red-700 rounded-lg p-4 mb-4">
                        <h3 class="font-bold text-red-400">Sin Suscripción</h3>
                        <p class="text-red-200">No tienes una suscripción activa.</p>
                    </div>
                    <a href="../planes.php" class="w-full bg-purple-600 hover:bg-purple-700 text-white font-bold py-2 px-4 rounded inline-block text-center">Ver Planes</a>
                <?php endif; ?>
            </div>
        </div>
    </div>
    <script src="../assets/js/script.js"></script>
</body>
</html>