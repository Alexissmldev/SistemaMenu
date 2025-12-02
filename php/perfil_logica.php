<?php
/* ARCHIVO: php/perfil_logica.php
   FUNCIÓN: Solo obtiene los datos del usuario actual para mostrarlos en el HTML.
   NO procesa guardado.
*/

// session_start() NO ES NECESARIO AQUÍ SI YA ESTÁ EN index.php
// Pero validamos por seguridad:
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$id_usuario = $_SESSION['id'];
$conexion = conexion();

// Consultamos los datos actuales del usuario en la BD
$datos_usuario = $conexion->query("SELECT * FROM usuario WHERE usuario_id = '$id_usuario'");

if ($datos_usuario->rowCount() > 0) {
    $usuario_actual = $datos_usuario->fetch();
} else {
    // Si no existe, logout
    include "./php/logout.php";
    exit();
}

$conexion = null;
