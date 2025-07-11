<?php
require_once 'includes/config.php';
// Incluimos los archivos necesarios para que la navbar funcione correctamente
require_once 'includes/database.php';
require_once 'includes/functions.php';
require_once 'includes/auth.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
// La variable $pageTitle ya no es necesaria si definimos el <title> directamente
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registro - KekaGamer</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body class="bg-gray-900 text-white">

    <?php include 'includes/navbar.php'; // ?>

    <div class="min-h-screen flex items-center justify-center py-24">
        <div class="w-full max-w-lg bg-gray-800 rounded-lg shadow-lg p-8 animated-form-box">
            <div class="text-center mb-8">
                <img src="https://kekagamer.cl/assets/images/logo.png" alt="Logo KekaGamer" class="mx-auto mb-4" style="width: 100px;">
                <h1 class="text-3xl font-bold text-purple-400">Crear Cuenta</h1>
            </div>
            
            <?php if(isset($_SESSION['error'])): ?>
                <div class="bg-red-500 bg-opacity-50 border border-red-700 text-white p-3 rounded mb-4 text-center"><?= $_SESSION['error']; unset($_SESSION['error']); ?></div>
            <?php endif; ?>
            
            <form id="form-registro" action="procesar/registro.php" method="POST" class="space-y-4">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label for="nombre" class="block text-sm font-medium text-gray-300 mb-1">Nombre</label>
                        <input type="text" id="nombre" name="nombre" required autocomplete="given-name" class="mt-1 block w-full px-4 py-2 bg-gray-700 bg-opacity-75 border border-gray-600 rounded-md focus:ring-purple-500 focus:border-purple-500 text-white">
                    </div>
                    <div>
                        <label for="apellido" class="block text-sm font-medium text-gray-300 mb-1">Apellido</label>
                        <input type="text" id="apellido" name="apellido" required autocomplete="family-name" class="mt-1 block w-full px-4 py-2 bg-gray-700 bg-opacity-75 border border-gray-600 rounded-md focus:ring-purple-500 focus:border-purple-500 text-white">
                    </div>
                </div>
                <div>
                    <label for="correo" class="block text-sm font-medium text-gray-300 mb-1">Correo Electrónico</label>
                    <input type="email" id="correo" name="correo" required autocomplete="email" class="mt-1 block w-full px-4 py-2 bg-gray-700 bg-opacity-75 border border-gray-600 rounded-md focus:ring-purple-500 focus:border-purple-500 text-white">
                </div>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label for="telefono" class="block text-sm font-medium text-gray-300 mb-1">Teléfono</label>
                        <input type="tel" id="telefono" name="telefono" required autocomplete="tel" class="mt-1 block w-full px-4 py-2 bg-gray-700 bg-opacity-75 border border-gray-600 rounded-md focus:ring-purple-500 focus:border-purple-500 text-white">
                    </div>
                    <div>
                        <label for="instagram" class="block text-sm font-medium text-gray-300 mb-1">Instagram (Opcional)</label>
                        <input type="text" id="instagram" name="instagram" placeholder="@usuario" autocomplete="off" class="mt-1 block w-full px-4 py-2 bg-gray-700 bg-opacity-75 border border-gray-600 rounded-md focus:ring-purple-500 focus:border-purple-500 text-white">
                    </div>
                </div>
                <div>
                    <label for="discord" class="block text-sm font-medium text-gray-300 mb-1">Cuenta Discord</label>
                    <input type="text" id="discord" name="discord" required placeholder="usuario#1234" autocomplete="off" class="mt-1 block w-full px-4 py-2 bg-gray-700 bg-opacity-75 border border-gray-600 rounded-md focus:ring-purple-500 focus:border-purple-500 text-white">
                </div>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label for="password" class="block text-sm font-medium text-gray-300 mb-1">Contraseña</label>
                        <input type="password" id="password" name="password" required autocomplete="new-password" class="mt-1 block w-full px-4 py-2 bg-gray-700 bg-opacity-75 border border-gray-600 rounded-md focus:ring-purple-500 focus:border-purple-500 text-white">
                    </div>
                    <div>
                        <label for="confirm_password" class="block text-sm font-medium text-gray-300 mb-1">Confirmar Contraseña</label>
                        <input type="password" id="confirm_password" name="confirm_password" required autocomplete="new-password" class="mt-1 block w-full px-4 py-2 bg-gray-700 bg-opacity-75 border border-gray-600 rounded-md focus:ring-purple-500 focus:border-purple-500 text-white">
                    </div>
                </div>
                <div>
                    <button type="submit" class="w-full bg-purple-600 hover:bg-purple-700 text-white font-bold py-3 px-4 rounded transition duration-200">
                        Registrarse
                    </button>
                </div>
            </form>
            <div class="mt-6 text-center">
                <p class="text-gray-400">¿Ya tienes una cuenta? <a href="login.php" class="text-purple-400 hover:text-purple-300">Inicia sesión</a></p>
            </div>
        </div>
    </div>
    <script src="assets/js/script.js"></script>
</body>
</html>