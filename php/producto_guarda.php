<?php
require_once "../inc/session_start.php";
require_once "main.php"; // Asegúrate que 'main.php' contenga las nuevas funciones de imagen

/*== Almacenando datos ==*/
$nombre     = limpiar_cadena($_POST['producto_nombre']);
$precio     = limpiar_cadena($_POST['producto_precio']);
$categoria  = limpiar_cadena($_POST['producto_categoria']);
$descripcion= limpiar_cadena($_POST['producto_descripcion']);

/*== Verificando campos obligatorios ==*/
if( $nombre=="" || $precio=="" || $categoria=="" || $descripcion=="") {
    enviar_respuesta_json("error","Campos vacíos","No has llenado todos los campos obligatorios");
}

/*== Verificando integridad de los datos ==*/
if(verificar_datos("[a-zA-Z0-9áéíóúÁÉÍÓÚñÑ().,$#\-\/ ]{1,70}",$nombre)){
    enviar_respuesta_json("error","Formato inválido","El nombre no cumple el formato requerido");
}
if(verificar_datos("[0-9.]{1,25}",$precio)){
    enviar_respuesta_json("error","Formato inválido","El precio no cumple el formato requerido");
}

/*== Verificando nombre duplicado ==*/
$check_nombre = conexion()->query("SELECT producto_nombre FROM producto WHERE producto_nombre='$nombre'");
if($check_nombre->rowCount()>0){
    enviar_respuesta_json("error","Nombre duplicado","El nombre ingresado ya existe");
}
$check_nombre = null;

/*== Verificando categoría ==*/
$check_categoria = conexion()->query("SELECT categoria_id FROM categoria WHERE categoria_id='$categoria'");
if($check_categoria->rowCount()<=0){
    enviar_respuesta_json("error","Categoría inválida","La categoría seleccionada no existe");
}
$check_categoria = null;


/*== Procesamiento de imagen (Sección MODIFICADA) ==*/

// Variable para el nombre de la foto en la DB
$foto = ""; 
// Array para rastrear archivos creados y borrarlos si algo falla
$archivos_creados = []; 

if($_FILES['producto_foto']['name']!="" && $_FILES['producto_foto']['size']>0){

    /* NUEVO: Directorios para imágenes optimizadas */
    $dir_base = '../img/producto/';
    $dir_original = $dir_base . 'original/';
    $dir_large = $dir_base . 'large/';
    $dir_thumbs = $dir_base . 'thumb/';

    /* NUEVO: Crear directorios si no existen (modo recursivo 'true') */
    if (!file_exists($dir_original) && !mkdir($dir_original, 0777, true)) {
        enviar_respuesta_json("error", "Error en directorio", "No se pudo crear el directorio 'original'");
    }
    if (!file_exists($dir_large) && !mkdir($dir_large, 0777, true)) {
        enviar_respuesta_json("error", "Error en directorio", "No se pudo crear el directorio 'large'");
    }
    if (!file_exists($dir_thumbs) && !mkdir($dir_thumbs, 0777, true)) {
        enviar_respuesta_json("error", "Error en directorio", "No se pudo crear el directorio 'thumb'");
    }

    /* Se mantienen las validaciones de tipo y tamaño antes de procesar */
    if(mime_content_type($_FILES['producto_foto']['tmp_name'])!="image/jpeg" && mime_content_type($_FILES['producto_foto']['tmp_name'])!="image/png"){
        enviar_respuesta_json("error","Formato inválido","La imagen debe ser JPG o PNG");
    }

    if(($_FILES['producto_foto']['size']/1024)>3072){
        enviar_respuesta_json("error","Imagen demasiado grande","La imagen supera el límite de 3MB");
    }

    /* MODIFICADO: Lógica de nombrado */
    // Obtenemos el nombre base sin extensión
    $img_nombre = renombrar_foto($nombre); 
    // El nombre a guardar en la DB será el nombre base con la extensión .webp
    $foto = $img_nombre . '.webp'; 

    /* NUEVO: Bloque try-catch para llamar a la función de procesamiento */
    try {
        
        procesar_imagen_optimizada(
            $_FILES['producto_foto'], // El archivo subido
            $img_nombre,              // El nombre base (ej: "nombre-producto-1")
            $dir_original,            // Directorio para el original
            $dir_large,               // Directorio para la versión large
            $dir_thumbs,              // Directorio para la versión thumb
            $archivos_creados         // Array (pasado por referencia) que se llenará con las rutas
        );

    } catch (Exception $e) {
        // Si la función 'procesar_imagen_optimizada' lanza una excepción
        // Borramos cualquier archivo que se haya alcanzado a crear
        foreach ($archivos_creados as $archivo) {
            if (is_file($archivo)) { unlink($archivo); }
        }
        enviar_respuesta_json("error", "Error al procesar imagen", $e->getMessage());
    }

} // Fin del if($_FILES...)

/*== Guardando producto ==*/
$guardar_producto=conexion()->prepare("INSERT INTO producto(producto_nombre,producto_precio,producto_foto,categoria_id,usuario_id,descripcion_producto) VALUES(:nombre,:precio,:foto,:categoria,:usuario,:descripcion)");

$marcadores=[
    ":nombre"=>$nombre,
    ":precio"=>$precio,
    ":foto"=>$foto, // Aquí se guarda el nombre base con extensión .webp (ej: "producto-1.webp")
    ":categoria"=>$categoria,
    ":descripcion"=>$descripcion,
    ":usuario"=>$_SESSION['id']
];

$guardar_producto->execute($marcadores);

if($guardar_producto->rowCount()==1){
    enviar_respuesta_json("success","¡Producto registrado!","El producto se registró con éxito");
}else{
    /* MODIFICADO: Si falla el guardado en BD, borra todos los archivos (original, large, thumb) */
    foreach ($archivos_creados as $archivo) {
        if (is_file($archivo)) { unlink($archivo); }
    }
    enviar_respuesta_json("error","Error inesperado","No se pudo registrar el producto, intente nuevamente");
}
?>