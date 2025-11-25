<div class="container pb-6 pt-6">
    <h2 class="title is-3 has-text-centered">Gestión de Pedidos</h2>

    <div class="table-container">
        <table class="table is-bordered is-striped is-narrow is-hoverable is-fullwidth">
            <thead>
                <tr class="has-text-centered">
                    <th>#</th>
                    <th>Cliente</th>
                    <th class="has-text-centered">Productos</th> <th>Fecha</th>
                    <th>Método</th>
                    <th>Total</th>
                    <th>Estado</th>
                    <th>Acciones</th>
                </tr>
            </thead>
            <tbody>
                <?php
                    require_once "./php/main.php";
                    $conexion = conexion();

                    // Seleccionamos todos los datos del pedido (incluyendo resumen_pedido) y el nombre del cliente
                    $datos = $conexion->query("SELECT p.*, c.nombre_cliente 
                                               FROM pedido p 
                                               INNER JOIN cliente c ON p.id_cliente = c.id_cliente 
                                               ORDER BY p.fecha DESC");

                    while($rows = $datos->fetch()){
                ?>
                <tr class="has-text-centered">
                    <td><?php echo $rows['id_pedido']; ?></td>
                    <td><?php echo $rows['nombre_cliente']; ?></td>
                    
                    <td class="has-text-left">
                        <small><?php echo $rows['resumen_pedido']; ?></small>
                    </td>

                    <td><?php echo date("d/m H:i", strtotime($rows['fecha'])); ?></td>
                    
                    <td>
                        <?php if($rows['metodo_pago'] == 'Pago Movil'): ?>
                            <span class="tag is-info is-light">Pago Móvil</span>
                        <?php else: ?>
                            <span class="tag is-success is-light">Efectivo</span>
                        <?php endif; ?>
                    </td>
                    
                    <td><?php echo number_format($rows['precio_total'], 2); ?> Bs</td>
                    
                    <td>
                        <?php 
                            if($rows['estado_pago'] == 'Por Verificar') echo '<span class="tag is-warning">Por Verificar</span>';
                            elseif($rows['estado_pago'] == 'Aprobado') echo '<span class="tag is-success">Aprobado</span>';
                            elseif($rows['estado_pago'] == 'Rechazado') echo '<span class="tag is-danger">Rechazado</span>';
                            else echo '<span class="tag is-light">'.$rows['estado_pago'].'</span>';
                        ?>
                    </td>
                    
                    <td>
                        <?php if($rows['metodo_pago'] == 'Pago Movil' && $rows['estado_pago'] == 'Por Verificar'): ?>
                            <div class="buttons are-small is-centered">
                                <button onclick="verificarPago(<?php echo $rows['id_pedido']; ?>, 'Aprobado')" class="button is-success" title="Aprobar Pago">
                                    <i class="fas fa-check"></i>
                                </button>
                                <button onclick="verificarPago(<?php echo $rows['id_pedido']; ?>, 'Rechazado')" class="button is-danger" title="Rechazar Pago">
                                    <i class="fas fa-times"></i>
                                </button>
                            </div>
                        <?php else: ?>
                            <button class="button is-small is-light" disabled>
                                <i class="fas fa-check-circle"></i> Listo
                            </button>
                        <?php endif; ?>
                    </td>
                </tr>
                <?php } ?>
            </tbody>
        </table>
    </div>
</div>

<script>
function verificarPago(id, estado) {
    let accion = estado === 'Aprobado' ? 'aprobar' : 'rechazar';
    
    if(!confirm("¿Estás seguro de " + accion + " este pedido?")) return;
    
    let formData = new FormData();
    formData.append('id_pedido', id);
    formData.append('estado', estado);

    fetch('php/pedido_actualizar_pago.php', { method: 'POST', body: formData })
    .then(r => r.json())
    .then(data => {
        if(data.status == 'success') {
            location.reload();
        } else {
            alert(data.message);
        }
    })
    .catch(err => alert("Error de conexión"));
}
</script>