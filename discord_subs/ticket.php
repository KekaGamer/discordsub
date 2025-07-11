<?php
// ticket.php (VERSIÓN FINAL, COMPLETA Y VERIFICADA)
require_once 'includes/config.php';
require_once 'includes/auth.php';
require_once 'includes/database.php';
require_once 'includes/functions.php';
requireLogin();

$db = new Database();
$conn = $db->connect();
$usuario_id = $_SESSION['usuario_id'];

// Obtener tickets del usuario
$stmt = $conn->prepare("SELECT * FROM tickets WHERE usuario_id = :usuario_id ORDER BY ultima_actualizacion DESC");
$stmt->execute([':usuario_id' => $usuario_id]);
$tickets = $stmt->fetchAll(PDO::FETCH_ASSOC);

function getEstadoColor($estado) {
    switch ($estado) {
        case 'Abierto': return 'bg-blue-500';
        case 'En Proceso': return 'bg-yellow-500';
        case 'Cerrado': return 'bg-gray-500';
        default: return 'bg-gray-500';
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Soporte por Tickets - KekaGamer</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body class="bg-gray-900 text-white">
    <?php include 'includes/navbar.php'; ?>
    <div class="container mx-auto px-4 py-8">
        <h1 class="text-3xl font-bold text-purple-400 mb-6">🎫 Mi Centro de Soporte</h1>
        
        <?php if(isset($_SESSION['error'])): ?>
            <div class="bg-red-500 text-white p-3 rounded mb-6 text-center"><?= $_SESSION['error']; unset($_SESSION['error']); ?></div>
        <?php endif; ?>
        <?php if(isset($_SESSION['success'])): ?>
            <div class="bg-green-500 text-white p-3 rounded mb-6 text-center"><?= $_SESSION['success']; unset($_SESSION['success']); ?></div>
        <?php endif; ?>

        <div class="bg-gray-800 p-6 rounded-lg shadow-lg mb-8">
            <h2 class="text-2xl font-bold mb-4">Crear Nuevo Ticket</h2>
            <form action="procesar/ticket.php" method="POST" enctype="multipart/form-data" class="space-y-4">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label for="asunto" class="block text-sm font-medium text-gray-300">Asunto</label>
                        <input type="text" name="asunto" id="asunto" required class="mt-1 block w-full bg-gray-700 border-gray-600 rounded-md p-2 text-white">
                    </div>
                    <div>
                        <label for="prioridad" class="block text-sm font-medium text-gray-300">Prioridad</label>
                        <select name="prioridad" id="prioridad" class="mt-1 block w-full bg-gray-700 border-gray-600 rounded-md p-2 text-white">
                            <option>Normal</option>
                            <option>Alta</option>
                            <option>Baja</option>
                        </select>
                    </div>
                </div>
                <div>
                    <label for="mensaje" class="block text-sm font-medium text-gray-300">Describe tu problema o consulta</label>
                    <textarea name="mensaje" id="mensaje" rows="5" required class="mt-1 block w-full bg-gray-700 border-gray-600 rounded-md p-2 text-white"></textarea>
                </div>
                <div>
                    <label for="adjunto" class="block text-sm font-medium text-gray-300">Adjuntar Archivo (Opcional, max 2MB: jpg, png, pdf)</label>
                    <input type="file" name="adjunto" id="adjunto" class="mt-1 block w-full text-gray-400 file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-sm file:font-semibold file:bg-purple-500 file:text-white hover:file:bg-purple-600">
                </div>
                <button type="submit" name="crear_ticket" class="bg-purple-600 hover:bg-purple-700 text-white font-bold py-2 px-4 rounded">Enviar Ticket</button>
            </form>
        </div>
        
        <div class="bg-gray-800 p-6 rounded-lg shadow-lg">
            <h2 class="text-2xl font-bold mb-4">Mis Tickets</h2>
            <div class="overflow-x-auto">
                <table class="min-w-full">
                    <thead class="bg-gray-700">
                        <tr>
                            <th class="px-4 py-2 text-left text-xs font-medium text-gray-300 uppercase">ID</th>
                            <th class="px-4 py-2 text-left text-xs font-medium text-gray-300 uppercase">Asunto</th>
                            <th class="px-4 py-2 text-left text-xs font-medium text-gray-300 uppercase">Estado</th>
                            <th class="px-4 py-2 text-left text-xs font-medium text-gray-300 uppercase">Última Actualización</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-700">
                        <?php if(!empty($tickets)): ?>
                            <?php foreach($tickets as $ticket): ?>
                                <tr>
                                    <td class="px-4 py-2">#<?= $ticket['id'] ?></td>
                                    <td class="px-4 py-2 font-semibold">
                                        <a href="ver_ticket_usuario.php?id=<?= $ticket['id'] ?>" class="text-purple-400 hover:underline">
                                            <?= htmlspecialchars($ticket['asunto']) ?>
                                        </a>
                                    </td>
                                    <td class="px-4 py-2"><span class="px-2 py-1 text-xs font-semibold rounded-full <?= getEstadoColor($ticket['estado']) ?> text-white"><?= htmlspecialchars($ticket['estado']) ?></span></td>
                                    <td class="px-4 py-2"><?= date('d/m/Y H:i', strtotime($ticket['ultima_actualizacion'])) ?></td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr><td colspan="4" class="text-center py-4 text-gray-400">No tienes tickets creados.</td></tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</body>
</html>