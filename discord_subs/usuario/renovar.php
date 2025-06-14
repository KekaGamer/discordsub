<?php
require_once '../includes/config.php';
require_once '../includes/auth.php';
require_once '../includes/functions.php';

requireLogin();

$usuario = obtenerUsuario($_SESSION['usuario_id']);
$suscripcion_activa = obtenerSuscripcionActiva($_SESSION['usuario_id']);

if(!$suscripcion_activa) {
    header('Location: ../planes.php');
    exit();
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Renovar Suscripción - Discord Subs</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body class="bg-gray-900 text-white">
    <?php include '../includes/navbar.php'; ?>
    
    <div class="container mx-auto px-4 py-8">
        <div class="max-w-2xl mx-auto">
            <h1 class="text-3xl font-bold text-purple-500 mb-2">Renovar Suscripción</h1>
            <p class="text-gray-400 mb-8">Elige un nuevo plan para renovar tu suscripción</p>
            
            <div class="bg-gray-800 rounded-lg p-6 shadow-lg mb-8">
                <h2 class="text-xl font-bold text-purple-500 mb-4">Tu suscripción actual</h2>
                <div class="grid grid-cols-2 gap-4 mb-4">
                    <div>
                        <p class="text-sm text-gray-400">Tipo</p>
                        <p><?= $suscripcion_activa['tipo'] ?></p>
                    </div>
                    <div>
                        <p class="text-sm text-gray-400">Expiración</p>
                        <p><?= date('d/m/Y', strtotime($suscripcion_activa['fecha_termino'])) ?></p>
                    </div>
                </div>
            </div>
            
            <div class="grid md:grid-cols-3 gap-6 mb-8">
                <!-- Plan 1 Mes -->
                <div class="bg-gray-800 rounded-xl overflow-hidden shadow-lg">
                    <div class="bg-purple-600 p-4 text-center">
                        <h2 class="text-xl font-bold">1 Mes</h2>
                    </div>
                    <div class="p-4">
                        <div class="text-center mb-4">
                            <span class="text-3xl font-bold">$2,500</span>
                            <span class="text-gray-400">CLP</span>
                        </div>
                        <form action="../procesar/renovacion.php" method="POST">
                            <input type="hidden" name="tipo" value="1 mes">
                            <input type="hidden" name="monto" value="2500">
                            <button type="submit" class="w-full bg-purple-600 hover:bg-purple-700 text-white font-bold py-2 px-4 rounded">
                                Renovar
                            </button>
                        </form>
                    </div>
                </div>
                
                <!-- Plan 6 Meses -->
                <div class="bg-gray-800 rounded-xl overflow-hidden shadow-lg">
                    <div class="bg-indigo-600 p-4 text-center">
                        <h2 class="text-xl font-bold">6 Meses</h2>
                        <p class="text-xs text-gray-200">Ahorra 10%</p>
                    </div>
                    <div class="p-4">
                        <div class="text-center mb-4">
                            <span class="text-3xl font-bold">$13,500</span>
                            <span class="text-gray-400">CLP</span>
                            <p class="text-xs text-gray-400 mt-1">($2,250/mes)</p>
                        </div>
                        <form action="../procesar/renovacion.php" method="POST">
                            <input type="hidden" name="tipo" value="6 meses">
                            <input type="hidden" name="monto" value="13500">
                            <button type="submit" class="w-full bg-indigo-600 hover:bg-indigo-700 text-white font-bold py-2 px-4 rounded">
                                Renovar
                            </button>
                        </form>
                    </div>
                </div>
                
                <!-- Plan 1 Año -->
                <div class="bg-gray-800 rounded-xl overflow-hidden shadow-lg">
                    <div class="bg-pink-600 p-4 text-center">
                        <h2 class="text-xl font-bold">1 Año</h2>
                        <p class="text-xs text-gray-200">Ahorra 20%</p>
                    </div>
                    <div class="p-4">
                        <div class="text-center mb-4">
                            <span class="text-3xl font-bold">$28,500</span>
                            <span class="text-gray-400">CLP</span>
                            <p class="text-xs text-gray-400 mt-1">($2,375/mes)</p>
                        </div>
                        <form action="../procesar/renovacion.php" method="POST">
                            <input type="hidden" name="tipo" value="1 año">
                            <input type="hidden" name="monto" value="28500">
                            <button type="submit" class="w-full bg-pink-600 hover:bg-pink-700 text-white font-bold py-2 px-4 rounded">
                                Renovar
                            </button>
                        </form>
                    </div>
                </div>
            </div>
            
            <div class="bg-gray-800 rounded-lg p-6 shadow-lg">
                <h2 class="text-xl font-bold text-purple-500 mb-4">Instrucciones para renovar</h2>
                <ol class="list-decimal list-inside space-y-2">
                    <li>Selecciona el plan que deseas renovar</li>
                    <li>Realiza la transferencia a la cuenta bancaria indicada</li>
                    <li>Sube el comprobante de pago en el siguiente formulario</li>
                    <li>Espera la confirmación del administrador</li>
                </ol>
                
                <div class="mt-6 bg-gray-700 p-4 rounded-lg">
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
    </div>
    
    <script src="../assets/js/script.js"></script>
</body>
</html>