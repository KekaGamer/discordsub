<<?php
require_once 'includes/config.php'; // Asegura que la configuración se cargue primero
require_once 'includes/database.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// ... (resto de la lógica PHP no cambia) ...
$token = $_GET['token'] ?? '';
if (empty($token)) {
    $_SESSION['error'] = "Token inválido o no proporcionado.";
    header('Location: login.php');
    exit();
}
try {
    $db = new Database();
    $conn = $db->connect();
    $query = "SELECT * FROM password_resets WHERE token = :token AND fecha_expiracion > NOW()";
    $stmt = $conn->prepare($query);
    $stmt->bindParam(':token', $token, PDO::PARAM_STR);
    $stmt->execute();
    $reset_request = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$reset_request) {
        $_SESSION['error'] = "El token de recuperación es inválido o ha expirado.";
        header('Location: olvido_password.php');
        exit();
    }
} catch (Exception $e) {
    $_SESSION['error'] = "Error del sistema. Inténtalo de nuevo.";
    header('Location: olvido_password.php');
    exit();
}

$pageTitle = "Restablecer Contraseña";
include 'includes/header.php';
?>
<body class="bg-gray-900 text-white">
    <div class="min-h-screen flex items-center justify-center">
        <div class="w-full max-w-md bg-gray-800 rounded-lg shadow-lg p-8">
            <div class="text-center mb-8">
                <h1 class="text-3xl font-bold text-purple-500">Restablecer Contraseña</h1>
                <p class="text-gray-400">Ingresa tu nueva contraseña.</p>
            </div>

            <?php if(isset($_SESSION['error'])): ?>
                <div class="bg-red-500 text-white p-3 rounded mb-4 text-center"><?= $_SESSION['error']; unset($_SESSION['error']); ?></div>
            <?php endif; ?>
            
            <form id="form-registro" action="procesar/recuperar.php" method="POST" class="space-y-6">
                <input type="hidden" name="token" value="<?= htmlspecialchars($token) ?>">
                <input type="hidden" name="action" value="reset">
                
                <div>
                    <label for="password" class="block text-sm font-medium text-gray-300 mb-1">Nueva Contraseña</label>
                    <input type="password" id="password" name="password" required class="mt-1 block w-full px-4 py-2 bg-gray-700 border border-gray-600 rounded-md focus:ring-purple-500 focus:border-purple-500 text-white">
                </div>
                <div>
                    <label for="confirm_password" class="block text-sm font-medium text-gray-300 mb-1">Confirmar Nueva Contraseña</label>
                    <input type="password" id="confirm_password" name="confirm_password" required class="mt-1 block w-full px-4 py-2 bg-gray-700 border border-gray-600 rounded-md focus:ring-purple-500 focus:border-purple-500 text-white">
                </div>
                
                <div>
                    <button type="submit" class="w-full bg-purple-600 hover:bg-purple-700 text-white font-bold py-2 px-4 rounded transition duration-200">
                        Guardar Contraseña
                    </button>
                </div>
            </form>
        </div>
    </div>
    <script src="assets/js/script.js"></script>
</body>
</html>