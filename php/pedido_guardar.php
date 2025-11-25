<?php
ob_start(); 
header('Content-Type: application/json');

if (!file_exists("main.php")) {
    ob_clean();
    echo json_encode(["status" => "error", "message" => "Falta archivo main.php"]);
    exit;
}
require_once "main.php";

$input = file_get_contents('php://input');
$data = json_decode($input, true);

if (!$data) {
    ob_clean();
    echo json_encode(["status" => "error", "message" => "No se recibieron datos válidos"]);
    exit;
}

$nombre_cliente = limpiar_cadena($data['nombre']);
$metodo_pago_raw = $data['metodo_pago']; 
$items = $data['items']; 
$total_bs = $data['total_bs'];
$total_usd = $data['total_usd'];
$nota = isset($data['nota']) ? limpiar_cadena($data['nota']) : '';

$metodo_db = ($metodo_pago_raw === 'pago_movil') ? 'Pago Movil' : 'Efectivo';
$estado_db = ($metodo_pago_raw === 'pago_movil') ? 'Por Verificar' : 'Pendiente';

$conexion = conexion();

try {
    $conexion->beginTransaction();

    // 1. Insertar Cliente
    $stmt = $conexion->prepare("INSERT INTO cliente(nombre_cliente, apellido_cliente, telefono_cliente) VALUES(:nom, 'Cliente', '0000')");
    $stmt->execute([':nom' => $nombre_cliente]);
    $id_cliente = $conexion->lastInsertId();

    // 2. Resumen
    $resumen = "";
    foreach($items as $item) {
        $cant = $item['cantidad'] ?? 1;
        $nom = $item['nombre'] ?? 'Producto';
        $resumen .= $cant . "x " . $nom . " | ";
    }

    // 3. Insertar Pedido
    $sql_pedido = "INSERT INTO pedido(id_cliente, fecha, precio_total, metodo_pago, estado_pago, total_usd, resumen_pedido) 
                   VALUES(:idc, NOW(), :total, :metodo, :estado, :usd, :resumen)";
    $stmt_pedido = $conexion->prepare($sql_pedido);
    $stmt_pedido->execute([
        ':idc' => $id_cliente,
        ':total' => $total_bs,
        ':metodo' => $metodo_db,
        ':estado' => $estado_db,
        ':usd' => $total_usd,
        ':resumen' => $resumen
    ]);
    $id_pedido = $conexion->lastInsertId();

    // 4. Insertar Detalles
    // Preparamos el insert que acepta variantes (id_variante_producto puede ser NULL o un numero)
    $sql_detalle = "INSERT INTO pedido_detalle(id_pedido, id_producto, id_variante_producto, id_monto) 
                    VALUES(:idp, :idprod, :idvar, :precio)";
    $stmt_detalle = $conexion->prepare($sql_detalle);

    // --- CONSULTAS DE VERIFICACIÓN ---
    
    // 1. Buscar en tabla PRODUCTO (Productos normales)
    $check_prod = $conexion->prepare("SELECT producto_id FROM producto WHERE producto_id = :id LIMIT 1");
    
    // 2. Buscar en tabla VARIANTE_PRODUCTO (Aquí está tu ID 8)
    // Buscamos por 'id_variante_producto' y recuperamos el 'producto_id' padre
    $check_variant = $conexion->prepare("SELECT producto_id FROM variante_producto WHERE id_variante_producto = :id LIMIT 1");
    
    // 3. Buscar en tabla PROMOCIONES
    $check_promo = $conexion->prepare("SELECT promo_id FROM promociones WHERE promo_id = :id LIMIT 1");
    
    // Auxiliares para promos
    $get_linked_prod = $conexion->prepare("SELECT producto_id FROM promocion_productos WHERE promo_id = :id LIMIT 1");
    $get_any_prod = $conexion->prepare("SELECT producto_id FROM producto LIMIT 1");

    foreach($items as $item) {
        // Obtenemos el ID limpio
        $id_recibido = $item['id'] ?? null;
        if($id_recibido) $id_recibido = intval(preg_replace('/[^0-9]+/', '', $id_recibido), 10);

        if (!$id_recibido) {
            throw new Exception("El item '{$item['nombre']}' tiene un ID inválido.");
        }

        $id_producto_bd = null;
        $id_variante_bd = null; 

        // --- LÓGICA DE BÚSQUEDA JERÁRQUICA ---

        // INTENTO 1: ¿Es un Producto Normal?
        $check_prod->execute([':id' => $id_recibido]);
        if ($check_prod->rowCount() > 0) {
            // Es un producto normal
            $id_producto_bd = $id_recibido;
            $id_variante_bd = null; 
        } 
        else {
            // INTENTO 2: ¿Es una Variante? (Revisamos tabla variante_producto)
            $check_variant->execute([':id' => $id_recibido]);
            if ($check_variant->rowCount() > 0) {
                // ¡Encontrado en variante_producto! (Aquí cae el ID 8)
                $datos_variante = $check_variant->fetch();
                
                $id_producto_bd = $datos_variante['producto_id']; // ID del padre (ej: 117 Pasta)
                $id_variante_bd = $id_recibido;                   // ID de la variante (ej: 8)
            } 
            else {
                // INTENTO 3: ¿Es una Promoción?
                $check_promo->execute([':id' => $id_recibido]);
                if ($check_promo->rowCount() > 0) {
                    // Es promo, buscamos un producto padre prestado
                    $get_linked_prod->execute([':id' => $id_recibido]);
                    $linked = $get_linked_prod->fetch();
                    $id_producto_bd = $linked ? $linked['producto_id'] : null;
                    
                    if(!$id_producto_bd){
                        $get_any_prod->execute();
                        $any = $get_any_prod->fetch();
                        $id_producto_bd = $any['producto_id'];
                    }
                    $id_variante_bd = null;
                } else {
                    // Si falla todo
                    throw new Exception("El ID $id_recibido ({$item['nombre']}) no se encuentra en Productos, Variantes ni Promociones.");
                }
            }
        }

        // Calcular total de la línea
        $precio_unitario = $item['precio'] ?? 0;
        $cantidad = $item['cantidad'] ?? 1;
        $precio_linea = $precio_unitario * $cantidad;

        // Guardar detalle
        $stmt_detalle->execute([
            ':idp' => $id_pedido,
            ':idprod' => $id_producto_bd,
            ':idvar' => $id_variante_bd, // Aquí se guardará el 8 si es variante
            ':precio' => $precio_linea 
        ]);
    }

    $conexion->commit();
    ob_clean();
    echo json_encode(["status" => "success", "message" => "Pedido registrado correctamente"]);

} catch (Exception $e) {
    if (isset($conexion) && $conexion->inTransaction()) {
        $conexion->rollBack();
    }
    ob_clean();
    echo json_encode(["status" => "error", "message" => "Error: " . $e->getMessage()]);
}
?>