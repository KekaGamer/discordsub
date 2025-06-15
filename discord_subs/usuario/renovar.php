<?php
require_once '../includes/config.php';
require_once '../includes/auth.php';
require_once '../includes/database.php';
require_once '../includes/functions.php';

requireLogin();
$db = new Database();
$conn = $db->connect();
$suscripcion_activa = obtenerSuscripcionActiva($conn, $_SESSION['usuario_id']);

if (!$suscripcion_activa) {
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
        <div class="max-w-4xl mx-auto">
            <h1 class="text-3xl font-bold text-purple-500 mb-2 text-center">Renovar Suscripción</h1>
            <p class="text-gray-400 mb-8 text-center">Tu nueva suscripción comenzará cuando la actual termine.</p>
            
            <div class="bg-gray-800 rounded-lg p-6 shadow-lg mb-8 text-center">
                <h2 class="text-xl font-bold text-purple-500 mb-2">Tu suscripción actual</h2>
                <p>Plan <span class="font-bold"><?= htmlspecialchars($suscripcion_activa['tipo']) ?></span>, expira el <span class="font-bold"><?= date('d/m/Y', strtotime($suscripcion_activa['fecha_termino'])) ?></span>.</p>
            </div>
            
            <div class="grid md:grid-cols-3 gap-6">
                <div class="card p-6 text-center">
                    <h2 class="text-xl font-bold">1 Mes</h2>
                    <p class="text-4xl font-bold my-4">$<?= number_format(PRECIO_MES, 0, ',', '.') ?></p>
                    <form action="../procesar/renovacion.php" method="POST">
                        <input type="hidden" name="tipo" value="1 mes"><input type="hidden" name="monto" value="<?= PRECIO_MES ?>">
                        <button type="submit" class="w-full btn-primary py-2 px-4 rounded">Renovar con este Plan</button>
                    </form>
                </div>
                <div class="card p-6 text-center border-2 border-indigo-500">
                    <h2 class="text-xl font-bold">6 Meses</h2>
                    <p class="text-4xl font-bold my-4">$<?= number_format(PRECIO_6MESES, 0, ',', '.') ?></p>
                    <form action="../procesar/renovacion.php" method="POST">
                        <input type="hidden" name="tipo" value="6 meses"><input type="hidden" name="monto" value="<?= PRECIO_6MESES ?>">
                        <button type="submit" class="w-full bg-indigo-600 hover:bg-indigo-700 text-white font-bold py-2 px-4 rounded">Renovar con este Plan</button>
                    </form>
                </div>
                <div class="card p-6 text-center">
                    <h2 class="text-xl font-bold">1 Año</h2>
                    <p class="text-4xl font-bold my-4">$<?= number_format(PRECIO_ANIO, 0, ',', '.') ?></p>
                    <form action="../procesar/renovacion.php" method="POST">
                        <input type="hidden" name="tipo" value="1 año"><input type="hidden" name="monto" value="<?= PRECIO_ANIO ?>">
                        <button type="submit" class="w-full btn-primary py-2 px-4 rounded">Renovar con este Plan</button>
                    </form>
                </div>
            </div>

            <div class="mt-12 bg-gray-800 rounded-lg p-6 shadow-lg">
                <h2 class="text-xl font-bold text-purple-500 mb-4">Instrucciones para Renovar</h2>
                 <ol class="list-decimal list-inside space-y-2 mb-6">
                    <li>Selecciona el nuevo plan con el que deseas renovar.</li>
                    <li>Realiza la transferencia a la cuenta bancaria indicada a continuación.</li>
                    <li>Sube el comprobante de pago en la página que aparecerá después de seleccionar el plan.</li>
                    <li>Espera la confirmación del administrador para activar tu renovación.</li>
                </ol>
                <div class="bg-gray-700 p-6 rounded-lg">
                    <h3 class="font-bold text-lg mb-2 text-white">Datos para Transferencia:</h3>
                    <ul class="space-y-2 text-gray-300">
                        <li><span class="font-semibold text-gray-100">Banco:</span> Mercado Pago</li>
                        <li><span class="font-semibold text-gray-100">Tipo de cuenta:</span> Cuenta Vista</li>
                        <li><span class="font-semibold text-gray-100">Número de cuenta:</span> 1087525538</li>
                        <li><span class="font-semibold text-gray-100">Titular:</span> Rodrigo Gonzalez Cerpa</li>
                        <li><span class="font-semibold text-gray-100">RUT:</span> 17.877.920-6</li>
                        <li><span class="font-semibold text-gray-100">Email para confirmación:</span> comprobante@kekagamer.cl</li>
                    </ul>
                </div>
            </div>
             </div>
    </div>
</body>
</html>