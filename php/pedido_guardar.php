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

$conexion = conexion();

try {
    $conexion->beginTransaction();

    // ==========================================
    // 1. GESTIÓN INTELIGENTE DEL CLIENTE
    // ==========================================
    $nombre_completo = limpiar_cadena($data['nombre']);
    $partes_nombre = explode(' ', $nombre_completo, 2);
    $nombre_cliente = $partes_nombre[0];
    $apellido_cliente = isset($partes_nombre[1]) ? $partes_nombre[1] : '';

    // Limpiamos cédula y teléfono para guardar solo números o formato limpio
    $cedula = isset($data['cedula']) ? limpiar_cadena($data['cedula']) : '';
    $telefono = isset($data['telefono']) ? limpiar_cadena($data['telefono']) : '';

    $id_cliente = 0;

    // A) Verificar si el cliente ya existe por Cédula
    if (!empty($cedula)) {
        $check_cliente = $conexion->prepare("SELECT id_cliente FROM cliente WHERE cedula_cliente = :ced LIMIT 1");
        $check_cliente->execute([':ced' => $cedula]);

        if ($check_cliente->rowCount() > 0) {
            // ---> CLIENTE EXISTE: Actualizamos datos y recuperamos ID
            $datos_existentes = $check_cliente->fetch();
            $id_cliente = $datos_existentes['id_cliente'];

            $update_cliente = $conexion->prepare("UPDATE cliente SET nombre_cliente = :nom, apellido_cliente = :ape, telefono_cliente = :tel WHERE id_cliente = :id");
            $update_cliente->execute([
                ':nom' => $nombre_cliente,
                ':ape' => $apellido_cliente,
                ':tel' => $telefono,
                ':id'  => $id_cliente
            ]);
        }
    }

    // B) Si no existe (o no tiene cédula), lo creamos
    if ($id_cliente == 0) {
        $sql_cliente = "INSERT INTO cliente(nombre_cliente, apellido_cliente, cedula_cliente, telefono_cliente) 
                        VALUES(:nom, :ape, :ced, :tel)";
        $stmt = $conexion->prepare($sql_cliente);
        $stmt->execute([
            ':nom' => $nombre_cliente,
            ':ape' => $apellido_cliente,
            ':ced' => $cedula,
            ':tel' => $telefono
        ]);
        $id_cliente = $conexion->lastInsertId();
    }

    // ==========================================
    // 2. DATOS DEL PEDIDO (CABECERA)
    // ==========================================
    $items = $data['items'];
    $total_bs = $data['total_bs'];
    $total_usd = $data['total_usd'];

    // Lógica Pago
    $metodo_pago_raw = $data['metodo_pago'];
    $referencia_raw = isset($data['referencia']) ? limpiar_cadena($data['referencia']) : null;

    $metodo_db = "Efectivo";
    $estado_db = "Pendiente";

    if ($metodo_pago_raw === 'pago_movil') {
        $metodo_db = "Pago Móvil";
        $estado_db = "Por Verificar";
    } elseif ($metodo_pago_raw === 'en_caja') {
        $tipo_caja = isset($data['tipo_caja']) ? $data['tipo_caja'] : 'efectivo';
        $metodo_db = ($tipo_caja === 'tarjeta') ? "Tarjeta" : "Efectivo";
        $referencia_raw = null; // Limpiar referencia si es caja
        $estado_db = "Pendiente";
    }

    $tipo_orden_raw = isset($data['tipo_orden']) ? $data['tipo_orden'] : 'comer';
    $tipo_orden_db = ($tipo_orden_raw === 'llevar') ? 'Para Llevar' : 'Comer Aquí';

    $sql_pedido = "INSERT INTO pedido(id_cliente, fecha, precio_total, metodo_pago, referencia, tipo_orden, estado_pago, total_usd) 
                   VALUES(:idc, NOW(), :total, :metodo, :ref, :tipo, :estado, :usd)";

    $stmt_pedido = $conexion->prepare($sql_pedido);
    $stmt_pedido->execute([
        ':idc' => $id_cliente,
        ':total' => $total_bs,
        ':metodo' => $metodo_db,
        ':ref' => $referencia_raw,
        ':tipo' => $tipo_orden_db,
        ':estado' => $estado_db,
        ':usd' => $total_usd
    ]);
    $id_pedido = $conexion->lastInsertId();

    // ==========================================
    // 3. INSERTAR DETALLES
    // ==========================================

    $sql_detalle = "INSERT INTO pedido_detalle(id_pedido, id_producto, id_variante_producto, id_promo, cantidad, precio_unitario, nota) 
                    VALUES(:idp, :idprod, :idvar, :idpromo, :cant, :precio, :nota)";
    $stmt_detalle = $conexion->prepare($sql_detalle);

    // Consultas de verificación
    $check_prod = $conexion->prepare("SELECT producto_id FROM producto WHERE producto_id = :id LIMIT 1");
    $check_variant = $conexion->prepare("SELECT producto_id FROM variante_producto WHERE id_variante_producto = :id LIMIT 1");
    $check_promo = $conexion->prepare("SELECT promo_id FROM promociones WHERE promo_id = :id LIMIT 1");

    foreach ($items as $item) {
        $id_recibido = $item['id'] ?? null;
        if (!$id_recibido) continue;

        // Limpiar ID numérico
        $id_limpio = intval(preg_replace('/[^0-9]+/', '', $id_recibido));

        $id_producto_bd = null;
        $id_variante_bd = null;
        $id_promo_bd = null;

        // Detectar si es Promo o Producto
        $es_promo = isset($item['type']) && $item['type'] === 'promo';

        if ($es_promo) {
            // --- ES UNA PROMOCIÓN ---
            $check_promo->execute([':id' => $id_limpio]);
            if ($check_promo->rowCount() > 0) {
                $id_promo_bd = $id_limpio;
            } else {
                throw new Exception("La promoción seleccionada (ID: $id_limpio) ya no existe.");
            }
        } else {
            // --- ES UN PRODUCTO O VARIANTE ---
            $check_prod->execute([':id' => $id_limpio]);
            if ($check_prod->rowCount() > 0) {
                $id_producto_bd = $id_limpio;
            } else {
                $check_variant->execute([':id' => $id_limpio]);
                if ($check_variant->rowCount() > 0) {
                    $datos_variante = $check_variant->fetch();
                    $id_producto_bd = $datos_variante['producto_id'];
                    $id_variante_bd = $id_limpio;
                } else {
                    // Fallback seguridad
                    $check_promo->execute([':id' => $id_limpio]);
                    if ($check_promo->rowCount() > 0) {
                        $id_promo_bd = $id_limpio;
                    } else {
                        throw new Exception("El ítem '$id_limpio' no se encuentra en la base de datos.");
                    }
                }
            }
        }

        // Datos complementarios
        $cantidad = intval($item['cantidad'] ?? 1);
        $precio = floatval($item['precio'] ?? 0);
        $nota = isset($item['nota']) ? limpiar_cadena($item['nota']) : null;

        // EJECUTAR INSERT DETALLE
        $stmt_detalle->execute([
            ':idp' => $id_pedido,
            ':idprod' => $id_producto_bd,
            ':idvar' => $id_variante_bd,
            ':idpromo' => $id_promo_bd,
            ':cant' => $cantidad,
            ':precio' => $precio,
            ':nota' => $nota
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
    echo json_encode(["status" => "error", "message" => "Error al procesar: " . $e->getMessage()]);
}
