<?php
require_once 'config.php';
require_once 'auth.php';
?>
<nav class="navbar border-b border-gray-800">
    <div class="container mx-auto px-4 py-3 flex justify-between items-center">
        <a href="../index.php" class="navbar-brand text-2xl">Discord Subs</a>
        
        <div class="flex items-center space-x-6">
            <?php if(isLoggedIn()): ?>
                <?php if(isAdmin()): ?>
                    <a href="admin/dashboard.php" class="nav-link">Dashboard</a>
                <?php else: ?>
                    <a href="usuario/perfil.php" class="nav-link">Mi Perfil</a>
                <?php endif; ?>
                
                <?php
                $db = new Database();
                $conn = $db->connect();
                $query = "SELECT COUNT(*) as total FROM suscripciones 
                          WHERE usuario_id = :usuario_id 
                          AND estado = 'pendiente'";
                $stmt = $conn->prepare($query);
                $stmt->bindParam(':usuario_id', $_SESSION['usuario_id']);
                $stmt->execute();
                $pendientes = $stmt->fetch(PDO::FETCH_ASSOC)['total'];
                ?>
                
                <?php if($pendientes > 0): ?>
                    <a href="usuario/perfil.php" class="relative">
                        <span class="nav-link">Suscripciones</span>
                        <span class="absolute -top-2 -right-4 bg-red-500 text-white text-xs font-bold px-2 py-1 rounded-full">
                            <?= $pendientes ?>
                        </span>
                    </a>
                <?php endif; ?>
                
                <a href="../procesar/logout.php" class="nav-link">Cerrar Sesión</a>
            <?php else: ?>
                <a href="../login.php" class="nav-link">Iniciar Sesión</a>
                <a href="../registro.php" class="bg-purple-600 hover:bg-purple-700 text-white font-bold py-2 px-4 rounded">
                    Registrarse
                </a>
            <?php endif; ?>
        </div>
    </div>
</nav>