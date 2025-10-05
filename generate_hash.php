<?php
// Este script genera un hash seguro para la contraseña 'clave123'
$password = 'clave123';
$hash = password_hash($password, PASSWORD_DEFAULT);
echo "Copia y pega este hash en la columna 'password' de tu usuario en la base de datos:<br><br>";
echo "<strong>" . htmlspecialchars($hash) . "</strong>";
?>