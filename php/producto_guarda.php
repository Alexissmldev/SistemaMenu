<?php
require_once "../inc/session_start.php";
require_once "main.php";

/*== Almacenando datos ==*/
$nombre     = limpiar_cadena($_POST['producto_nombre']);
$precio     = limpiar_cadena($_POST['producto_precio']);
$categoria  = limpiar_cadena($_POST['producto_categoria']);
$descripcion = limpiar_cadena($_POST['producto_descripcion']);

/*== Verificando campos obligatorios ==*/
if ($nombre == "" || $precio == "" || $categoria == "" || $descripcion == "") {
    enviar_respuesta_json("error", "Campos vacíos", "No has llenado todos los campos obligatorios");
}

/*== Verificando integridad de los datos ==*/
if (verificar_datos("[a-zA-Z0-9áéíóúÁÉÍÓÚñÑ().,$#\-\/ ]{1,70}", $nombre)) {
    enviar_respuesta_json("error", "Formato inválido", "El nombre no cumple el formato requerido");
}
if (verificar_datos("[0-9.]{1,25}", $precio)) {
    enviar_respuesta_json("error", "Formato inválido", "El precio no cumple el formato requerido");
}

/*== Verificando nombre duplicado ==*/
$check_nombre = conexion()->query("SELECT producto_nombre FROM producto WHERE producto_nombre='$nombre'");
if ($check_nombre->rowCount() > 0) {
    enviar_respuesta_json("error", "Nombre duplicado", "El nombre ingresado ya existe");
}
$check_nombre = null;

/*== Verificando categoría ==*/
$check_categoria = conexion()->query("SELECT categoria_id FROM categoria WHERE categoria_id='$categoria'");
if ($check_categoria->rowCount() <= 0) {
    enviar_respuesta_json("error", "Categoría inválida", "La categoría seleccionada no existe");
}
$check_categoria = null;

/*== Procesamiento de imagen ==*/
$foto = ""; 
$archivos_creados = []; 

if ($_FILES['producto_foto']['name'] != "" && $_FILES['producto_foto']['size'] > 0) {

    $dir_base = '../img/producto/';
    $dir_original = $dir_base . 'original/';
    $dir_large = $dir_base . 'large/';
    $dir_thumbs = $dir_base . 'thumb/';

    if (!file_exists($dir_original) && !mkdir($dir_original, 0777, true)) {
        enviar_respuesta_json("error", "Error en directorio", "No se pudo crear el directorio 'original'");
    }
    if (!file_exists($dir_large) && !mkdir($dir_large, 0777, true)) {
        enviar_respuesta_json("error", "Error en directorio", "No se pudo crear el directorio 'large'");
    }
    if (!file_exists($dir_thumbs) && !mkdir($dir_thumbs, 0777, true)) {
        enviar_respuesta_json("error", "Error en directorio", "No se pudo crear el directorio 'thumb'");
    }

    if (mime_content_type($_FILES['producto_foto']['tmp_name']) != "image/jpeg" && mime_content_type($_FILES['producto_foto']['tmp_name']) != "image/png") {
        enviar_respuesta_json("error", "Formato inválido", "La imagen debe ser JPG o PNG");
    }

    if (($_FILES['producto_foto']['size'] / 1024) > 3072) {
        enviar_respuesta_json("error", "Imagen demasiado grande", "La imagen supera el límite de 3MB");
    }

    $img_nombre = renombrar_foto($nombre); 
    $foto = $img_nombre . '.webp'; 

    try {
        
        procesar_imagen_optimizada(
            $_FILES['producto_foto'], 
            $img_nombre,              
            $dir_original,            
            $dir_large,               
            $dir_thumbs,              
            $archivos_creados         
        );

    } catch (Exception $e) {
        foreach ($archivos_creados as $archivo) {
            if (is_file($archivo)) { unlink($archivo); }
        }
        enviar_respuesta_json("error", "Error al procesar imagen", $e->getMessage());
    }

} // Fin del if($_FILES...)

