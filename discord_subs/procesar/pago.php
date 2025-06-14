<?php
session_start();
require_once '../includes/config.php';
require_once '../includes/auth.php';
require_once '../includes/functions.php';

requireLogin();

if($_SERVER['REQUEST_METHOD'] === 'POST') {
    $tipo = $_POST['tipo'];
    $monto = $_POST['monto'];
    $usuario_id = $_SESSION['usuario_id'];
    
    // Calcular fechas
    $fecha_inicio = date('Y-m-d H:i:s');
    $fecha_termino = calcularFechaTermino($tipo);
    
    $db = new Database();
    $conn = $db->connect();
    
    // Crear suscripción pendiente
    $query = "INSERT INTO suscripciones (usuario_id, tipo, monto, fecha_inicio, fecha_termino, estado, metodo_pago) 
              VALUES (:usuario_id, :tipo, :monto, :fecha_inicio, :fecha_termino, 'pendiente', 'transferencia')";
    
    $stmt = $conn->prepare($query);
    $stmt->bindParam(':usuario_id', $usuario_id);
    $stmt->bindParam(':tipo', $tipo);
    $stmt->bindParam(':monto', $monto);
    $stmt->bindParam(':fecha_inicio', $fecha_inicio);
    $stmt->bindParam(':fecha_termino', $fecha_termino);
    
    if($stmt->execute()) {
        $suscripcion_id = $conn->lastInsertId();
        
        // Registrar actividad
        registrarActividad($usuario_id, 'Solicitud de suscripción', "Tipo: $tipo, Monto: $monto CLP");
        
        //