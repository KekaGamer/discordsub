<?php
require_once 'includes/config.php';
require_once 'includes/auth.php';
require_once 'includes/database.php';

// --- FUNCIÓN PARA VERIFICAR EL ESTADO DE LOS SERVICIOS ---
function check_service_status($url) {
    // Usamos cURL que es más robusto
    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
    curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 5);
    curl_setopt($ch, CURLOPT_TIMEOUT, 10);
    curl_setopt($ch, CURLOPT_NOBODY, true);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
    curl_exec($ch);

    if (curl_errno($ch)) {
        curl_close($ch);
        return ['status' => 'Indisponible', 'class' => 'text-red-500', 'dot' => 'bg-red-500'];
    }
    $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    return ($http_code >= 200 && $http_code < 400) 
        ? ['status' => 'Operacional', 'class' => 'text-green-400', 'dot' => 'bg-green-400 animate-pulse']
        : ['status' => 'Indisponible', 'class' => 'text-red-500', 'dot' => 'bg-red-500'];
}

$status_pagina_web = check_service_status('https://kekagamer.cl/'); 
$status_transmisiones = check_service_status('https://stream.kekagamer.cl');

// --- OBTENER ANUNCIOS DE LA BASE DE DATOS ---
try {
    $db = new Database();
    $conn = $db->connect();
    $anuncios_stmt = $conn->query("SELECT * FROM anuncios ORDER BY fecha_creacion DESC LIMIT 3");
    $anuncios = $anuncios_stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (Exception $e) {
    $anuncios = []; 
}

?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Comunidad KekaGamer - El Centro de Operaciones</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css" integrity="sha512-SnH5WK+bZxgPHs44uWIX+LLJAJ9/2PkPKZ5QiAj6Ta86w+fsb2TkcmfRyVX3pBnMFcV7oQPJkl9QevSCWr3W6A==" crossorigin="anonymous" referrerpolicy="no-referrer" />
    <link rel="stylesheet" href="https://kekagamer.cl/assets/css/style.css">
    <style>
        /* --- ESTILOS (Sin cambios) --- */
        @keyframes fadeIn { from { opacity: 0; transform: translateY(20px); } to { opacity: 1; transform: translateY(0); } }
        @keyframes animated-gradient { 0% { background-position: 0% 50%; } 50% { background-position: 100% 50%; } 100% { background-position: 0% 50%; } }
        .fade-in-section { animation: fadeIn 0.8s ease-out forwards; }
        .hero-banner { padding: 4rem 1rem; border-radius: 1rem; background: linear-gradient(45deg, #1f2937, #4c1d95, #1f2937, #5b21b6); background-size: 300% 300%; animation: animated-gradient 15s ease infinite; border: 1px solid #8b5cf6; }
        .text-gradient { background-image: linear-gradient(45deg, #a78bfa, #f9a8d4); -webkit-background-clip: text; -webkit-text-fill-color: transparent; background-clip: text; text-fill-color: transparent; }
        .community-card { background-color: #1e293b; border-radius: 0.75rem; padding: 1.5rem; transition: transform 0.3s ease, box-shadow 0.3s ease; border: 1px solid #374151; display: flex; flex-direction: column; height: 100%; overflow: hidden; position: relative; }
        .community-card:hover { transform: translateY(-10px); box-shadow: 0 0 30px rgba(139, 92, 246, 0.2); border-color: #a78bfa; }
        .card-icon { font-size: 40px; line-height: 1; margin-bottom: 1rem; color: #a78bfa; text-align: left; width: 48px; height: 48px; }
        .card-title { color: #c4b5fd; font-size: 1.25rem; font-weight: 700; margin-bottom: 0.5rem; }
        .card-description { color: #d1d5db; flex-grow: 1; }
        .card-button { margin-top: 1.5rem; background-image: linear-gradient(to right, #8b5cf6, #7c3aed); color: white; padding: 0.75rem 1.5rem; border-radius: 0.5rem; text-align: center; font-weight: 600; transition: all 0.3s ease; display: inline-block; border: none; box-shadow: 0 4px 15px rgba(0,0,0,0.2); }
        .card-button:hover { transform: scale(1.05); box-shadow: 0 6px 20px rgba(139, 92, 246, 0.4); }
        .social-icon-grid { display: flex; flex-wrap: wrap; gap: 1.5rem; margin-top: 1rem; justify-content: center; }
        .social-icon-grid a { color: #d1d5db; transition: all 0.2s ease-in-out; font-size: 28px; }
        .social-icon-grid a:hover { color: white; transform: scale(1.1); }
        .status-list li, .announcements-list li { display: flex; justify-content: space-between; align-items: center; padding: 0.75rem 0; border-bottom: 1px solid #374151; }
        .announcements-list li { flex-direction: column; align-items: flex-start; gap: 0.25rem; }
        .status-list li:last-child, .announcements-list li:last-child { border-bottom: none; }
        .status-indicator { font-weight: 700; }
        .scroll-reveal { opacity: 0; transform: translateY(40px); transition: opacity 0.8s ease-out, transform 0.8s ease-out; }
        .scroll-reveal.is-visible { opacity: 1; transform: translateY(0); }
    </style>
</head>
<body class="bg-gray-900 text-white">

    <?php include 'includes/navbar.php'; ?>

    <div class="container mx-auto px-4 py-12 fade-in-section">
        
        <div class="hero-banner text-center mb-12">
            <img src="https://kekagamer.cl/assets/images/logo.png" alt="Logo de KekaGamer" class="mx-auto mb-4 drop-shadow-lg" style="width: 150px;">
            <h1 class="text-4xl md:text-6xl font-extrabold text-gradient mb-2">Bienvenid@ al Hub de KekaGamer</h1>
            <p class="text-xl text-gray-300">¡El corazón de nuestra comunidad! Todo lo que necesitas está aquí.</p>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
            
            <div class="community-card lg:col-span-2">
                <i class="fa-brands fa-discord card-icon"></i>
                <h3 class="card-title">💬 Únete a nuestro Discord: ¡El Centro de Todo!</h3>
                <div class="card-description">
                    <p>Nuestra comunidad siempre está activa. Es el mejor lugar para enterarte de todo, participar en eventos y disfrutar de contenido gratuito como:</p>
                    <ul class="list-disc list-inside mt-2 text-gray-300 space-y-1">
                        <li><strong>Retransmisiones de WWE</strong></li>
                        <li><strong>Películas</strong></li>
                        <li><strong>Series Animadas</strong></li>
                    </ul>
                </div>
                <a href="https://discord.gg/eW35YyK6h9" target="_blank" class="card-button">¡Entrar al Discord!</a>
            </div>

            <div class="community-card">
                 <i class="fa-solid fa-calendar-days card-icon"></i>
                <h3 class="card-title">🗓️ Horario de Transmisión</h3>
                 <div class="card-description">
                    <p>Nuestro horario es dinámico y se ajusta a la programación de los siguientes eventos:</p>
                    <ul class="list-disc list-inside mt-2 text-gray-300 space-y-1">
                        <li>Liga de Primera Itau</li>
                        <li>Copa Chile</li>
                        <li>Champions League & Europa League</li>
                        <li>Copa Sudamericana & Libertadores</li>
                        <li>Mundial De Clubes, Amistosos y Partidos FIFA</li>
                    </ul>
                    <p class="text-xs text-gray-500 mt-4">* La transmisión dependerá de que los horarios no se crucen. En caso de superposición, se realizarán encuestas o se dará prioridad a lo nacional. ¡El calendario final siempre se anuncia en Discord!</p>
                </div>
            </div>
            
            <div class="community-card lg:col-span-2">
                 <i class="fa-solid fa-ticket-simple card-icon"></i>
                <h3 class="card-title">🎫 Soporte y Gestión de Cuenta</h3>
                <div class="mt-2">
                    <h4 class="font-bold text-purple-300">Sistema de Tickets</h4>
                    <p class="text-sm text-gray-400 mt-1 mb-3">Para registrar incidencias, dudas o sugerencias. Sigue el avance y recibe soluciones.</p>
                    <a href="https://kekagamer.cl/ticket.php" class="card-button">Acceder al Portal</a>
                </div>
                <hr class="my-6 border-gray-700">
                <div>
                    <h4 class="font-bold text-purple-300">Mi Cuenta</h4>
                    <p class="text-sm text-gray-400 mt-1 mb-3">Asegúrate de que tu suscripción esté activa para no perderte las transmisiones exclusivas.</p>
                    <a href="https://kekagamer.cl/usuario/perfil.php" class="card-button">Ir a Mi Perfil</a>
                </div>
            </div>
            
            <div class="community-card">
                <i class="fa-solid fa-gamepad card-icon"></i>
                <h3 class="card-title">🎮 Otras Transmisiones Gamer</h3>
                <p class="card-description">¡La diversión no para! Síguenos en nuestras otras plataformas para más contenido de videojuegos y transmisiones variadas.</p>
                <div class="mt-auto pt-4">
                    <a href="https://kick.com/kekagamer" target="_blank" class="card-button" style="background-image: linear-gradient(to right, #53fc18, #18a442); color: #000;">Ver en Kick</a>
                    <a href="https://twitch.tv/tiokekagamer" target="_blank" class="card-button mt-2" style="background-image: linear-gradient(to right, #9146ff, #6f1ff4);">Ver en Twitch</a>
                </div>
            </div>

            <div class="community-card lg:col-span-2">
                <i class="fa-solid fa-bullhorn card-icon"></i>
                <h3 class="card-title">🔔 Anuncios Recientes</h3>
                <ul class="announcements-list mt-4 text-sm space-y-4">
                    <?php if (!empty($anuncios)): ?>
                        <?php foreach($anuncios as $anuncio): ?>
                        <li>
                            <span class="text-xs font-bold text-purple-400"><?= date('d M, Y', strtotime($anuncio['fecha_creacion'])) ?></span>
                            <p class="font-semibold text-gray-200"><?= htmlspecialchars($anuncio['titulo']) ?></p>
                            <p class="text-gray-400"><?= htmlspecialchars($anuncio['mensaje']) ?></p>
                        </li>
                        <?php endforeach; ?>
                    <?php else: ?>
                         <p class="text-gray-500">No hay anuncios recientes.</p>
                    <?php endif; ?>
                </ul>
            </div>
            
            <div class="community-card">
                <i class="fa-solid fa-server card-icon"></i>
                <h3 class="card-title">📊 Estado de los Servicios</h3>
                <ul class="status-list mt-4 text-sm flex-grow">
                    <li><span>Página Web</span><span class="status-indicator <?= $status_pagina_web['class'] ?>"><?= $status_pagina_web['status'] ?></span></li>
                    <li><span>Servidor Transmisiones</span><span class="status-indicator <?= $status_transmisiones['class'] ?>"><?= $status_transmisiones['status'] ?></span></li>
                </ul>
            </div>
            
            <div class="community-card">
                <i class="fa-solid fa-satellite-dish card-icon"></i>
                <h3 class="card-title">🌐 Síguenos en Redes</h3>
                <p class="card-description">¡Mantente conectado!</p>
                <div class="social-icon-grid mt-auto pt-4">
                    <a href="https://www.instagram.com/kekagamer2021" target="_blank" title="Instagram"><i class="fa-brands fa-instagram"></i></a>
                    <a href="https://x.com/TWOS2024" target="_blank" title="X (Twitter)"><i class="fa-brands fa-x-twitter"></i></a>
                    <a href="https://tiktok.com/@kekagamermobile" target="_blank" title="TikTok"><i class="fa-brands fa-tiktok"></i></a>
                </div>
            </div>
        </div>

        <div class="text-center mt-16 text-gray-500">
            <p>¡Gracias por ser parte de KekaGamer! ¡Nos vemos en el próximo directo!</p>
        </div>
    </div>

    <script>
    document.addEventListener('DOMContentLoaded', function() {
        const observer = new IntersectionObserver((entries) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    entry.target.classList.add('is-visible');
                }
            });
        }, {
            threshold: 0.1
        });
        const targets = document.querySelectorAll('.scroll-reveal');
        targets.forEach(target => {
            observer.observe(target);
        });
    });
    </script>
</body>
</html>