<nav class="navbar border-b border-gray-800 sticky top-0 z-40">
    <div class="container mx-auto px-4 py-3 flex justify-between items-center">
        <a href="<?= SITE_URL ?>index.php" class="navbar-brand text-2xl">Discord Subs</a>
        
        <div class="hidden md:flex items-center space-x-6">
            <?php if(isLoggedIn()): ?>
                <?php if(isAdmin()): ?>
                    <a href="<?= SITE_URL ?>admin/dashboard.php" class="nav-link">Dashboard</a>
                    <a href="<?= SITE_URL ?>admin/usuarios.php" class="nav-link">Usuarios</a>
                    <a href="<?= SITE_URL ?>admin/suscripciones.php" class="nav-link">Suscripciones</a>
                    <a href="<?= SITE_URL ?>admin/eventos.php" class="nav-link">Eventos</a>
                <?php else: ?>
                    <a href="<?= SITE_URL ?>usuario/perfil.php" class="nav-link">Mi Perfil</a>
                    <a href="<?= SITE_URL ?>usuario/historial.php" class="nav-link">Mi Historial</a>
                    <a href="<?= SITE_URL ?>planes.php" class="nav-link">Planes</a>
                <?php endif; ?>
                
                <a href="<?= SITE_URL ?>stream.php" class="nav-link">Stream</a>
                <a href="<?= SITE_URL ?>procesar/logout.php" class="nav-link bg-red-600 hover:bg-red-700 px-3 py-1 rounded-md">Cerrar Sesión</a>
            <?php else: ?>
                <a href="<?= SITE_URL ?>login.php" class="nav-link">Iniciar Sesión</a>
                <a href="<?= SITE_URL ?>registro.php" class="bg-purple-600 hover:bg-purple-700 text-white font-bold py-2 px-4 rounded">
                    Registrarse
                </a>
            <?php endif; ?>
        </div>
        </div>
</nav>