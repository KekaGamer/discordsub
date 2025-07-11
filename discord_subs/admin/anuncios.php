<?php
// admin/anuncios.php (CORREGIDO PARA VISTA MÓVIL)
require_once '../includes/config.php';
require_once '../includes/auth.php';
require_once '../includes/database.php';
requireAdmin();

$db = new Database();
$conn = $db->connect();
$anuncios = $conn->query("SELECT * FROM anuncios ORDER BY fecha_creacion DESC")->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestionar Anuncios - KekaGamer</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body class="bg-gray-900 text-white">
    <?php include '../includes/navbar.php'; ?>
    <div class="container mx-auto px-4 py-8">
        <h1 class="text-3xl font-bold text-purple-400 mb-6">Gestionar Anuncios de la Comunidad</h1>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
            <div>
                <h2 class="text-2xl font-bold mb-4">📢 Crear Nuevo Anuncio</h2>
                <form action="../procesar/admin_anuncios.php" method="POST" class="bg-gray-800 p-6 rounded-lg space-y-4">
                    <div>
                        <label for="titulo" class="block text-sm font-medium">Título del Anuncio</label>
                        <input type="text" name="titulo" required class="w-full bg-gray-700 p-2 rounded mt-1 text-white">
                    </div>
                    <div>
                        <label for="mensaje" class="block text-sm font-medium">Mensaje</label>
                        <textarea name="mensaje" rows="4" required class="w-full bg-gray-700 p-2 rounded mt-1 text-white"></textarea>
                    </div>
                    <button type="submit" name="accion" value="crear" class="bg-purple-600 hover:bg-purple-700 text-white font-bold py-2 px-4 rounded">Publicar Anuncio</button>
                </form>
            </div>
            <div>
                <h2 class="text-2xl font-bold mb-4">Anuncios Publicados</h2>
                <div class="bg-gray-800 p-6 rounded-lg space-y-4 max-h-96 overflow-y-auto">
                    <?php if (empty($anuncios)): ?>
                        <p class="text-gray-400">No hay anuncios publicados.</p>
                    <?php else: ?>
                        <?php foreach($anuncios as $anuncio): ?>
                            <div class="border-b border-gray-700 pb-2">
                                <p class="font-bold"><?= htmlspecialchars($anuncio['titulo']) ?></p>
                                <p class="text-sm text-gray-400"><?= htmlspecialchars($anuncio['mensaje']) ?></p>
                                <p class="text-xs text-gray-500 mt-1"><?= date('d/m/Y', strtotime($anuncio['fecha_creacion'])) ?></p>
                                <form action="../procesar/admin_anuncios.php" method="POST" class="text-right">
                                    <input type="hidden" name="id" value="<?= $anuncio['id'] ?>">
                                    <button type="submit" name="accion" value="eliminar" class="text-red-500 hover:underline text-xs">Eliminar</button>
                                </form>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</body>
</html>