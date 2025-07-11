<?php
// ver_ticket_usuario.php (NUEVO ARCHIVO)
require_once 'includes/config.php';
require_once 'includes/auth.php';
require_once 'includes/database.php';
require_once 'includes/functions.php';
requireLogin();

$ticket_id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
if (!$ticket_id) {
    header('Location: ticket.php');
    exit();
}

$db = new Database();
$conn = $db->connect();
$usuario_id = $_SESSION['usuario_id'];

// Obtener info del ticket y verificar que pertenece al usuario logueado
$stmt_ticket = $conn->prepare("SELECT t.*, u.discord FROM tickets t JOIN usuarios u ON t.usuario_id = u.id WHERE t.id = :id AND t.usuario_id = :uid");
$stmt_ticket->execute([':id' => $ticket_id, ':uid' => $usuario_id]);
$ticket = $stmt_ticket->fetch(PDO::FETCH_ASSOC);

// Si el ticket no existe o no pertenece al usuario, lo redirigimos
if (!$ticket) {
    $_SESSION['error'] = "Acceso no permitido a este ticket.";
    header('Location: ticket.php');
    exit();
}

// Obtener todas las respuestas y el mensaje original
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
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body class="bg-gray-900 text-white">
    <?php include 'includes/navbar.php'; ?>
    <div class="container mx-auto px-4 py-8">
        <a href="ticket.php" class="text-purple-400 hover:underline mb-4 inline-block">&larr; Volver a Mis Tickets</a>
        <h1 class="text-3xl font-bold text-purple-400 mb-2">Ticket #<?= $ticket['id'] ?>: <?= htmlspecialchars($ticket['asunto']) ?></h1>
        <p class="text-gray-400">Estado: <span class="font-semibold"><?= htmlspecialchars($ticket['estado']) ?></span> | Prioridad: <span class="font-semibold"><?= htmlspecialchars($ticket['prioridad']) ?></span></p>

        <div class="mt-8">
            <h2 class="text-2xl font-bold mb-4">Conversación</h2>
            <div class="space-y-6">
                <div class="p-4 rounded-lg bg-gray-800 border border-purple-500">
                    <p class="text-sm font-bold text-green-400">
                        Tú
                        <span class="text-xs text-gray-500 font-normal ml-2"><?= date('d/m/Y H:i', strtotime($ticket['fecha_creacion'])) ?></span>
                    </p>
                    <p class="mt-2 whitespace-pre-wrap"><?= htmlspecialchars($ticket['mensaje']) ?></p>
                    <?php if ($ticket['adjunto']): ?>
                        <div class="mt-4 pt-4 border-t border-gray-700">
                            <span class="font-semibold text-sm">Archivo Adjunto:</span>
                            <a href="uploads/tickets/<?= htmlspecialchars($ticket['adjunto']) ?>" target="_blank" class="text-blue-400 hover:underline ml-2">
                                <?= htmlspecialchars($ticket['adjunto']) ?>
                            </a>
                        </div>
                    <?php endif; ?>
                </div>
                
                <?php foreach ($respuestas as $msg): ?>
                    <div class="p-4 rounded-lg <?= ($msg['rol'] == 'admin') ? 'bg-gray-700' : 'bg-gray-800' ?>">
                        <p class="text-sm font-bold <?= ($msg['rol'] == 'admin') ? 'text-purple-400' : 'text-green-400' ?>">
                            <?= ($msg['rol'] == 'admin') ? 'Soporte KekaGamer' : 'Tú' ?>
                            <span class="text-xs text-gray-500 font-normal ml-2"><?= date('d/m/Y H:i', strtotime($msg['fecha_creacion'])) ?></span>
                        </p>
                        <p class="mt-2 whitespace-pre-wrap"><?= htmlspecialchars($msg['mensaje']) ?></p>
                    </div>
                <?php endforeach; ?>
            </div>

            <hr class="my-8 border-gray-700">
            <form action="procesar/ticket.php" method="POST" class="bg-gray-800 p-6 rounded-lg">
                <input type="hidden" name="ticket_id" value="<?= $ticket['id'] ?>">
                <h3 class="text-xl font-bold mb-4">Añadir una Respuesta</h3>
                <textarea name="mensaje" rows="5" placeholder="Escribe tu respuesta aquí..." required class="w-full bg-gray-700 p-2 rounded text-white border border-gray-600 mb-4"></textarea>
                <button type="submit" name="responder_ticket" class="bg-purple-600 hover:bg-purple-700 text-white font-bold py-2 px-4 rounded">
                    Enviar Respuesta
                </button>
            </form>
        </div>
    </div>
</body>
</html>