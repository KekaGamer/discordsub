<?php
require_once 'includes/config.php';
require_once 'includes/auth.php';
require_once 'includes/database.php';
require_once 'includes/functions.php';

requireLogin();

$db = new Database();
$conn = $db->connect();
$suscripcion_activa = obtenerSuscripcionActiva($conn, $_SESSION['usuario_id']);

$pageTitle = "Planes de Suscripción";
include 'includes/header.php';
?>
<body class="bg-gray-900 text-white">
    <?php include 'includes/navbar.php'; ?>
    
    <div class="container mx-auto px-4 py-8">
        <div class="text-center mb-12">
            <h1 class="text-4xl font-bold text-purple-500 mb-2">Planes de Suscripción</h1>
            <p class="text-xl text-gray-400">Elige el plan que mejor se adapte a ti</p>
        </div>
        
        <?php if(isset($_SESSION['success'])): ?>
            <div class="bg-green-500 text-white p-3 rounded mb-6 text-center max-w-4xl mx-auto"><?= $_SESSION['success']; unset($_SESSION['success']); ?></div>
        <?php endif; ?>
        <?php if(isset($_SESSION['error'])): ?>
            <div class="bg-red-500 text-white p-3 rounded mb-6 text-center max-w-4xl mx-auto"><?= $_SESSION['error']; unset($_SESSION['error']); ?></div>
        <?php endif; ?>

        <?php if($suscripcion_activa): ?>
            <div class="bg-green-900 text-green-200 p-4 rounded-lg mb-8 max-w-4xl mx-auto text-center">
                <p>✨ ¡Ya tienes una suscripción activa! (<?= htmlspecialchars($suscripcion_activa['tipo']) ?>) que expira el <?= date('d/m/Y', strtotime($suscripcion_activa['fecha_termino'])) ?>. Puedes renovar desde tu perfil.</p>
            </div>
        <?php endif; ?>
        
        <div class="grid md:grid-cols-3 gap-8 max-w-6xl mx-auto">
            
            <div class="card transform transition hover:scale-105">
                <div class="p-6">
                    <h2 class="text-2xl font-bold text-center text-purple-400">1 Mes</h2>
                    <div class="text-center my-6">
                        <span class="text-5xl font-bold">$<?= number_format(PRECIO_MES, 0, ',', '.') ?></span>
                        <span class="text-gray-400"> CLP</span>
                    </div>
                    <form action="procesar/pago.php" method="POST">
                        <input type="hidden" name="tipo" value="1 mes">
                        <input type="hidden" name="monto" value="<?= PRECIO_MES ?>">
                        <button type="submit" class="w-full btn-primary text-white font-bold py-3 px-4 rounded">
                            <?= $suscripcion_activa ? 'Renovar Plan' : 'Suscribirse' ?>
                        </button>
                    </form>
                </div>
            </div>
            
            <div class="card transform transition hover:scale-105 border-2 border-indigo-500">
                <div class="p-6">
                     <h2 class="text-2xl font-bold text-center text-indigo-400">6 Meses</h2>
                    <div class="text-center my-6">
                        <span class="text-5xl font-bold">$<?= number_format(PRECIO_6MESES, 0, ',', '.') ?></span>
                        <span class="text-gray-400"> CLP</span>
                        <p class="text-sm text-gray-400 mt-1">(Ahorra un 10%)</p>
                    </div>
                    <form action="procesar/pago.php" method="POST">
                        <input type="hidden" name="tipo" value="6 meses">
                        <input type="hidden" name="monto" value="<?= PRECIO_6MESES ?>">
                        <button type="submit" class="w-full bg-indigo-600 hover:bg-indigo-700 text-white font-bold py-3 px-4 rounded">
                             <?= $suscripcion_activa ? 'Renovar Plan' : 'Suscribirse' ?>
                        </button>
                    </form>
                </div>
            </div>
            
            <div class="card transform transition hover:scale-105">
                <div class="p-6">
                    <h2 class="text-2xl font-bold text-center text-pink-400">1 Año</h2>
                    <div class="text-center my-6">
                        <span class="text-5xl font-bold">$<?= number_format(PRECIO_ANIO, 0, ',', '.') ?></span>
                        <span class="text-gray-400"> CLP</span>
                        <p class="text-sm text-gray-400 mt-1">(Ahorra un 5%)</p>
                    </div>
                    <form action="procesar/pago.php" method="POST">
                        <input type="hidden" name="tipo" value="1 año">
                        <input type="hidden" name="monto" value="<?= PRECIO_ANIO ?>">
                        <button type="submit" class="w-full btn-primary text-white font-bold py-3 px-4 rounded">
                             <?= $suscripcion_activa ? 'Renovar Plan' : 'Suscribirse' ?>
                        </button>
                    </form>
                </div>
            </div>
        </div>
        
        <div class="mt-12 bg-gray-800 rounded-lg p-8 max-w-4xl mx-auto">
            <h2 class="text-2xl font-bold text-purple-500 mb-4 text-center">¿Cómo funciona?</h2>
            <ol class="list-decimal list-inside space-y-3 text-gray-300">
                <li>Elige el plan que prefieras y haz clic en "Suscribirse".</li>
                <li>Se creará una solicitud de suscripción en tu perfil con estado "Pendiente".</li>
                <li>Serás redirigido a una página para que puedas subir tu comprobante de transferencia.</li>
                <li>Una vez que el administrador apruebe tu pago, tu suscripción se activará y recibirás acceso completo.</li>
            </ol>
        </div>

        <div class="mt-12 bg-gray-800 rounded-lg p-8 max-w-4xl mx-auto">
            <h2 class="text-2xl font-bold text-purple-500 mb-4 text-center">Método de Pago</h2>
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
</body>
</html>