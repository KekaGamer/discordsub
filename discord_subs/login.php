<?php
require_once 'includes/config.php';
// Incluimos la base de datos y las funciones para que navbar.php pueda funcionar
require_once 'includes/database.php';
require_once 'includes/functions.php';
require_once 'includes/auth.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Iniciar Sesión - KekaGamer</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body class="bg-gray-900 text-white">

    <?php include 'includes/navbar.php'; ?>

    <div class="min-h-screen flex flex-col items-center justify-center pt-24 pb-12 px-4">
        
        <div class="hero-banner text-center w-full max-w-2xl">
            <h1 class="text-3xl md:text-4xl font-extrabold text-gradient mb-2">Bienvenid@ a KekaGamer</h1>
            <p class="text-lg text-gray-300">La plataforma para nuestra comunidad de suscriptores.</p>
        </div>

        <div class="w-full max-w-md bg-gray-800 rounded-lg shadow-lg p-8 animated-form-box">
            <div class="text-center mb-6">
                <h2 class="text-2xl font-bold text-purple-300">Iniciar Sesión</h2>
            </div>
            
            <?php if(isset($_SESSION['error'])): ?>
                <div class="bg-red-500 bg-opacity-50 border border-red-700 text-white p-3 rounded mb-4 text-center"><?= $_SESSION['error']; unset($_SESSION['error']); ?></div>
            <?php endif; ?>
            <?php if(isset($_SESSION['success'])): ?>
                <div class="bg-green-500 bg-opacity-50 border border-green-700 text-white p-3 rounded mb-4 text-center"><?= $_SESSION['success']; unset($_SESSION['success']); ?></div>
            <?php endif; ?>
            
            <form action="procesar/login.php" method="POST" class="space-y-6">
                <div>
                    <label for="discord" class="block text-sm font-medium text-gray-300">Usuario Discord (ej: usuario#1234)</label>
                    <input type="text" id="discord" name="discord" required class="mt-1 block w-full px-4 py-2 bg-gray-700 bg-opacity-75 border-gray-600 rounded-md focus:ring-purple-500 focus:border-purple-500 text-white">
                </div>
                
                <div>
                    <label for="password" class="block text-sm font-medium text-gray-300">Contraseña</label>
                    <input type="password" id="password" name="password" required class="mt-1 block w-full px-4 py-2 bg-gray-700 bg-opacity-75 border-gray-600 rounded-md focus:ring-purple-500 focus:border-purple-500 text-white">
                </div>
                
                <div class="text-right">
                    <a href="olvido_password.php" class="text-sm text-purple-400 hover:text-purple-300">¿Olvidaste tu contraseña?</a>
                </div>
                
                <div>
                    <button type="submit" class="w-full bg-purple-600 hover:bg-purple-700 text-white font-bold py-2 px-4 rounded transition duration-200">
                        Acceder
                    </button>
                </div>
            </form>
            
            <div class="mt-6 text-center">
                <p class="text-gray-400">¿No tienes una cuenta? <a href="registro.php" class="text-purple-400 hover:text-purple-300">Regístrate aquí</a></p>
            </div>
        </div>
    </div>
</body>
</html>