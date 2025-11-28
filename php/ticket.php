<?php
    require_once "main.php";

    $id = (isset($_GET['id'])) ? $_GET['id'] : 0;
    $id = limpiar_cadena($id);
    $conexion = conexion();

    // 1. DATOS DE LA TIENDA (Para el encabezado)
    $datos_tienda = $conexion->query("SELECT * FROM tienda LIMIT 1")->fetch();

    // 2. DATOS DEL PEDIDO Y CLIENTE
    $query_pedido = "SELECT p.*, c.nombre_cliente, c.apellido_cliente, c.cedula_cliente, c.telefono_cliente 
                     FROM pedido p 
                     INNER JOIN cliente c ON p.id_cliente = c.id_cliente 
                     WHERE p.id_pedido = '$id'";
    $pedido = $conexion->query($query_pedido)->fetch();

    if(!$pedido){
        echo "Pedido no encontrado";
        exit();
    }

    // 3. DATOS DE LOS PRODUCTOS (Con lógica de Promociones)
    $sql_detalle = "SELECT 
                        d.cantidad, 
                        d.precio_unitario,
                        d.id_promo,
                        COALESCE(v.nombre_variante, pr.producto_nombre, promo.promo_nombre) as nombre_item,
                        pr.producto_nombre as nombre_padre
                    FROM pedido_detalle d
                    LEFT JOIN producto pr ON d.id_producto = pr.producto_id
                    LEFT JOIN variante_producto vp ON d.id_variante_producto = vp.id_variante_producto
                    LEFT JOIN variante v ON vp.id_variante = v.id_variante
                    LEFT JOIN promociones promo ON d.id_promo = promo.promo_id
                    WHERE d.id_pedido = '$id'";
    
    $detalles = $conexion->query($sql_detalle)->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ticket #<?php echo $pedido['id_pedido']; ?></title>
    <style>
        /* ESTILOS PARA IMPRESORA TÉRMICA */
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Courier New', Courier, monospace; /* Fuente tipo ticket */
        }
        body {
            background-color: #f0f0f0;
            display: flex;
            justify-content: center;
            padding-top: 20px;
        }
        .ticket {
            width: 80mm; /* Ancho estándar de impresora */
            background-color: white;
            padding: 15px;
            box-shadow: 0 0 10px rgba(0,0,0,0.1);
        }
        .centrado { text-align: center; }
        .derecha { text-align: right; }
        .negrita { font-weight: bold; }
        .mayus { text-transform: uppercase; }
        
        .linea {
            border-top: 1px dashed #000;
            margin: 8px 0;
        }
        
        .info-tienda h1 { font-size: 18px; margin-bottom: 5px; }
        .info-tienda p { font-size: 12px; }
        
        .info-cliente { margin-top: 10px; font-size: 12px; }
        .info-cliente p { margin-bottom: 2px; }

        .tabla-productos {
            width: 100%;
            font-size: 12px;
            margin-top: 10px;
            border-collapse: collapse;
        }
        .tabla-productos th { text-align: left; border-bottom: 1px solid #000; }
        .tabla-productos td { padding-top: 5px; vertical-align: top; }
        
        .detalle-promo {
            font-size: 10px;
            color: #444;
            padding-left: 10px;
            font-style: italic;
        }

        .totales {
            margin-top: 10px;
            font-size: 14px;
        }
        .total-grande {
            font-size: 18px;
            font-weight: bold;
            margin-top: 5px;
        }

        /* OCULTAR EN IMPRESIÓN */
        @media print {
            body { background: none; padding: 0; }
            .ticket { box-shadow: none; width: 100%; padding: 0; }
            .no-print { display: none; }
        }
    </style>
</head>
<body>

    <div class="ticket">
        
        <div class="centrado info-tienda">
            <h1 class="mayus"><?php echo $datos_tienda['nombre_tienda'] ?? 'MI NEGOCIO'; ?></h1>
            <p>RIF: <?php echo $datos_tienda['rif_tienda'] ?? 'J-0000000'; ?></p>
            <p>Tel: <?php echo $datos_tienda['telefono_tienda'] ?? ''; ?></p>
            <br>
            <p class="negrita">ORDEN #<?php echo $pedido['id_pedido']; ?></p>
            <p><?php echo date("d/m/Y h:i A", strtotime($pedido['fecha'])); ?></p>
            
            <?php if(stripos($pedido['tipo_orden'], 'llevar') !== false): ?>
                <p style="border: 1px solid #000; display:inline-block; padding: 2px 5px; margin-top:5px; font-weight:bold;">PARA LLEVAR</p>
            <?php else: ?>
                <p style="font-weight:bold;">MESA</p>
            <?php endif; ?>
        </div>

        <div class="linea"></div>

        <div class="info-cliente">
            <p><b>Cliente:</b> <?php echo $pedido['nombre_cliente']; ?></p>
            <p><b>CI/RIF:</b> <?php echo $pedido['cedula_cliente']; ?></p>
            <p><b>Tel:</b> <?php echo $pedido['telefono_cliente']; ?></p>
        </div>

        <div class="linea"></div>

        <table class="tabla-productos">
            <thead>
                <tr>
                    <th style="width: 15%;">Cant</th>
                    <th style="width: 55%;">Descrip</th>
                    <th style="width: 30%; text-align: right;">Total</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach($detalles as $item): 
                    $total_linea = $item['cantidad'] * $item['precio_unitario'];
                ?>
                <tr>
                    <td class="centrado"><?php echo $item['cantidad']; ?></td>
                    <td>
                        <?php echo $item['nombre_item']; ?>
                        
                        <?php if($item['nombre_item'] != $item['nombre_padre'] && $item['nombre_padre'] && empty($item['id_promo'])): ?>
                            <br><span style="font-size:10px;">(<?php echo $item['nombre_padre']; ?>)</span>
                        <?php endif; ?>

                        <?php if(!empty($item['id_promo'])): ?>
                            <?php 
                                $idPromo = $item['id_promo'];
                                $sqlContenido = "SELECT pp.cantidad, p.producto_nombre 
                                                 FROM promocion_productos pp
                                                 INNER JOIN producto p ON pp.producto_id = p.producto_id
                                                 WHERE pp.promo_id = '$idPromo'";
                                $contenido = $conexion->query($sqlContenido)->fetchAll();
                            ?>
                            <div class="detalle-promo">
                                <?php foreach($contenido as $sub): ?>
                                    + <?php echo $sub['cantidad']; ?> <?php echo $sub['producto_nombre']; ?><br>
                                <?php endforeach; ?>
                            </div>
                        <?php endif; ?>
                    </td>
                    <td class="derecha"><?php echo number_format($total_linea, 2); ?></td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>

        <div class="linea"></div>

        <div class="totales derecha">
            <?php if($pedido['metodo_pago'] == 'Pago Móvil'): ?>
                <p style="font-size:10px;">Pago Móvil Ref: <?php echo $pedido['referencia']; ?></p>
            <?php else: ?>
                <p style="font-size:10px;">Pago: <?php echo $pedido['metodo_pago']; ?></p>
            <?php endif; ?>
            
            <br>
            <p>REF USD: $<?php echo number_format($pedido['total_usd'], 2); ?></p>
            <p class="total-grande">TOTAL: Bs <?php echo number_format($pedido['precio_total'], 2); ?></p>
        </div>

        <div class="linea"></div>
        
        <div class="centrado" style="font-size: 10px; margin-top: 10px;">
            <p>¡Gracias por su compra!</p>
        </div>

        <div class="centrado no-print" style="margin-top: 20px;">
            <button onclick="window.print()" style="padding: 10px 20px; background: #000; color: #fff; border: none; cursor: pointer;">Imprimir</button>
            <br><br>
            <a href="javascript:window.close();" style="color: #000;">Cerrar Ventana</a>
        </div>

    </div>

    <script>
        // Auto-imprimir al cargar
        window.onload = function() {
            window.print();
        }
    </script>

</body>
</html>