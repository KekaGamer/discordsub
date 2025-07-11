<?php
// Ruta al archivo que queremos verificar
$rutaArchivo = 'procesar/admin_eventos.php';

echo "<h1>Verificando la codificación del archivo: " . $rutaArchivo . "</h1>";

if (file_exists($rutaArchivo)) {
    $contenido = file_get_contents($rutaArchivo);

    // Usamos una función de PHP para detectar la codificación
    $encoding = mb_detect_encoding($contenido, 'UTF-8, ISO-8859-1, GB18030', true);

    echo "<p style='font-size: 18px;'>PHP ha detectado que el archivo está en codificación: <strong style='color: green;'>" . $encoding . "</strong></p>";

} else {
    echo "<p style='color: red;'>Error: El archivo no se encontró en la ruta especificada.</p>";
}
?>