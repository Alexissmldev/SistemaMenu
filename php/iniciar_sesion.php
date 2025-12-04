<?php

# 1. Almacenar y limpiar datos #
$usuario = limpiar_cadena($_POST['login_usuario']);
$clave   = limpiar_cadena($_POST['login_clave']);

# 2. Verificando campos obligatorios #
if ($usuario == "" || $clave == "") {
    echo '
    <script>
        Swal.fire({
            icon: "error",
            title: "¡Ocurrió un error!",
            text: "No has llenado todos los campos que son obligatorios",
            confirmButtonText: "Aceptar",
            confirmButtonColor: "#f97316"
        });
    </script>';
    exit();
}

# 3. Verificando integridad de los datos #
if (verificar_datos("[a-zA-Z0-9]{4,20}", $usuario)) {
    echo '
    <script>
        Swal.fire({
            icon: "error",
            title: "Formato inválido",
            text: "El USUARIO no coincide con el formato solicitado (4 a 20 caracteres alfanuméricos)",
            confirmButtonText: "Aceptar",
            confirmButtonColor: "#f97316"
        });
    </script>';
    exit();
}

if (verificar_datos("[a-zA-Z0-9$@.-]{7,100}", $clave)) {
    echo '
    <script>
        Swal.fire({
            icon: "error",
            title: "Formato inválido",
            text: "La CLAVE no coincide con el formato solicitado",
            confirmButtonText: "Aceptar",
            confirmButtonColor: "#f97316"
        });
    </script>';
    exit();
}

# --- INICIO DE LA LÓGICA DE LOGIN --- #

$conexion = conexion();

# 4. Verificar usuario en la BD #
$check_user = $conexion->prepare("SELECT * FROM usuario WHERE usuario_usuario = :usuario");
$check_user->execute([':usuario' => $usuario]);

if ($check_user->rowCount() == 1) {

    $datos_usuario = $check_user->fetch(PDO::FETCH_ASSOC);

    # 5. Verificar Password #
    if ($datos_usuario['usuario_usuario'] == $usuario && password_verify($clave, $datos_usuario['usuario_clave'])) {

        // --- INICIO DE SESIÓN EXITOSO --- //

        $_SESSION['id']        = $datos_usuario['usuario_id'];
        $_SESSION['nombre']    = $datos_usuario['usuario_nombre'];
        $_SESSION['apellido']  = $datos_usuario['usuario_apellido'];
        $_SESSION['usuario']   = $datos_usuario['usuario_usuario'];
        $_SESSION['email']     = $datos_usuario['usuario_email'];
        $_SESSION['tienda_id'] = $datos_usuario['id_tienda'];
        $_SESSION['rol_id']    = $datos_usuario['rol_id'];

        // --- 6. CONSULTA DE PERMISOS --- //
        try {
            $sql_permisos = "SELECT p.permiso_clave 
                             FROM permisos p
                             INNER JOIN permiso_rol pr ON p.permiso_id = pr.permiso_id
                             WHERE pr.rol_id = :rol_id";

            $stmt = $conexion->prepare($sql_permisos);
            $stmt->execute([':rol_id' => $datos_usuario['rol_id']]);

            $permisos = $stmt->fetchAll(PDO::FETCH_COLUMN);
            $_SESSION['permisos'] = $permisos;
        } catch (Exception $e) {
            $_SESSION['permisos'] = [];
        }

        // --- 7. REDIRECCIÓN --- //
        $vista_destino = "index.php?vista=home";

        // Redirección inteligente basada en rol/permisos
        if (in_array('pedidos.preparar', $_SESSION['permisos']) && !in_array('inventario.gestionar', $_SESSION['permisos'])) {
            $vista_destino = "orders_kanban";
        } elseif (in_array('pedidos.entregar', $_SESSION['permisos']) && !in_array('inventario.gestionar', $_SESSION['permisos'])) {
            $vista_destino = "index.php?vista=orders_kanban";
        }

        if (headers_sent()) {
            echo "<script> window.location.href='$vista_destino';</script>";
        } else {
            header("Location: $vista_destino");
        }
    } else {
        // ERROR: CLAVE INCORRECTA
        echo '
        <script>
            Swal.fire({
                icon: "error",
                title: "Acceso denegado",
                text: "Usuario o clave incorrectos",
                confirmButtonText: "Intentar de nuevo",
                confirmButtonColor: "#d33"
            });
        </script>';
    }
} else {
    // ERROR: USUARIO NO EXISTE
    echo '
    <script>
        Swal.fire({
            icon: "error",
            title: "Acceso denegado",
            text: "Usuario o clave incorrectos",
            confirmButtonText: "Intentar de nuevo",
            confirmButtonColor: "#d33"
        });
    </script>';
}

$conexion = null;