/*== Guardando producto y variantes (con transacción) ==*/

$pdo = conexion(); 

try {
    // 2. Iniciar la transacción
    $pdo->beginTransaction();

    // 3. Guardar el producto principal
    $guardar_producto = $pdo->prepare("INSERT INTO producto(producto_nombre,producto_precio,producto_foto,categoria_id,usuario_id,descripcion_producto) VALUES(:nombre,:precio,:foto,:categoria,:usuario,:descripcion)");
    
    $marcadores=[
        ":nombre"=>$nombre,
        ":precio"=>$precio,
        ":foto"=>$foto, 
        ":categoria"=>$categoria,
        ":descripcion"=>$descripcion,
        ":usuario"=>$_SESSION['id']
    ];
    
    $guardar_producto->execute($marcadores);

    // 4. Verificar si el producto se guardó
    if ($guardar_producto->rowCount() != 1) {
        throw new Exception("No se pudo registrar el producto principal.");
    }
    
    // 5. Obtener el ID del producto
    $producto_id = $pdo->lastInsertId();

    /*== 6. PROCESAR Y GUARDAR VARIANTES ==*/
    if (isset($_POST['variante_nombre']) && is_array($_POST['variante_nombre'])) {
        
        $variante_nombres = $_POST['variante_nombre'];
        $variante_precios = $_POST['variante_precio'];

        /* --- CAMBIO AQUÍ: Ya no guardamos precio en 'variante', solo nombre --- */
        // Primero buscamos si la variante ya existe (opcional pero recomendado para no duplicar nombres)
        // O simplemente insertamos una nueva. Aquí insertamos nueva para simplificar tu lógica actual.
        $stmt_variante = $pdo->prepare("INSERT INTO variante(nombre_variante) VALUES(:nombre)");
        
        /* --- CAMBIO AQUÍ: Ahora guardamos precio en 'variante_producto' --- */
        $stmt_link = $pdo->prepare("INSERT INTO variante_producto(producto_id, id_variante, precio) VALUES(:pid, :vid, :precio)");

        foreach ($variante_nombres as $index => $nombre_variante) {
            
            $nombre_variante_limpio = limpiar_cadena($nombre_variante);
            
            if (empty($nombre_variante_limpio)) {
                continue;
            }

            $precio_variante_limpio = limpiar_cadena($variante_precios[$index]);
            $precio_final = null; 
            if (!empty($precio_variante_limpio) && is_numeric($precio_variante_limpio)) {
                $precio_final = (float)$precio_variante_limpio;
            }

            // 6a. Guardar en la tabla 'variante' (SOLO NOMBRE)
            $stmt_variante->execute([
                ":nombre" => $nombre_variante_limpio
            ]);
            
            // 6b. Obtener el ID de la nueva variante
            $variante_id = $pdo->lastInsertId();

            // 6c. Enlazar producto y variante Y GUARDAR PRECIO AQUÍ
            $stmt_link->execute([
                ":pid" => $producto_id,
                ":vid" => $variante_id,
                ":precio" => $precio_final // <-- El precio se guarda aquí ahora
            ]);
        }
    }
    /*== FIN DE PROCESAR VARIANTES ==*/

    // 7. Si todo salió bien, confirmar los cambios
    $pdo->commit();
    
    enviar_respuesta_json("success","¡Producto registrado!","El producto y sus variantes se registraron con éxito");

} catch (Exception $e) {
    // 8. Si algo falló, revertir todo
    $pdo->rollBack();

    // Borrar archivos de imagen si la BD falló
    foreach ($archivos_creados as $archivo) {
        if (is_file($archivo)) { unlink($archivo); }
    }
    
    enviar_respuesta_json("error","Error inesperado","No se pudo registrar el producto. " . $e->getMessage());
}

$pdo = null;
?>