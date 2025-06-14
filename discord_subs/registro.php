<?php include 'includes/config.php'; ?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registro - Discord Subs</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body class="bg-gray-900 text-white">
    <div class="min-h-screen flex items-center justify-center">
        <div class="w-full max-w-md bg-gray-800 rounded-lg shadow-lg p-8">
            <div class="text-center mb-8">
                <h1 class="text-3xl font-bold text-purple-500">Discord Subs</h1>
                <p class="text-gray-400">Crea tu cuenta</p>
            </div>
            
            <?php if(isset($_SESSION['error'])): ?>
                <div class="bg-red-500 text-white p-3 rounded mb-4">
                    <?= $_SESSION['error']; unset($_SESSION['error']); ?>
                </div>
            <?php endif; ?>
            
            <form action="procesar/registro.php" method="POST" class="space-y-4">
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label for="nombre" class="block text-sm font-medium text-gray-300">Nombre</label>
                        <input type="text" id="nombre" name="nombre" required
                               class="mt-1 block w-full px-4 py-2 bg-gray-700 border border-gray-600 rounded-md focus:ring-purple-500 focus:border-purple-500">
                    </div>
                    <div>
                        <label for="apellido" class="block text-sm font-medium text-gray-300">Apellido</label>
                        <input type="text" id="apellido" name="apellido" required
                               class="mt-1 block w-full px-4 py-2 bg-gray-700 border border-gray-600 rounded-md focus:ring-purple-500 focus:border-purple-500">
                    </div>
                </div>
                
                <div>
                    <label for="correo" class="block text-sm font-medium text-gray-300">Correo Electrónico</label>
                    <input type="email" id="correo" name="correo" required
                           class="mt-1 block w-full px-4 py-2 bg-gray-700 border border-gray-600 rounded-md focus:ring-purple-500 focus:border-purple-500">
                </div>
                
                <div>
                    <label for="telefono" class="block text-sm font-medium text-gray-300">Teléfono</label>
                    <input type="tel" id="telefono" name="telefono"
                           class="mt-1 block w-full px-4 py-2 bg-gray-700 border border-gray-600 rounded-md focus:ring-purple-500 focus:border-purple-500">
                </div>
                
                <div>
                    <label for="instagram" class="block text-sm font-medium text-gray-300">Cuenta Instagram</label>
                    <input type="text" id="instagram" name="instagram"
                           class="mt-1 block w-full px-4 py-2 bg-gray-700 border border-gray-600 rounded-md focus:ring-purple-500 focus:border-purple-500">
                </div>
                
                <div>
                    <label for="discord" class="block text-sm font-medium text-gray-300">Cuenta Discord (ej: usuario#1234)</label>
                    <input type="text" id="discord" name="discord" required
                           class="mt-1 block w-full px-4 py-2 bg-gray-700 border border-gray-600 rounded-md focus:ring-purple-500 focus:border-purple-500">
                </div>
                
                <div>
                    <label for="password" class="block text-sm font-medium text-gray-300">Contraseña</label>
                    <input type="password" id="password" name="password" required
                           class="mt-1 block w-full px-4 py-2 bg-gray-700 border border-gray-600 rounded-md focus:ring-purple-500 focus:border-purple-500">
                </div>
                
                <div>
                    <label for="confirm_password" class="block text-sm font-medium text-gray-300">Confirmar Contraseña</label>
                    <input type="password" id="confirm_password" name="confirm_password" required
                           class="mt-1 block w-full px-4 py-2 bg-gray-700 border border-gray-600 rounded-md focus:ring-purple-500 focus:border-purple-500">
                </div>
                
                <div class="flex items-center">
                    <input type="checkbox" id="terminos" name="terminos" required
                           class="h-4 w-4 text-purple-600 focus:ring-purple-500 border-gray-600 rounded bg-gray-700">
                    <label for="terminos" class="ml-2 block text-sm text-gray-300">
                        Acepto los <a href="#" class="text-purple-400 hover:text-purple-300">Términos y Condiciones</a>
                    </label>
                </div>
                
                <div>
                    <button type="submit" class="w-full bg-purple-600 hover:bg-purple-700 text-white font-bold py-2 px-4 rounded transition duration-200">
                        Registrarse
                    </button>
                </div>
            </form>
            
            <div class="mt-6 text-center">
                <p class="text-gray-400">¿Ya tienes una cuenta? 
                    <a href="login.php" class="text-purple-400 hover:text-purple-300">Inicia sesión aquí</a>
                </p>
            </div>
        </div>
    </div>
</body>
</html>