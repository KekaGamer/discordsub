<?php
// includes/navbar.php (VERSIÓN FINAL, REVISADA Y COMPLETA)

/* --- BLOQUE DE NOTIFICACIONES ---
* NOTA: Este bloque fue la causa de un error anterior. Se mantiene desactivado (comentado)
* para garantizar la estabilidad del sitio.
*/
$notificaciones_no_leidas = 0;
/*
if (isLoggedIn()) {
    $db_nav = @new Database();
    if ($db_nav) {
        $conn_nav = $db_nav->connect();
        if ($conn_nav) {
            $query_notif = "SELECT COUNT(*) as total FROM notificaciones WHERE usuario_id = :usuario_id AND leido = 0";
            $stmt_notif = $conn_nav->prepare($query_notif);
            $stmt_notif->execute([':usuario_id' => $_SESSION['usuario_id']]);
            $notificaciones_no_leidas = $stmt_notif->fetchColumn();
        }
    }
}
*/
?>

<nav class="navbar border-b border-gray-800 sticky top-0 z-50 bg-gray-900 text-white">
    <div class="container mx-auto px-4 py-3 flex justify-between items-center">
        <a href="<?= SITE_URL ?>index.php" class="navbar-brand">
            <img src="<?= SITE_URL ?>assets/images/logo.png" alt="Logo KekaGamer" style="height: 2rem; width: auto; display: block;">
        </a>

        <div class="hidden md:flex items-center space-x-6">
            <a href="<?= SITE_URL ?>comunidad.php" class="nav-link">Comunidad</a>
            
            <?php if(isLoggedIn()): ?>
                <?php if(isAdmin()): ?>
                    <a href="<?= SITE_URL ?>admin/dashboard.php" class="nav-link">Dashboard</a>
                    <a href="<?= SITE_URL ?>admin/usuarios.php" class="nav-link">Usuarios</a>
                    <a href="<?= SITE_URL ?>admin/suscripciones.php" class="nav-link">Suscripciones</a>
                    <a href="<?= SITE_URL ?>admin/tickets.php" class="nav-link">Tickets</a>
                    <a href="<?= SITE_URL ?>admin/eventos.php" class="nav-link">Eventos</a>
                    <a href="<?= SITE_URL ?>admin/anuncios.php" class="nav-link">Anuncios</a>
                <?php else: ?>
                    <a href="<?= SITE_URL ?>usuario/perfil.php" class="nav-link">Mi Perfil</a>
                    <a href="<?= SITE_URL ?>usuario/bandeja.php" class="nav-link relative">
                        Bandeja
                        <?php if($notificaciones_no_leidas > 0): ?>
                            <span class="absolute -top-2 -right-3 bg-red-500 text-white text-xs font-bold w-5 h-5 flex items-center justify-center rounded-full"><?= $notificaciones_no_leidas ?></span>
                        <?php endif; ?>
                    </a>
                    <a href="<?= SITE_URL ?>planes.php" class="nav-link">Planes</a>
                    <a href="<?= SITE_URL ?>ticket.php" class="nav-link">Mis Tickets</a>
                <?php endif; ?>
                
                <a href="<?= SITE_URL ?>stream.php" class="nav-link font-bold text-purple-400 hover:text-purple-300">🟣 STREAM EN VIVO</a>
                <a href="<?= SITE_URL ?>procesar/logout.php" class="nav-link font-bold hover:text-red-500 transition-colors duration-300">Cerrar Sesión</a>

            <?php else: ?>
                <a href="<?= SITE_URL ?>login.php" class="nav-link">Iniciar Sesión</a>
                <a href="<?= SITE_URL ?>registro.php" class="bg-purple-600 hover:bg-purple-700 text-white font-bold py-2 px-4 rounded">Registrarse</a>
            <?php endif; ?>
        </div>

        <div class="md:hidden flex items-center">
             <button id="mobile-menu-button" class="text-white focus:outline-none">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16m-7 6h7"></path></svg>
            </button>
        </div>
    </div>

    <div id="mobile-menu" class="hidden md:hidden">
        <div class="px-2 pt-2 pb-3 space-y-1 sm:px-3 flex flex-col">
            <a href="<?= SITE_URL ?>comunidad.php" class="nav-link-mobile">Comunidad</a>
            <?php if(isLoggedIn()): ?>
                 <?php if(isAdmin()): ?>
                    <a href="<?= SITE_URL ?>admin/dashboard.php" class="nav-link-mobile">Dashboard</a>
                    <a href="<?= SITE_URL ?>admin/usuarios.php" class="nav-link-mobile">Usuarios</a>
                    <a href="<?= SITE_URL ?>admin/suscripciones.php" class="nav-link-mobile">Suscripciones</a>
                    <a href="<?= SITE_URL ?>admin/tickets.php" class="nav-link-mobile">Tickets</a>
                    <a href="<?= SITE_URL ?>admin/eventos.php" class="nav-link-mobile">Eventos</a>
                    <a href="<?= SITE_URL ?>admin/anuncios.php" class="nav-link-mobile">Anuncios</a>
                <?php else: ?>
                    <a href="<?= SITE_URL ?>usuario/perfil.php" class="nav-link-mobile">Mi Perfil</a>
                    <a href="<?= SITE_URL ?>usuario/bandeja.php" class="nav-link-mobile">Bandeja de Entrada</a>
                    <a href="<?= SITE_URL ?>planes.php" class="nav-link-mobile">Planes</a>
                    <a href="<?= SITE_URL ?>ticket.php" class="nav-link-mobile">Mis Tickets</a>
                <?php endif; ?>
                <a href="<?= SITE_URL ?>stream.php" class="nav-link-mobile font-bold text-purple-400">🟣 STREAM EN VIVO</a>
                <a href="<?= SITE_URL ?>procesar/logout.php" class="nav-link-mobile font-bold text-red-400">Cerrar Sesión</a>
            <?php else: ?>
                <a href="<?= SITE_URL ?>login.php" class="nav-link-mobile">Iniciar Sesión</a>
                <a href="<?= SITE_URL ?>registro.php" class="bg-purple-600 hover:bg-purple-700 text-white font-bold py-2 px-4 rounded mt-2 text-center">Registrarse</a>
            <?php endif; ?>
        </div>
    </div>
</nav>

<script>
    document.getElementById('mobile-menu-button').addEventListener('click', function() {
        var menu = document.getElementById('mobile-menu');
        if (menu.classList.contains('hidden')) {
            menu.classList.remove('hidden');
        } else {
            menu.classList.add('hidden');
        }
    });
</script>

<style>
    .nav-link {
        @apply text-white hover:text-purple-400 transition-colors duration-300;
    }
    .nav-link-mobile {
        @apply block px-3 py-2 rounded-md text-base font-medium text-white hover:bg-gray-700;
    }
</style>