<?php
require_once 'includes/config.php';
require_once 'includes/auth.php';
require_once 'includes/functions.php';

requireLogin();

$usuario = obtenerUsuario($_SESSION['usuario_id']);
$suscripcion_activa = obtenerSuscripcionActiva($_SESSION['usuario_id']);
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Planes - Discord Subs</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body class="bg-gray-900 text-white">
    <?php include 'includes/navbar.php'; ?>
    
    <div class="container mx-auto px-4 py-8">
        <div class="text-center mb-12">
            <h1 class="text-4xl font-bold text-purple-500 mb-2">Planes de Suscripción</h1>
            <p class="text-xl text-gray-400">Elige el plan que mejor se adapte a tus necesidades</p>
        </div>
        
        <?php if($suscripcion_activa): ?>
            <div class="bg-green-900 text-green-200 p-4 rounded-lg mb-8">
                <p class="font-bold">¡Ya tienes una suscripción activa!</p>
                <p>Tu suscripción <?= $suscripcion_activa['tipo'] ?> expira el <?= date('d/m/Y', strtotime($suscripcion_activa['fecha_termino'])) ?>.</p>
            </div>
        <?php endif; ?>
        
        <div class="grid md:grid-cols-3 gap-8">
            <!-- Plan 1 Mes -->
            <div class="bg-gray-800 rounded-xl overflow-hidden shadow-lg transform transition hover:scale-105">
                <div class="bg-purple-600 p-6 text-center">
                    <h2 class="text-2xl font-bold">1 Mes</h2>
                    <p class="text-gray-200">Ideal para probar el servicio</p>
                </div>
                <div class="p-6">
                    <div class="text-center mb-6">
                        <span class="text-4xl font-bold">$2,500</span>
                        <span class="text-gray-400">CLP</span>
                    </div>
                    <ul class="space-y-3 mb-8">
                        <li class="flex items-center">
                            <svg class="w-5 h-5 text-green-500 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                            </svg>
                            Acceso completo por 1 mes
                        </li>
                        <li class="flex items-center">
                            <svg class="w-5 h-5 text-green-500 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                            </svg>
                            Soporte prioritario
                        </li>
                        <li class="flex items-center">
                            <svg class="w-5 h-5 text-green-500 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                            </svg>
                            Renovable manualmente
                        </li>
                    </ul>
                    <form action="procesar/pago.php" method="POST">
                        <input type="hidden" name="tipo" value="1 mes">
                        <input type="hidden" name="monto" value="2500">
                        <button type="submit" class="w-full bg-purple-600 hover:bg-purple-700 text-white font-bold py-3 px-4 rounded transition duration-200">
                            <?= $suscripcion_activa ? 'Renovar' : 'Suscribirse' ?>
                        </button>
                    </form>
                </div>
            </div>
            
            <!-- Plan 6 Meses -->
            <div class="bg-gray-800 rounded-xl overflow-hidden shadow-lg transform transition hover:scale-105">
                <div class="bg-indigo-600 p-6 text-center">
                    <h2 class="text-2xl font-bold">6 Meses</h2>
                    <p class="text-gray-200">Ahorra un 10%</p>
                </div>
                <div class="p-6">
                    <div class="text-center mb-6">
                        <span class="text-4xl font-bold">$13,500</span>
                        <span class="text-gray-400">CLP</span>
                        <p class="text-sm text-gray-400 mt-1">($2,250/mes)</p>
                    </div>
                    <ul class="space-y-3 mb-8">
                        <li class="flex items-center">
                            <svg class="w-5 h-5 text-green-500 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                            </svg>
                            Acceso completo por 6 meses
                        </li>
                        <li class="flex items-center">
                            <svg class="w-5 h-5 text-green-500 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                            </svg>
                            Soporte prioritario
                        </li>
                        <li class="flex items-center">
                            <svg class="w-5 h-5 text-green-500 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                            </svg>
                            Ahorro del 10%
                        </li>
                    </ul>
                    <form action="procesar/pago.php" method="POST">
                        <input type="hidden" name="tipo" value="6 meses">
                        <input type="hidden" name="monto" value="13500">
                        <button type="submit" class="w-full bg-indigo-600 hover:bg-indigo-700 text-white font-bold py-3 px-4 rounded transition duration-200">
                            <?= $suscripcion_activa ? 'Renovar' : 'Suscribirse' ?>
                        </button>
                    </form>
                </div>
            </div>
            
            <!-- Plan 1 Año -->
            <div class="bg-gray-800 rounded-xl overflow-hidden shadow-lg transform transition hover:scale-105">
                <div class="bg-pink-600 p-6 text-center">
                    <h2 class="text-2xl font-bold">1 Año</h2>
                    <p class="text-gray-200">Ahorra un 20%</p>
                </div>
                <div class="p-6">
                    <div class="text-center mb-6">
                        <span class="text-4xl font-bold">$28,500</span>
                        <span class="text-gray-400">CLP</span>
                        <p class="text-sm text-gray-400 mt-1">($2,375/mes)</p>
                    </div>
                    <ul class="space-y-3 mb-8">
                        <li class="flex items-center">
                            <svg class="w-5 h-5 text-green-500 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                            </svg>
                            Acceso completo por 1 año
                        </li>
                        <li class="flex items-center">
                            <svg class="w-5 h-5 text-green-500 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                            </svg>
                            Soporte prioritario 24/7
                        </li>
                        <li class="flex items-center">
                            <svg class="w-5 h-5 text-green-500 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                            </svg>
                            Ahorro del 20%
                        </li>
                    </ul>
                    <form action="procesar/pago.php" method="POST">
                        <input type="hidden" name="tipo" value="1 año">
                        <input type="hidden" name="monto" value="28500">
                        <button type="submit" class="w-full bg-pink-600 hover:bg-pink-700 text-white font-bold py-3 px-4 rounded transition duration-200">
                            <?= $suscripcion_activa ? 'Renovar' : 'Suscribirse' ?>
                        </button>
                    </form>
                </div>
            </div>
        </div>
        
        <div class="mt-12 bg-gray-800 rounded-lg p-6">
            <h2 class="text-2xl font-bold text-purple-500 mb-4">Método de Pago</h2>
            <p class="text-gray-300 mb-4">Actualmente solo aceptamos pagos por transferencia bancaria. Una vez realizado el pago, deberás subir el comprobante para activar tu suscripción.</p>
            
            <div class="bg-gray-700 p-4 rounded-lg">
                <h3 class="font-bold text-lg mb-2">Datos para transferencia:</h3>
                <ul class="space-y-2">
                    <li><span class="font-semibold">Banco:</span> Banco de Chile</li>
                    <li><span class="font-semibold">Tipo de cuenta:</span> Cuenta Corriente</li>
                    <li><span class="font-semibold">Número de cuenta:</span> 123-45678-90</li>
                    <li><span class="font-semibold">Titular:</span> Rodrigo González Cerpa</li>
                    <li><span class="font-semibold">RUT:</span> 12.345.678-9</li>
                    <li><span class="font-semibold">Email para confirmación:</span> gonzalez.cerpa.rodrigo.a@gmail.com</li>
                </ul>
            </div>
        </div>
    </div>
</body>
</html>