<?php
// Se incluyen todos los archivos necesarios para que la página funcione
require_once 'includes/config.php';
require_once 'includes/auth.php';
require_once 'includes/database.php';
require_once 'includes/functions.php';

// Se exige que el usuario haya iniciado sesión
requireLogin();

// Se conecta a la base de datos
$db = new Database();
$conn = $db->connect();

// Se verifica si es una sesión única y válida
require_once 'includes/session_check.php';

// Se verifica que el usuario tenga una suscripción activa
$suscripcion_activa = obtenerSuscripcionActiva($conn, $_SESSION['usuario_id']);

if (!$suscripcion_activa && !isAdmin()) {
    $_SESSION['error'] = "Necesitas una suscripción activa para ver la transmisión.";
    header('Location: planes.php');
    exit();
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Transmisión en Vivo - Discord Subs</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
    <link rel="stylesheet" href="assets/css/style.css">
    <style>
        .video-container {
            position: relative;
            overflow: hidden;
            width: 100%;
            padding-top: 56.25%; 
        }
        .video-container iframe {
            position: absolute;
            top: 0;
            left: 0;
            bottom: 0;
            right: 0;
            width: 100%;
            height: 100%;
            border: none;
        }
        /* --- ESTILOS PARA LA MARCA DE AGUA --- */
        .watermark {
            position: absolute;
            top: 20px;
            left: 20px;
            color: white;
            background-color: rgba(0, 0, 0, 0.5);
            padding: 5px 10px;
            border-radius: 5px;
            font-size: 14px;
            opacity: 0.7;
            pointer-events: none; /* Para poder hacer clic a través de la marca de agua */
            z-index: 10;
        }
    </style>
</head>
<body class="bg-gray-900 text-white">
    <?php include 'includes/navbar.php'; ?>
    
    <div class="container mx-auto px-4 py-8">
        <h1 class="text-3xl font-bold text-purple-500 mb-6">Transmisión en Vivo</h1>

        <div class="flex flex-col lg:flex-row gap-4">

            <div class="w-full lg:w-3/4 relative"> <div class="video-container rounded-lg shadow-lg">
                    <iframe
                        src="http://172.25.122.241:8080/embed/video"
                        title="Transmisión de video de Owncast"
                        allowfullscreen>
                    </iframe>
                </div>
                <div class="watermark">
                    Viendo como: <?= htmlspecialchars($_SESSION['usuario_discord']) ?>
                </div>
            </div>

            <div class="w-full lg:w-1/4">
                <iframe
                    class="w-full h-[70vh] lg:h-full rounded-lg"
                    src="http://172.25.122.241:8080/embed/chat"
                    title="Chat de Owncast">
                </iframe>
            </div>

        </div>

        <div class="mt-8">
            <h2 class="text-2xl font-bold text-purple-500 mb-4">Próximos Eventos</h2>
            </div>
    </div>
</body>
</html>