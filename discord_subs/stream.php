<?php
// Se incluyen todos los archivos necesarios para que la página funcione
require_once 'includes/config.php';
require_once 'includes/auth.php';
require_once 'includes/database.php';
require_once 'includes/functions.php';

// Se exige que el usuario haya iniciado sesión
requireLogin();

// Se conecta a la base de datos y verifica la sesión y suscripción
$db = new Database();
$conn = $db->connect();
require_once 'includes/session_check.php';
$suscripcion_activa = obtenerSuscripcionActiva($conn, $_SESSION['usuario_id']);

// Si no es admin y no tiene suscripción activa, se le redirige a la página de planes
if (!$suscripcion_activa && !isAdmin()) {
    $_SESSION['error'] = "Necesitas una suscripción activa para ver la transmisión.";
    header('Location: planes.php');
    exit();
}

$pageTitle = "Transmisión en Vivo";
$extraStyles = '
    <style>
        .video-container {
            position: relative;
            overflow: hidden;
            width: 100%;
            padding-top: 56.25%; /* Proporción 16:9 para el video */
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
        .watermark {
            position: absolute;
            bottom: 15px;
            right: 15px;
            color: white;
            padding: 2px 8px;
            font-size: 11px;
            opacity: 0.4;
            pointer-events: none;
            z-index: 10;
            text-shadow: 1px 1px 2px black;
        }
    </style>';
include 'includes/header.php';
?>
<body class="bg-gray-900 text-white">
    <?php include 'includes/navbar.php'; ?>
    
    <div class="container mx-auto px-4 py-8">
        <h1 class="text-3xl font-bold text-purple-500 mb-6">Transmisión en Vivo</h1>

        <div class="flex flex-col lg:flex-row gap-4">

            <div class="w-full lg:w-3/4">
                <div id="video-wrapper" class="relative">
                    <div class="video-container rounded-lg shadow-lg">
                        <iframe
                            src="https://stream.kekagamer.cl/embed/video"
                            title="Transmisión de video de Owncast"
                            allowfullscreen>
                        </iframe>
                    </div>
                    <div class="watermark">
                        Viendo como: <?= htmlspecialchars($_SESSION['usuario_discord']) ?>
                    </div>
                </div>

                <div class="mt-4 text-center">
                    <button id="cinema-mode-btn" class="bg-purple-600 hover:bg-purple-700 text-white font-bold py-2 px-6 rounded-lg inline-flex items-center">
                        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 8V4m0 0h4M4 4l5 5m11-1V4m0 0h-4m4 0l-5 5M4 16v4m0 0h4m-4 0l5-5m11 5v-4m0 4h-4m4 0l-5-5"></path></svg>
                        Modo Cine / Horizontal
                    </button>
                </div>
            </div>

            <div class="w-full lg:w-1/4">
                <iframe
                    class="w-full h-[70vh] lg:h-full rounded-lg"
                    src="https://stream.kekagamer.cl/embed/chat"
                    title="Chat de Owncast">
                </iframe>
            </div>
        </div>

        <div class="mt-8">
            </div>
    </div>

    <script>
        const cinemaButton = document.getElementById('cinema-mode-btn');
        const videoWrapper = document.getElementById('video-wrapper');

        // Verificar si el botón y el contenedor existen
        if (cinemaButton && videoWrapper) {
            cinemaButton.addEventListener('click', () => {
                // 1. Solicitar Pantalla Completa
                // Se intenta con diferentes prefijos para máxima compatibilidad de navegadores
                if (videoWrapper.requestFullscreen) {
                    videoWrapper.requestFullscreen();
                } else if (videoWrapper.webkitRequestFullscreen) { // Safari
                    videoWrapper.webkitRequestFullscreen();
                } else if (videoWrapper.msRequestFullscreen) { // IE11
                    videoWrapper.msRequestFullscreen();
                }

                // 2. Intentar bloquear la orientación a horizontal
                // Esto puede fallar silenciosamente en navegadores que no lo soportan (como en muchos iPhones)
                try {
                    screen.orientation.lock('landscape').catch(error => {
                        // No mostramos el error al usuario, solo en la consola del desarrollador
                        console.warn('No se pudo bloquear la orientación:', error);
                    });
                } catch (error) {
                    console.warn('La API de orientación de pantalla no es compatible en este navegador:', error);
                }
            });
        }
    </script>
    </body>
</html>