<?php
/*
|---------------------------------------------------------------
| ARCHIVO DE LÓGICA (EL "CEREBRO")
|---------------------------------------------------------------
| Este archivo:
| 1. Se conecta a la BD (PDO).
| 2. Revisa si el formulario fue enviado (POST).
| 3. Valida y actualiza los datos.
| 4. Obtiene los datos frescos del usuario (GET).
| 5. Prepara las variables ($msg_exito, $usuario_actual) para la vista.
*/

// 1. INCLUIR CONEXIÓN Y OBTENER ID
// ===================================
//
// Usamos require_once aquí. Asumimos que main.php está en la misma carpeta.
//
require_once "main.php"; 

$conexion = conexion();

// Obtenemos el ID del usuario de la sesión.
$user_id = $_SESSION['id'];

// Inicializamos variables para la vista
$msg_exito = "";
$msg_error = "";


// 2. MANEJAR EL ENVÍO DEL FORMULARIO (POST)
// ===================================
if ($_SERVER["REQUEST_METHOD"] == "POST") {

    /* Almacenar datos del formulario */
    $username = limpiar_cadena($_POST['username']);
    $phone = limpiar_cadena($_POST['phone']);
    
    $current_password = limpiar_cadena($_POST['current_password']);
    $new_password = limpiar_cadena($_POST['new_password']);
    $confirm_password = limpiar_cadena($_POST['confirm_password']);

    /* ===============================================================
      ¡IMPORTANTE! Revisa que estos nombres coincidan con tu BD
      ===============================================================
      Nombre de Tabla: 'usuario'
      Columna ID: 'usuario_id'
      Columna Usuario: 'usuario_usuario'
      Columna Teléfono: 'usuario_telefono' (Asegúrate que esta columna exista)
      Columna Clave: 'usuario_clave'
      ===============================================================
    */

    // --- A. Actualizar Información Personal (Sintaxis PDO) ---
    $stmt_info = $conexion->prepare("UPDATE usuario SET usuario_usuario = :user, usuario_telefono = :phone WHERE usuario_id = :id");
    
    $actualizado = $stmt_info->execute([
        ':user' => $username,
        ':phone' => $phone,
        ':id' => $user_id
    ]);

    if ($actualizado) {
        $msg_exito = "Información personal actualizada con éxito.";
        // Actualizamos el nombre de usuario en la sesión también
        $_SESSION['usuario'] = $username; 
    } else {
        $msg_error = "Error al actualizar la información personal. Inténtalo de nuevo.";
    }


    // --- B. Manejar Cambio de Contraseña (Sintaxis PDO) ---
    if (!empty($current_password) || !empty($new_password) || !empty($confirm_password)) {

        if (empty($current_password) || empty($new_password) || empty($confirm_password)) {
            $msg_error .= " <br>Para cambiar la contraseña, debes llenar los tres campos: actual, nueva y confirmación.";
        
        } elseif ($new_password !== $confirm_password) {
            $msg_error .= " <br>La nueva contraseña y su confirmación no coinciden.";
        
        } else {
            
            // B1. Obtener el hash actual de la BD (Sintaxis PDO)
            $stmt_check = $conexion->prepare("SELECT usuario_clave FROM usuario WHERE usuario_id = :id");
            $stmt_check->execute([':id' => $user_id]);
            $user_data = $stmt_check->fetch(PDO::FETCH_ASSOC);

            if ($user_data) {
                $current_hash_db = $user_data['usuario_clave'];

                // B2. Verificar que la "Contraseña Actual" coincida
                if (password_verify($current_password, $current_hash_db)) {
                    
                    // ¡Correcto! Hashear la *nueva* contraseña
                    $new_hash = password_hash($new_password, PASSWORD_BCRYPT, ["cost" => 10]);
                    
                    // B3. Actualizar la contraseña en la BD (Sintaxis PDO)
                    $stmt_update_pass = $conexion->prepare("UPDATE usuario SET usuario_clave = :pass WHERE usuario_id = :id");
                    
                    $pass_actualizada = $stmt_update_pass->execute([
                        ':pass' => $new_hash,
                        ':id' => $user_id
                    ]);
                    
                    if ($pass_actualizada) {
                        $msg_exito = "¡Perfil actualizado con éxito! (Contraseña cambiada)";
                    } else {
                        $msg_error .= " <br>Error al actualizar la contraseña.";
                    }

                } else {
                    $msg_error .= " <br>La 'Contraseña Actual' es incorrecta. No se pudo cambiar.";
                }
            } else {
                $msg_error .= " <br>Error al verificar el usuario. Contacte a soporte.";
            }
        }
    }
} // Fin del if ($_SERVER["REQUEST_METHOD"] == "POST")


// 3. OBTENER DATOS DEL USUARIO (GET)
// ===================================
// Esta lógica se ejecuta *siempre* (sea POST o no)
// para asegurar que el formulario tenga los datos más actuales.

$stmt_get = $conexion->prepare("SELECT usuario_usuario, usuario_telefono FROM usuario WHERE usuario_id = :id");
$stmt_get->execute([':id' => $user_id]);
$usuario_actual = $stmt_get->fetch(PDO::FETCH_ASSOC);

if (!$usuario_actual) {
    // Si falla, creamos un array vacío para evitar errores en el HTML
    $usuario_actual = ['usuario_usuario' => 'Error', 'usuario_telefono' => ''];
}

// Cerramos la conexión PDO asignando null
$conexion = null; 
$stmt_get = null; 

