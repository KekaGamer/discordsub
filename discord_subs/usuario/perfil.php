<?php
require_once '../includes/config.php';
require_once '../includes/auth.php';
require_once '../includes/functions.php';

requireLogin();

$usuario = obtenerUsuario($_SESSION['usuario_id']);
$suscripcion_activa = obtenerSuscripcionActiva($_SESSION['usuario_id']);
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mi Perfil - Discord Subs</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body class="bg-gray-900 text-white">
    <?php include '../includes/navbar.php'; ?>
    
    <div class="container mx-auto px-4 py-8">
        <div class="flex justify-between items-center mb-8">
            <h1 class="text-3xl font-bold text-purple-500">Mi Perfil</h1>
            <a href="renovar.php" class="bg-purple-600 hover:bg-purple-700 text-white font-bold py-2 px-4 rounded">
                Renovar Suscripción
            </a>
        </div>
        
        <div class="grid md:grid-cols-3 gap-8">
            <!-- Información del usuario -->
            <div class="md:col-span-2 bg-gray-800 rounded-lg p-6 shadow-lg">
                <h2 class="text-2xl font-bold text-purple-500 mb-4">Información Personal</h2>
                
                <form id="form-perfil" action="../procesar/perfil.php" method="POST" class="space-y-4">
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label for="nombre" class="block text-sm font-medium text-gray-300 mb-1">Nombre</label>
                            <input type="text" id="nombre" name="nombre" value="<?= htmlspecialchars($usuario['nombre']) ?>" required
                                   class="w-full px-3 py-2 bg-gray-700 border border-gray-600 rounded-md">
                        </div>
                        <div>
                            <label for="apellido" class="block text-sm font-medium text-gray-300 mb-1">Apellido</label>
                            <input type="text" id="apellido" name="apellido" value="<?= htmlspecialchars($usuario['apellido']) ?>" required
                                   class="w-full px-3 py-2 bg-gray-700 border border-gray-600 rounded-md">
                        </div>
                    </div>
                    
                    <div>
                        <label for="correo" class="block text-sm font-medium text-gray-300 mb-1">Correo Electrónico</label>
                        <input type="email" id="correo" name="correo" value="<?= htmlspecialchars($usuario['correo']) ?>" required
                               class="w-full px-3 py-2 bg-gray-700 border border-gray-600 rounded-md">
                    </div>
                    
                    <div>
                        <label for="telefono" class="block text-sm font-medium text-gray-300 mb-1">Teléfono</label>
                        <input type="tel" id="telefono" name="telefono" value="<?= htmlspecialchars($usuario['telefono']) ?>"
                               class="w-full px-3 py-2 bg-gray-700 border border-gray-600 rounded-md">
                    </div>
                    
                    <div>
                        <label for="instagram" class="block text-sm font-medium text-gray-300 mb-1">Cuenta Instagram</label>
                        <input type="text" id="instagram" name="instagram" value="<?= htmlspecialchars($usuario['instagram']) ?>"
                               class="w-full px-3 py-2 bg-gray-700 border border-gray-600 rounded-md">
                    </div>
                    
                    <div>
                        <label for="discord" class="block text-sm font-medium text-gray-300 mb-1">Cuenta Discord</label>
                        <input type="text" id="discord" name="discord" value="<?= htmlspecialchars($usuario['discord']) ?>" required
                               class="w-full px-3 py-2 bg-gray-700 border border-gray-600 rounded-md">
                    </div>
                    
                    <div class="pt-4">
                        <h3 class="text-lg font-medium text-gray-300 mb-2">Cambiar Contraseña</h3>
                        <div class="space-y-4">
                            <div>
                                <label for="password" class="block text-sm font-medium text-gray-300 mb-1">Nueva Contraseña</label>
                                <input type="password" id="password" name="password"
                                       class="w-full px-3 py-2 bg-gray-700 border border-gray-600 rounded-md">
                            </div>
                            <div>
                                <label for="confirm_password" class="block text-sm font-medium text-gray-300 mb-1">Confirmar Nueva Contraseña</label>
                                <input type="password" id="confirm_password" name="confirm_password"
                                       class="w-full px-3 py-2 bg-gray-700 border border-gray-600 rounded-md">
                            </div>
                        </div>
                    </div>
                    
                    <div class="pt-4">
                        <button type="submit" class="bg-purple-600 hover:bg-purple-700 text-white font-bold py-2 px-4 rounded">
                            Guardar Cambios
                        </button>
                    </div>
                </form>
            </div>
            
            <!-- Estado de suscripción -->
            <div class="bg-gray-800 rounded-lg p-6 shadow-lg h-fit">
                <h2 class="text-2xl font-bold text-purple-500 mb-4">Mi Suscripción</h2>
                
                <?php if($suscripcion_activa): ?>
                    <div class="bg-green-900/30 border border-green-800 rounded-lg p-4 mb-4">
                        <h3 class="font-bold text-green-400 mb-1">Suscripción Activa</h3>
                        <p class="text-green-200"><?= $suscripcion_activa['tipo'] ?></p>
                        <p class="text-sm text-green-300 mt-2">Expira el <?= date('d/m/Y', strtotime($suscripcion_activa['fecha_termino'])) ?></p>
                    </div>
                    
                    <div class="space-y-3">
                        <div>
                            <p class="text-sm text-gray-400">Fecha de inicio</p>
                            <p><?= date('d/m/Y', strtotime($suscripcion_activa['fecha_inicio'])) ?></p>
                        </div>
                        <div>
                            <p class="text-sm text-gray-400">Monto pagado</p>
                            <p>$<?= number_format($suscripcion_activa['monto'], 0, ',', '.') ?> CLP</p>
                        </div>
                        <div>
                            <p class="text-sm text-gray-400">Método de pago</p>
                            <p><?= ucfirst($suscripcion_activa['metodo_pago']) ?></p>
                        </div>
                    </div>
                <?php else: ?>
                    <div class="bg-yellow-900/30 border border-yellow-800 rounded-lg p-4 mb-4">
                        <h3 class="font-bold text-yellow-400 mb-1">Sin Suscripción Activa</h3>
                        <p class="text-yellow-200">No tienes una suscripción activa en este momento.</p>
                    </div>
                    
                    <a href="../planes.php" class="w-full bg-purple-600 hover:bg-purple-700 text-white font-bold py-2 px-4 rounded inline-block text-center">
                        Suscribirse
                    </a>
                <?php endif; ?>
            </div>
        </div>
    </div>
    
    <script src="../assets/js/script.js"></script>
</body>
</html>