<?php
// admin/ver_ticket.php (VERSIÓN CORREGIDA Y COMPLETA)
require_once '../includes/config.php';
require_once '../includes/auth.php';
require_once '../includes/database.php';
require_once '../includes/functions.php';
requireAdmin();

$ticket_id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
if (!$ticket_id) {
    header('Location: tickets.php');
    exit();
}

$db = new Database();
$conn = $db->connect();

// Obtener la información principal del ticket y los datos del usuario que lo creó
$stmt_ticket = $conn->prepare("SELECT t.*, u.discord, u.correo FROM tickets t JOIN usuarios u ON t.usuario_id = u.id WHERE t.id = :id");
$stmt_ticket->execute([':id' => $ticket_id]);
$ticket = $stmt_ticket->fetch(PDO::FETCH_ASSOC);

if (!$ticket) {
    $_SESSION['error'] = "Ticket no encontrado.";
    header('Location: tickets.php');
    exit();
}

// Obtener solo las respuestas de la tabla de respuestas
$stmt_respuestas = $conn->prepare(
    "SELECT r.usuario_id, r.mensaje, r.fecha_creacion, u.discord, u.rol 
     FROM ticket_respuestas r 
     JOIN usuarios u ON r.usuario_id = u.id 
     WHERE r.ticket_id = :id 
     ORDER BY r.fecha_creacion ASC"
);
$stmt_respuestas->execute([':id' => $ticket_id]);
$respuestas = $stmt_respuestas->fetchAll(PDO::FETCH_ASSOC);

?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Viendo Ticket #<?= $ticket['id'] ?> - KekaGamer</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body class="bg-gray-900 text-white">
    <?php include '../includes/navbar.php'; ?>
    <div class="container mx-auto px-4 py-8">
        <a href="tickets.php" class="text-purple-400 hover:underline mb-4 inline-block">&larr; Volver a todos los tickets</a>
        <h1 class="text-3xl font-bold text-purple-400 mb-2">Ticket #<?= $ticket['id'] ?>: <?= htmlspecialchars($ticket['asunto']) ?></h1>
        <p class="text-gray-400">Abierto por: <span class="font-semibold"><?= htmlspecialchars($ticket['discord']) ?></span> (<?= htmlspecialchars($ticket['correo']) ?>)</p>
        
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8 mt-6">
            <div class="lg:col-span-2">
                <h2 class="text-2xl font-bold mb-4">Conversación</h2>
                <div class="space-y-6">
                    <div class="p-4 rounded-lg bg-gray-800 border border-purple-500">
                        <p class="text-sm font-bold text-green-400">
                            <?= htmlspecialchars($ticket['discord']) ?> (Cliente)
                            <span class="text-xs text-gray-500 font-normal ml-2"><?= date('d/m/Y H:i', strtotime($ticket['fecha_creacion'])) ?></span>
                        </p>
                        <p class="mt-2 whitespace-pre-wrap"><?= htmlspecialchars($ticket['mensaje']) ?></p>
                        <?php if ($ticket['adjunto']): ?>
                            <div class="mt-4 pt-4 border-t border-gray-700">
                                <span class="font-semibold text-sm">Archivo Adjunto:</span>
                                <a href="../uploads/tickets/<?= htmlspecialchars($ticket['adjunto']) ?>" target="_blank" class="text-blue-400 hover:underline ml-2">
                                    <?= htmlspecialchars($ticket['adjunto']) ?>
                                </a>
                            </div>
                        <?php endif; ?>
                    </div>
                    
                    <?php foreach ($respuestas as $msg): ?>
                        <div class="p-4 rounded-lg <?= ($msg['rol'] == 'admin') ? 'bg-gray-700' : 'bg-gray-800' ?>">
                            <p class="text-sm font-bold <?= ($msg['rol'] == 'admin') ? 'text-purple-400' : 'text-green-400' ?>">
                                <?= ($msg['rol'] == 'admin') ? htmlspecialchars($msg['discord']) . ' (Soporte)' : htmlspecialchars($msg['discord']) . ' (Cliente)' ?>
                                <span class="text-xs text-gray-500 font-normal ml-2"><?= date('d/m/Y H:i', strtotime($msg['fecha_creacion'])) ?></span>
                            </p>
                            <p class="mt-2 whitespace-pre-wrap"><?= htmlspecialchars($msg['mensaje']) ?></p>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
            
            <div class="lg:col-span-1">
                <form action="../procesar/admin_ticket.php" method="POST" class="bg-gray-800 p-6 rounded-lg sticky top-24 shadow-lg">
                    <input type="hidden" name="ticket_id" value="<?= $ticket['id'] ?>">
                    <h3 class="text-xl font-bold mb-4">Gestionar Ticket</h3>
                    
                    <div class="space-y-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-300">Estado</label>
                            <select name="estado" class="w-full bg-gray-700 p-2 rounded mt-1 text-white border border-gray-600">
                                <option <?= $ticket['estado'] == 'Abierto' ? 'selected' : '' ?>>Abierto</option>
                                <option <?= $ticket['estado'] == 'En Proceso' ? 'selected' : '' ?>>En Proceso</option>
                                <option <?= $ticket['estado'] == 'Cerrado' ? 'selected' : '' ?>>Cerrado</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-300">Prioridad</label>
                            <select name="prioridad" class="w-full bg-gray-700 p-2 rounded mt-1 text-white border border-gray-600">
                                <option <?= $ticket['prioridad'] == 'Baja' ? 'selected' : '' ?>>Baja</option>
                                <option <?= $ticket['prioridad'] == 'Normal' ? 'selected' : '' ?>>Normal</option>
                                <option <?= $ticket['prioridad'] == 'Alta' ? 'selected' : '' ?>>Alta</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-300">Asignar a Grupo</label>
                            <select name="grupo_asignado" class="w-full bg-gray-700 p-2 rounded mt-1 text-white border border-gray-600">
                                <option <?= $ticket['grupo_asignado'] == 'Soporte General' ? 'selected' : '' ?>>Soporte General</option>
                                <option <?= $ticket['grupo_asignado'] == 'Base de Datos' ? 'selected' : '' ?>>Base de Datos</option>
                                <option <?= $ticket['grupo_asignado'] == 'Finanzas' ? 'selected' : '' ?>>Finanzas</option>
                            </select>
                        </div>
                    </div>

                    <hr class="my-6 border-gray-700">

                    <div>
                        <label class="block text-sm font-medium text-gray-300 mb-2">Añadir Respuesta</label>
                        <textarea name="mensaje" rows="5" placeholder="Escribe una respuesta pública para el usuario..." class="w-full bg-gray-700 p-2 rounded text-white border border-gray-600"></textarea>
                    </div>

                    <button type="submit" name="actualizar_ticket" class="w-full mt-6 bg-purple-600 hover:bg-purple-700 text-white font-bold py-2 px-4 rounded">
                        Enviar Respuesta y Guardar Cambios
                    </button>
                </form>
            </div>
        </div>
    </div>
</body>
</html>