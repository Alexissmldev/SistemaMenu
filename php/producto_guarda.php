<?php
require_once "../inc/session_start.php";
require_once "main.php";

/*== Almacenando datos del formulario ==*/
$nombre      = limpiar_cadena($_POST['producto_nombre']);
$precio      = limpiar_cadena($_POST['producto_precio']);
$categoria   = limpiar_cadena($_POST['producto_categoria']);
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

    // Creación de directorios si no existen
    if (!file_exists($dir_original)) mkdir($dir_original, 0777, true);
    if (!file_exists($dir_large)) mkdir($dir_large, 0777, true);
    if (!file_exists($dir_thumbs)) mkdir($dir_thumbs, 0777, true);

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
            if (is_file($archivo)) unlink($archivo);
        }
        enviar_respuesta_json("error", "Error al procesar imagen", $e->getMessage());
    }
}

/*== Guardando producto y variantes (con transacción) ==*/
$pdo = conexion();

try {
    $pdo->beginTransaction();

    // 1. Guardar el Producto Principal
    $guardar_producto = $pdo->prepare("INSERT INTO producto(producto_nombre,producto_precio,producto_foto,categoria_id,usuario_id,descripcion_producto) VALUES(:nombre,:precio,:foto,:categoria,:usuario,:descripcion)");

    $guardar_producto->execute([
        ":nombre" => $nombre,
        ":precio" => $precio,
        ":foto" => $foto,
        ":categoria" => $categoria,
        ":descripcion" => $descripcion,
        ":usuario" => $_SESSION['id']
    ]);

    if ($guardar_producto->rowCount() != 1) {
        throw new Exception("No se pudo registrar el producto principal.");
    }

    $producto_id = $pdo->lastInsertId();

    /*== 2. PROCESAR Y GUARDAR VARIANTES ==*/
    // CORRECCIÓN AQUI: Usar 'variante_nombre' para coincidir con el HTML
    if (isset($_POST['variante_nombre']) && is_array($_POST['variante_nombre'])) {

        $nombres_input = $_POST['variante_nombre'];
        $precios_input = $_POST['variante_precio'];

        // Preparar consultas SQL
        // NOTA: Asegúrate que tus columnas en BD sean 'id_variante' y 'nombre_variante'
        $stmt_check   = $pdo->prepare("SELECT id_variante FROM variante WHERE nombre_variante = :nombre");
        $stmt_insert  = $pdo->prepare("INSERT INTO variante(nombre_variante) VALUES(:nombre)");
        $stmt_link    = $pdo->prepare("INSERT INTO variante_producto(producto_id, id_variante, precio_variante) VALUES(:pid, :vid, :precio)");

        foreach ($nombres_input as $index => $nombre_variante) {

            $nombre_limpio = limpiar_cadena($nombre_variante);
            if (empty($nombre_limpio)) continue;

            // Procesar el precio
            $precio_valor = isset($precios_input[$index]) ? limpiar_cadena($precios_input[$index]) : "";
            $precio_final = null;

            if (!empty($precio_valor) && is_numeric($precio_valor)) {
                $precio_final = (float)$precio_valor;
            }

            /* A) Buscar o Crear Variante (Tabla 'variante') */
            $stmt_check->execute([":nombre" => $nombre_limpio]);
            $row_variante = $stmt_check->fetch(PDO::FETCH_ASSOC);

            $id_variante = null;

            if ($row_variante) {
                // Si existe, tomamos el ID
                $id_variante = $row_variante['id_variante'];
            } else {
                // Si no existe, insertamos y tomamos el nuevo ID
                $stmt_insert->execute([":nombre" => $nombre_limpio]);
                $id_variante = $pdo->lastInsertId();
            }

            /* B) Guardar Relación y Precio (Tabla 'variante_producto') */
            $stmt_link->execute([
                ":pid"    => $producto_id,
                ":vid"    => $id_variante,
                ":precio" => $precio_final
            ]);
        }
    }

    $pdo->commit();
    enviar_respuesta_json("success", "¡Producto registrado!", "El producto y sus variantes se registraron con éxito");
} catch (Exception $e) {
    $pdo->rollBack();
    foreach ($archivos_creados as $archivo) {
        if (is_file($archivo)) unlink($archivo);
    }
    enviar_respuesta_json("error", "Error inesperado", "No se pudo registrar el producto: " . $e->getMessage());
}

$pdo = null;
