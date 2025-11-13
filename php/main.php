<?php
//conexion a la base de datos
function conexion()
{
    $pdo = new PDO('mysql:host=localhost;dbname=SistemaMenu', 'root', '',);
    return $pdo;
}

# verificar datos #
function verificar_datos($filtro, $cadena)
{

    if (preg_match("/^" . $filtro . "$/", $cadena)) {
        return false;
    } else {
        return true;
    }
}


# limpiar cadena de texto#
function limpiar_cadena($cadena)
{

    $cadena = trim($cadena);
    $cadena = stripslashes($cadena);
    $cadena = str_ireplace("<script>", "", $cadena);
    $cadena = str_ireplace("</script>", "", $cadena);
    $cadena = str_ireplace("<script src", "", $cadena);
    $cadena = str_ireplace("<script type=", "", $cadena);
    $cadena = str_ireplace("SELECT * FROM", "", $cadena);
    $cadena = str_ireplace("DELETE FROM", "", $cadena);
    $cadena = str_ireplace("INSERT INTO", "", $cadena);
    $cadena = str_ireplace("DROP TABLE", "", $cadena);
    $cadena = str_ireplace("DROP DATABASE", "", $cadena);
    $cadena = str_ireplace("TRUNCATE TABLE", "", $cadena);
    $cadena = str_ireplace("SHOW TABLES;", "", $cadena);
    $cadena = str_ireplace("SHOW DATABASES;", "", $cadena);
    $cadena = str_ireplace("<?php", "", $cadena);
    $cadena = str_ireplace("?>", "", $cadena);
    $cadena = str_ireplace("--", "", $cadena);
    $cadena = str_ireplace("^", "", $cadena);
    $cadena = str_ireplace("<", "", $cadena);
    $cadena = str_ireplace("[", "", $cadena);
    $cadena = str_ireplace("]", "", $cadena);
    $cadena = str_ireplace("==", "", $cadena);
    $cadena = str_ireplace(";", "", $cadena);
    $cadena = str_ireplace("::", "", $cadena);
    $cadena = trim($cadena);
    $cadena = stripslashes($cadena);
    return $cadena;
}

# funcion renombrar fotos# 
function renombrar_foto($nombre)
{
    $nombre = str_ireplace(" ", "_", $nombre);
    $nombre = str_ireplace("/", "_", $nombre);
    $nombre = str_ireplace("#", "_", $nombre);
    $nombre = str_ireplace("-", "_", $nombre);
    $nombre = str_ireplace("$", "_", $nombre);
    $nombre = str_ireplace(".", "_", $nombre);
    $nombre = str_ireplace(",", "_", $nombre);
    $nombre = $nombre . "_" . rand(0, 100);
    return $nombre;
}

#paginador de tablas  #
function paginador_tablas($pagina, $Npaginas, $url, $botones)
{
    // Clases de Tailwind CSS para reutilizar
    $base_class = "flex items-center justify-center px-3 h-8 leading-tight text-gray-500 bg-white border border-gray-300 hover:bg-gray-100 hover:text-gray-700";
    $active_class = "flex items-center justify-center px-3 h-8 text-blue-600 border border-gray-300 bg-blue-50 hover:bg-blue-100 hover:text-blue-700";
    $disabled_class = "flex items-center justify-center px-3 h-8 leading-tight text-gray-500 bg-white border border-gray-300 cursor-not-allowed opacity-50";

    $tabla = '<nav class="flex justify-center items-center mt-6" aria-label="Page navigation">';

    // Botón "Anterior"
    if ($pagina <= 1) {
        $tabla .= '<span class="' . $disabled_class . ' rounded-l-lg">Anterior</span>';
    } else {
        $tabla .= '<a href="' . $url . ($pagina - 1) . '" class="' . $base_class . ' rounded-l-lg">Anterior</a>';
    }

    // --- Lógica para mostrar los números de página ---

    // Calcula el rango de botones a mostrar alrededor de la página actual
    $rango_inicio = max(1, $pagina - floor($botones / 2));
    $rango_fin = min($Npaginas, $rango_inicio + $botones - 1);

    // Ajusta el inicio si el final llega al límite de páginas
    $rango_inicio = max(1, $rango_fin - $botones + 1);

    // Muestra el botón de la primera página y los puntos suspensivos si es necesario
    if ($rango_inicio > 1) {
        $tabla .= '<a href="' . $url . '1" class="' . $base_class . '">1</a>';
        if ($rango_inicio > 2) {
            $tabla .= '<span class="' . $base_class . '">...</span>';
        }
    }

    // Bucle para crear los botones de página numéricos
    for ($i = $rango_inicio; $i <= $rango_fin; $i++) {
        if ($pagina == $i) {
            $tabla .= '<span class="' . $active_class . '">' . $i . '</span>'; // Página actual
        } else {
            $tabla .= '<a href="' . $url . $i . '" class="' . $base_class . '">' . $i . '</a>';
        }
    }

    // Muestra el botón de la última página y los puntos suspensivos si es necesario
    if ($rango_fin < $Npaginas) {
        if ($rango_fin < $Npaginas - 1) {
            $tabla .= '<span class="' . $base_class . '">...</span>';
        }
        $tabla .= '<a href="' . $url . $Npaginas . '" class="' . $base_class . '">' . $Npaginas . '</a>';
    }


    // Botón "Siguiente"
    if ($pagina == $Npaginas) {
        $tabla .= '<span class="' . $disabled_class . ' rounded-r-lg">Siguiente</span>';
    } else {
        $tabla .= '<a href="' . $url . ($pagina + 1) . '" class="' . $base_class . ' rounded-r-lg">Siguiente</a>';
    }

    $tabla .= '</nav>';
    return $tabla;
}


