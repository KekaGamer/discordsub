<?php
// Nueva contraseña temporal
$nueva_password = 'admin12345';

// Generar el hash encriptado
$hash_encriptado = password_hash($nueva_password, PASSWORD_DEFAULT);

// Mostrar el hash
echo 'Copia este código: <br><br>';
echo '<textarea rows="3" cols="80" readonly>' . $hash_encriptado . '</textarea>';
echo '<br><br>La nueva contraseña para el usuario "admin" será: <strong>' . $nueva_password . '</strong>';
?>