<?php
// Consulta para obtener categorías activas
$query_categorias = $conexion->prepare("
  SELECT DISTINCT c.categoria_nombre 
  FROM categoria c 
  INNER JOIN producto p ON c.categoria_id = p.categoria_id 
  WHERE p.producto_estado = 1 
  ORDER BY c.categoria_nombre ASC
");
$query_categorias->execute();
$categorias = $query_categorias->fetchAll(PDO::FETCH_ASSOC);

// Lógica de Reordenamiento de Categorías
$desayunos = array_filter($categorias, fn($c) => $c['categoria_nombre'] === 'Desayunos');
$almuerzos = array_filter($categorias, fn($c) => $c['categoria_nombre'] === 'Almuerzos');
$especiales = array_filter($categorias, fn($c) => $c['categoria_nombre'] === 'Especiales');
$bebidas = array_filter($categorias, fn($c) => $c['categoria_nombre'] === 'Bebidas');
$otras = array_filter($categorias, fn($c) => !in_array($c['categoria_nombre'], ['Desayunos', 'Almuerzos', 'Especiales', 'Bebidas']));

// Concatenar en el orden deseado
$categorias_ordenadas = array_merge($desayunos, $almuerzos, $otras, $especiales, $bebidas);