function mostrar_error($mensaje)
{
    echo '
        <script>
            Swal.fire({
                icon: "error",
                title: "¡Ocurrió un error inesperado!",
                text: "' . $mensaje . '"
            });
        </script>
    ';
    exit();
}


function enviar_respuesta_json($tipo, $titulo, $texto, $data = null)
{
    header('Content-Type: application/json');
    $respuesta = [
        "tipo" => $tipo,
        "titulo" => $titulo,
        "texto" => $texto
    ];

    if ($data) {
        $respuesta['nuevaCategoria'] = $data;
    }
    echo json_encode($respuesta);
    exit();
}



function redimensionar_y_guardar_desde_recurso($recurso, $ancho_orig, $alto_orig, $destino, $max_ancho, $max_alto, $calidad)
{
    // Calcula las nuevas dimensiones manteniendo la proporción
    $ratio = $ancho_orig / $alto_orig;
    if ($max_ancho / $max_alto > $ratio) {
        $nuevo_ancho = $max_alto * $ratio;
        $nuevo_alto = $max_alto;
    } else {
        $nuevo_alto = $max_ancho / $ratio;
        $nuevo_ancho = $max_ancho;
    }

    // Crea el lienzo para la nueva imagen
    $copia = imagecreatetruecolor($nuevo_ancho, $nuevo_alto);

    // Mantiene la transparencia para PNGs
    imagealphablending($copia, false);
    imagesavealpha($copia, true);

    // Copia y redimensiona la imagen
    imagecopyresampled($copia, $recurso, 0, 0, 0, 0, $nuevo_ancho, $nuevo_alto, $ancho_orig, $alto_orig);

    // Guarda la nueva imagen en formato WebP
    if (!imagewebp($copia, $destino, $calidad)) {
        imagedestroy($copia);
        throw new Exception("No se pudo guardar la imagen WebP en {$destino}.");
    }

    // Libera memoria
    imagedestroy($copia);
}

/**
 * Proceso principal y optimizado para una imagen subida.
 * Mueve el original y crea las versiones large y thumb.
 */
function procesar_imagen_optimizada($archivo_subido, $nombre_base, $dir_original, $dir_large, $dir_thumbs, &$archivos_a_eliminar)
{
    // 1. Preparar rutas
    $extension_original = strtolower(pathinfo($archivo_subido['name'], PATHINFO_EXTENSION));
    $ruta_original_final = $dir_original . $nombre_base . '.' . $extension_original;
    $ruta_large_webp = $dir_large . $nombre_base . '.webp';
    $ruta_thumb_webp = $dir_thumbs . $nombre_base . '.webp';

    // 2. Registrar archivos para posible limpieza en caso de error
    $archivos_a_eliminar[] = $ruta_original_final;
    $archivos_a_eliminar[] = $ruta_large_webp;
    $archivos_a_eliminar[] = $ruta_thumb_webp;

    // 3. Mover el archivo original subido
    if (!move_uploaded_file($archivo_subido['tmp_name'], $ruta_original_final)) {
        throw new Exception("No se pudo guardar la imagen original.");
    }

    // 4. Crear un recurso de imagen desde el archivo original (UNA SOLA VEZ)
    $info_imagen = getimagesize($ruta_original_final);
    $ancho_original = $info_imagen[0];
    $alto_original = $info_imagen[1];
    switch ($info_imagen['mime']) {
        case 'image/jpeg':
            $original_en_memoria = imagecreatefromjpeg($ruta_original_final);
            break;
        case 'image/png':
            $original_en_memoria = imagecreatefrompng($ruta_original_final);
            break;
        case 'image/gif':
            $original_en_memoria = imagecreatefromgif($ruta_original_final);
            break;
        default:
            throw new Exception("Formato de imagen no soportado: " . $info_imagen['mime']);
    }

    redimensionar_y_guardar_desde_recurso($original_en_memoria, $ancho_original, $alto_original, $ruta_large_webp, 1024, 768, 85);

    // 6. Crear la versión "thumb" a partir del MISMO recurso en memoria
    redimensionar_y_guardar_desde_recurso($original_en_memoria, $ancho_original, $alto_original, $ruta_thumb_webp, 200, 200, 80);

    // 7. Liberar memoria del recurso principal
    imagedestroy($original_en_memoria);
}
