<?php
require_once "main.php";

/*-- Recibir ID de la tienda --*/
$id = limpiar_cadena($_POST['id_tienda']);

/*-- Verificando tienda en BD --*/
$check_tienda = conexion();
$check_tienda = $check_tienda->query("SELECT * FROM tiendas WHERE id_tienda='$id'");

if($check_tienda->rowCount()<=0){
    echo '
        <div class="notification is-danger is-light">
            <strong>¡Ocurrió un error inesperado!</strong><br>
            La tienda no existe en el sistema.
        </div>';
    exit();
} else {
    $datos_tienda = $check_tienda->fetch();
}
$check_tienda = null;

/*-- Recibir Datos del Formulario --*/
$nombre = limpiar_cadena($_POST['nombre_tienda']);
$rif = limpiar_cadena($_POST['rif_tienda']);
$telefono = limpiar_cadena($_POST['telefono_tienda']);
$direccion = limpiar_cadena($_POST['direccion_tienda']);
$moneda = limpiar_cadena($_POST['moneda_simbolo']);
$color = limpiar_cadena($_POST['color_principal']);

// Datos Pago Móvil
$pm_banco = limpiar_cadena($_POST['pm_banco']);
$pm_tel = limpiar_cadena($_POST['pm_tel']);
$pm_ced = limpiar_cadena($_POST['pm_ced']);

/*-- Validaciones Básicas --*/
if($nombre == ""){
    echo '
        <div class="notification is-danger is-light">
            <strong>¡Ocurrió un error!</strong><br>
            El nombre de la tienda es obligatorio.
        </div>';
    exit();
}

/*-- Preparar Datos para Actualizar --*/
$datos_update = [
    ":nombre" => $nombre,
    ":rif" => $rif,
    ":telefono" => $telefono,
    ":direccion" => $direccion,
    ":moneda" => $moneda,
    ":color" => $color,
    ":pm_banco" => $pm_banco,
    ":pm_tel" => $pm_tel,
    ":pm_ced" => $pm_ced,
    ":id" => $id
];

/*-- Procesar Logo (Imagen) --*/
$sql_logo_part = "";

if(isset($_FILES['logo_tienda']) && $_FILES['logo_tienda']['name'] != "" && $_FILES['logo_tienda']['size'] > 0){
    
    $img_dir = '../img/logo/';

    // Crear directorio si no existe
    if(!file_exists($img_dir)){
        if(!mkdir($img_dir, 0777)){
            echo '<div class="notification is-danger">Error al crear el directorio de logos.</div>';
            exit();
        }
    }

    // Validar formato
    if(mime_content_type($_FILES['logo_tienda']['tmp_name']) != "image/jpeg" && mime_content_type($_FILES['logo_tienda']['tmp_name']) != "image/png"){
        echo '<div class="notification is-danger">Formato de imagen no válido (Use JPG o PNG).</div>';
        exit();
    }

    // Validar peso (3MB máx)
    if(($_FILES['logo_tienda']['size'] / 1024) > 3072){
        echo '<div class="notification is-danger">La imagen es muy pesada (Máx 3MB).</div>';
        exit();
    }

    // Nombre único para la imagen
    $foto_nombre = "logo_" . $id . "_" . rand(0,100) . ".png";
    
    // Subir imagen
    if(!move_uploaded_file($_FILES['logo_tienda']['tmp_name'], $img_dir . $foto_nombre)){
        echo '<div class="notification is-danger">No se pudo cargar la imagen.</div>';
        exit();
    }

    // Borrar logo anterior si existe y no es el default
    if(is_file($img_dir . $datos_tienda['logo_tienda']) && $datos_tienda['logo_tienda'] != "logo_default.png"){
        unlink($img_dir . $datos_tienda['logo_tienda']);
    }

    $sql_logo_part = ", logo_tienda = :logo";
    $datos_update[':logo'] = $foto_nombre;
}

/*-- Ejecutar Update en BD --*/
$conexion = conexion();
$update = $conexion->prepare("UPDATE tiendas SET 
    nombre_tienda = :nombre,
    rif_tienda = :rif,
    telefono_tienda = :telefono,
    direccion_tienda = :direccion,
    moneda_simbolo = :moneda,
    color_principal = :color,
    pm_banco = :pm_banco,
    pm_telefono = :pm_tel,
    pm_cedula = :pm_ced
    $sql_logo_part
    WHERE id_tienda = :id");

if($update->execute($datos_update)){
    echo '
        <div class="notification is-info is-light">
            <strong>¡TIENDA ACTUALIZADA!</strong><br>
            Los datos se han guardado correctamente.
        </div>
        <script>
            setTimeout(function(){ 
                window.location.href="index.php?vista=user_update&tab=tienda"; 
            }, 1500);
        </script>
    ';
} else {
    echo '
        <div class="notification is-danger is-light">
            <strong>¡Ocurrió un error!</strong><br>
            No se pudo actualizar la base de datos.
        </div>';
}
$conexion = null;
?>