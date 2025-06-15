<?php
require_once 'includes/config.php';
require_once 'includes/database.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$token = $_GET['token'] ?? '';
if (empty($token)) {
    $_SESSION['error'] = "Token inválido o no proporcionado.";
    header('Location: login.php');
    exit();
}

// Validar token en la base de datos
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
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Restablecer Contraseña - Discord Subs</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
    <link rel="stylesheet" href="assets/css/style.css">
</head>
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
                    <label for="password" class="block text-sm font-medium text-gray-300">Nueva Contraseña</label>
                    <input type="password" id="password" name="password" required class="mt-1 block w-full form-input">
                </div>
                <div>
                    <label for="confirm_password" class="block text-sm font-medium text-gray-300">Confirmar Nueva Contraseña</label>
                    <input type="password" id="confirm_password" name="confirm_password" required class="mt-1 block w-full form-input">
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