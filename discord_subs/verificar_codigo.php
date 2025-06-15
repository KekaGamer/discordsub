<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
$correo_usuario = $_GET['email'] ?? '';
if (empty($correo_usuario)) {
    header('Location: olvido_password.php');
    exit();
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Verificar Código - Discord Subs</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body class="bg-gray-900 text-white">
    <div class="min-h-screen flex items-center justify-center">
        <div class="w-full max-w-md bg-gray-800 rounded-lg shadow-lg p-8">
            <div class="text-center mb-8">
                <h1 class="text-3xl font-bold text-purple-500">Verificar Código</h1>
                <p class="text-gray-400">Ingresa el código que enviamos a tu teléfono.</p>
            </div>

            <?php if(isset($_SESSION['error'])): ?>
                <div class="bg-red-500 text-white p-3 rounded mb-4 text-center"><?= $_SESSION['error']; unset($_SESSION['error']); ?></div>
            <?php endif; ?>
            
            <form action="procesar/recuperar.php" method="POST" class="space-y-6">
                <input type="hidden" name="action" value="verify_code">
                <input type="hidden" name="correo" value="<?= htmlspecialchars($correo_usuario) ?>">
                <div>
                    <label for="codigo" class="block text-sm font-medium text-gray-300 mb-1">Código de 6 dígitos</label>
                    <input type="text" id="codigo" name="codigo" required maxlength="6" class="mt-1 block w-full px-4 py-2 bg-gray-700 border border-gray-600 rounded-md focus:ring-purple-500 focus:border-purple-500 text-white text-center text-2xl tracking-widest">
                </div>
                
                <div>
                    <button type="submit" class="w-full bg-purple-600 hover:bg-purple-700 text-white font-bold py-2 px-4 rounded transition duration-200">
                        Verificar y Continuar
                    </button>
                </div>
            </form>
        </div>
    </div>
</body>
</html>