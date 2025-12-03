-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Servidor: 127.0.0.1
-- Tiempo de generación: 03-12-2025 a las 14:17:24
-- Versión del servidor: 10.4.32-MariaDB
-- Versión de PHP: 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Base de datos: `sistemamenu`
--

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `anuncios`
--

CREATE TABLE `anuncios` (
  `anuncio_id` int(11) NOT NULL,
  `anuncio_mensaje` text NOT NULL,
  `anuncio_hora_inicio` int(11) NOT NULL,
  `anuncio_hora_fin` int(11) NOT NULL,
  `anuncio_tipo` varchar(50) NOT NULL DEFAULT 'info',
  `anuncio_prioridad` int(11) NOT NULL DEFAULT 0,
  `anuncio_estado` tinyint(1) NOT NULL DEFAULT 1,
  `anuncio_fecha_inicio` date DEFAULT NULL,
  `anuncio_fecha_fin` date DEFAULT NULL,
  `anuncio_creado` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `anuncios`
--

INSERT INTO `anuncios` (`anuncio_id`, `anuncio_mensaje`, `anuncio_hora_inicio`, `anuncio_hora_fin`, `anuncio_tipo`, `anuncio_prioridad`, `anuncio_estado`, `anuncio_fecha_inicio`, `anuncio_fecha_fin`, `anuncio_creado`) VALUES
(9, 'El Desayuno termina a las 11:00 AM', 8, 11, 'alerta', 1, 1, NULL, NULL, '2025-11-17 00:34:57'),
(10, 'Contamos con Delivery', 0, 23, 'info', 3, 1, NULL, NULL, '2025-11-17 00:41:31'),
(11, 'holaaaaaaaaaaaaaaaaaaa', 8, 13, 'alerta', 0, 1, NULL, NULL, '2025-11-17 14:59:44');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `anuncio_categorias`
--

CREATE TABLE `anuncio_categorias` (
  `anuncio_id` int(11) NOT NULL,
  `categoria_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `anuncio_categorias`
--

INSERT INTO `anuncio_categorias` (`anuncio_id`, `categoria_id`) VALUES
(9, 44);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `anuncio_productos`
--

CREATE TABLE `anuncio_productos` (
  `anuncio_id` int(11) NOT NULL,
  `producto_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `anuncio_productos`
--

INSERT INTO `anuncio_productos` (`anuncio_id`, `producto_id`) VALUES
(11, 105);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `categoria`
--

CREATE TABLE `categoria` (
  `categoria_id` int(11) NOT NULL,
  `categoria_nombre` varchar(50) DEFAULT NULL,
  `categoria_estado` int(11) NOT NULL,
  `categoria_hora_inicio` time DEFAULT NULL,
  `categoria_hora_fin` time DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `categoria`
--

INSERT INTO `categoria` (`categoria_id`, `categoria_nombre`, `categoria_estado`, `categoria_hora_inicio`, `categoria_hora_fin`) VALUES
(43, 'Almuerzos', 1, '00:00:00', '00:00:00'),
(44, 'Desayunos', 1, '00:00:06', '00:00:11'),
(45, 'Bebidas', 1, '00:00:00', '00:00:00'),
(46, 'Especiales', 1, '00:00:00', '00:00:00');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `cliente`
--

CREATE TABLE `cliente` (
  `id_cliente` int(11) NOT NULL,
  `nombre_cliente` varchar(50) NOT NULL,
  `apellido_cliente` varchar(50) NOT NULL,
  `telefono_cliente` varchar(20) DEFAULT NULL,
  `cedula_cliente` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `cliente`
--

INSERT INTO `cliente` (`id_cliente`, `nombre_cliente`, `apellido_cliente`, `telefono_cliente`, `cedula_cliente`) VALUES
(245, 'alexis', 'mendoza', '04124618344', 30956959),
(246, 'jose', 'mendoza', '04160436151', 9645099),
(247, 'jose', 'mendoza', '04160436151', 945099),
(248, 'prueba', '', '04124618344', 123456789),
(249, 'prueba2', '', '04124618344', 12345678),
(250, 'aaaa', '', '21222222', 1234567);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `pedido`
--

CREATE TABLE `pedido` (
  `id_pedido` int(11) NOT NULL,
  `id_cliente` int(11) NOT NULL,
  `fecha` datetime DEFAULT current_timestamp(),
  `tipo_orden` varchar(50) DEFAULT 'Comer aquí',
  `precio_total` decimal(10,2) DEFAULT NULL,
  `metodo_pago` varchar(50) DEFAULT 'Efectivo',
  `referencia` varchar(50) DEFAULT NULL,
  `estado_pago` varchar(50) DEFAULT 'Pendiente',
  `total_usd` decimal(10,2) DEFAULT 0.00
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `pedido`
--

INSERT INTO `pedido` (`id_pedido`, `id_cliente`, `fecha`, `tipo_orden`, `precio_total`, `metodo_pago`, `referencia`, `estado_pago`, `total_usd`) VALUES
(245, 245, '2025-11-27 20:44:50', 'Comer Aquí', 737.01, 'Efectivo', NULL, 'Entregado', 3.00),
(246, 245, '2025-11-27 20:45:23', 'Para Llevar', 737.01, 'Efectivo', NULL, 'Entregado', 3.00),
(247, 246, '2025-11-27 20:46:19', 'Para Llevar', 737.01, 'Pago Móvil', '7777', 'Entregado', 3.00),
(248, 247, '2025-11-27 22:05:10', 'Para Llevar', 17.00, 'Efectivo', NULL, 'Rechazado', 17.00),
(249, 245, '2025-11-28 19:19:33', 'Comer Aquí', 1236.50, 'Pago Móvil', '1231', 'Entregado', 5.00),
(250, 248, '2025-11-28 19:19:57', 'Para Llevar', 741.90, 'Tarjeta', NULL, 'Entregado', 3.00),
(251, 249, '2025-11-28 19:20:17', 'Comer Aquí', 494.60, 'Efectivo', NULL, 'Entregado', 2.00),
(252, 250, '2025-11-30 19:39:02', 'Comer Aquí', 2473.00, 'Efectivo', NULL, 'Entregado', 10.00);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `pedido_detalle`
--

CREATE TABLE `pedido_detalle` (
  `id_detalle` int(11) NOT NULL,
  `id_pedido` int(11) NOT NULL,
  `id_producto` int(11) DEFAULT NULL,
  `id_promo` int(11) DEFAULT NULL,
  `id_variante_producto` int(11) DEFAULT NULL,
  `cantidad` int(11) NOT NULL DEFAULT 1,
  `precio_unitario` decimal(10,2) NOT NULL,
  `nota` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `pedido_detalle`
--

INSERT INTO `pedido_detalle` (`id_detalle`, `id_pedido`, `id_producto`, `id_promo`, `id_variante_producto`, `cantidad`, `precio_unitario`, `nota`) VALUES
(23, 245, NULL, 2, NULL, 1, 737.01, ''),
(24, 246, NULL, 2, NULL, 1, 737.01, ''),
(25, 247, NULL, 2, NULL, 1, 737.01, ''),
(26, 248, NULL, 7, NULL, 1, 1.00, ''),
(27, 248, NULL, 3, NULL, 1, 4.00, ''),
(28, 248, NULL, 2, NULL, 1, 3.00, ''),
(29, 248, NULL, 4, NULL, 1, 3.00, ''),
(30, 248, NULL, 1, NULL, 2, 2.00, ''),
(31, 248, 102, NULL, 19, 1, 2.00, ''),
(32, 249, 102, NULL, 19, 1, 494.60, ''),
(33, 249, 107, NULL, NULL, 1, 741.90, ''),
(34, 250, 100, NULL, NULL, 1, 741.90, ''),
(35, 251, NULL, 1, NULL, 1, 494.60, ''),
(36, 252, 108, NULL, NULL, 2, 494.60, ''),
(37, 252, NULL, 3, NULL, 1, 989.20, ''),
(38, 252, NULL, 1, NULL, 1, 494.60, '');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `permisos`
--

CREATE TABLE `permisos` (
  `permiso_id` bigint(20) UNSIGNED NOT NULL,
  `permiso_clave` varchar(50) NOT NULL,
  `permiso_nombre` varchar(100) NOT NULL,
  `permiso_modulo` varchar(50) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `permisos`
--

INSERT INTO `permisos` (`permiso_id`, `permiso_clave`, `permiso_nombre`, `permiso_modulo`) VALUES
(1, 'estadisticas.financieras', 'Ver Cierre de Caja y Ganancias', 'Estadísticas'),
(2, 'estadisticas.operativas', 'Ver Métricas de Productos', 'Estadísticas'),
(3, 'inventario.ver', 'Ver Menú y Categorías', 'Inventario'),
(4, 'inventario.gestionar', 'Crear/Editar Productos', 'Inventario'),
(5, 'inventario.pdf', 'Descargar PDF', 'Inventario'),
(6, 'campanas.gestionar', 'Gestionar Campañas y Horarios', 'Marketing'),
(7, 'pedidos.ver', 'Ver Tablero Kanban', 'Pedidos'),
(8, 'pedidos.preparar', 'Mover a Preparación/Listo', 'Pedidos'),
(9, 'pedidos.entregar', 'Mover a Entregado', 'Pedidos'),
(10, 'pedidos.notificar', 'Notificar Cliente (WhatsApp)', 'Pedidos'),
(11, 'config.negocio', 'Datos del Negocio (RIF/Pago)', 'Configuración'),
(12, 'usuarios.gestionar', 'Gestionar Usuarios y Roles', 'Configuración');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `permiso_rol`
--

CREATE TABLE `permiso_rol` (
  `rol_id` bigint(20) UNSIGNED NOT NULL,
  `permiso_id` bigint(20) UNSIGNED NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `permiso_rol`
--

INSERT INTO `permiso_rol` (`rol_id`, `permiso_id`) VALUES
(1, 1),
(1, 2),
(1, 3),
(1, 4),
(1, 5),
(1, 6),
(1, 7),
(1, 8),
(1, 9),
(1, 10),
(1, 11),
(1, 12),
(2, 2),
(2, 3),
(2, 4),
(2, 5),
(2, 6),
(2, 7),
(2, 8),
(2, 9),
(2, 10),
(3, 7),
(3, 8),
(4, 1),
(4, 7),
(4, 9),
(4, 10);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `producto`
--

CREATE TABLE `producto` (
  `producto_id` int(11) NOT NULL,
  `producto_nombre` varchar(50) DEFAULT NULL,
  `producto_precio` decimal(30,2) DEFAULT NULL,
  `producto_estado` tinyint(1) NOT NULL DEFAULT 1,
  `producto_foto` varchar(500) DEFAULT NULL,
  `categoria_id` int(11) DEFAULT NULL,
  `usuario_id` int(11) DEFAULT NULL,
  `descripcion_producto` varchar(250) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `producto`
--

INSERT INTO `producto` (`producto_id`, `producto_nombre`, `producto_precio`, `producto_estado`, `producto_foto`, `categoria_id`, `usuario_id`, `descripcion_producto`) VALUES
(99, 'botella agua', 0.50, 1, 'botella_agua_72.webp', 45, 30, 'botella de 440ml'),
(100, 'Pabellon criollo', 3.00, 1, 'Pabellon_criollo_53.webp', 43, 30, 'pabellon criollo clasico'),
(101, 'pasticho', 2.90, 1, 'pasticho_45.webp', 43, 30, 'pasticho venezolano'),
(102, 'arroz chino', 2.00, 1, 'arroz_chino_40.webp', 43, 30, 'arroz chinos especial'),
(103, 'jugo de melon pequeño', 0.50, 1, 'jugo_de_melon_pequeño_26.webp', 45, 30, 'pequeño'),
(104, 'jugo de guayaba pequeño', 0.50, 1, 'jugo_de_guayaba_pequeño_0.webp', 45, 30, 'pequeño'),
(105, 'Arepas', 1.70, 1, 'Arepas_35.webp', 43, 30, 'pelua, domino, reina pepiada, catira'),
(106, 'jugo de mango', 0.30, 1, 'jugo_de_mango_42.webp', 45, 30, 'mango'),
(107, 'club house', 3.00, 1, 'club_house_57.webp', 43, 30, 'normal'),
(108, 'prueba', 2.00, 1, 'prueba_40.webp', 44, 30, 'mouse'),
(109, 'prueba2', 2.00, 1, 'prueba2_81.webp', 46, 30, 'horizontal'),
(110, 'prueba3', 2.00, 1, 'prueba3_46.webp', 46, 30, 'lejos horizontal'),
(117, 'pasta', 2.00, 1, '', 43, 30, 'ddd'),
(122, 'chawarma', 1.00, 1, 'chawarma_55.webp', 43, 30, 'carne y vegetales'),
(123, 'sopa', 1.30, 1, '', 43, 30, 'costilla');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `promociones`
--

CREATE TABLE `promociones` (
  `promo_id` int(11) NOT NULL,
  `promo_nombre` varchar(100) NOT NULL,
  `promo_precio` decimal(10,2) NOT NULL,
  `promo_foto` varchar(100) DEFAULT NULL,
  `hora_inicio` int(11) NOT NULL,
  `hora_fin` int(11) NOT NULL,
  `fecha_inicio` date DEFAULT NULL,
  `fecha_fin` date DEFAULT NULL,
  `prioridad` int(11) NOT NULL DEFAULT 0,
  `estado` tinyint(1) NOT NULL DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `promociones`
--

INSERT INTO `promociones` (`promo_id`, `promo_nombre`, `promo_precio`, `promo_foto`, `hora_inicio`, `hora_fin`, `fecha_inicio`, `fecha_fin`, `prioridad`, `estado`) VALUES
(1, '2 empanas y un jugo', 2.00, '2_empana_y_un_jugo_65.webp', 0, 23, NULL, NULL, 1, 1),
(2, '2x1 en Arepas', 3.00, '2x1_en_Arepas_10.webp', 0, 23, NULL, NULL, 2, 1),
(3, 'arepa', 4.00, '', 1, 23, NULL, NULL, 3, 1),
(4, '1', 3.00, '', 2, 22, NULL, NULL, 4, 1),
(5, '3x2 en arepas', 5.00, '', 0, 23, NULL, NULL, 1, 0),
(7, 'super desayuno', 1.00, '', 0, 23, NULL, NULL, 1, 1);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `promocion_productos`
--

CREATE TABLE `promocion_productos` (
  `promo_id` int(11) NOT NULL,
  `producto_id` int(11) NOT NULL,
  `cantidad` int(11) NOT NULL DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `promocion_productos`
--

INSERT INTO `promocion_productos` (`promo_id`, `producto_id`, `cantidad`) VALUES
(1, 104, 1),
(2, 105, 2),
(3, 105, 1),
(4, 105, 1),
(5, 105, 1),
(7, 105, 2),
(7, 106, 1);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `roles`
--

CREATE TABLE `roles` (
  `rol_id` bigint(20) UNSIGNED NOT NULL,
  `rol_nombre` varchar(50) NOT NULL,
  `rol_clave` varchar(50) NOT NULL,
  `rol_descripcion` text DEFAULT NULL,
  `creado_en` timestamp NOT NULL DEFAULT current_timestamp(),
  `actualizado_en` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `roles`
--

INSERT INTO `roles` (`rol_id`, `rol_nombre`, `rol_clave`, `rol_descripcion`, `creado_en`, `actualizado_en`) VALUES
(1, 'Super Administrador', 'super_admin', 'Acceso total y configuración financiera.', '2025-12-02 22:10:36', '2025-12-02 22:10:36'),
(2, 'Gerente', 'gerente', 'Gestión operativa del menú y campañas.', '2025-12-02 22:10:36', '2025-12-02 22:10:36'),
(3, 'Cocina', 'cocina', 'Visualización de pedidos en preparación.', '2025-12-02 22:10:36', '2025-12-02 22:10:36'),
(4, 'Despacho', 'despacho', 'Entregas y atención al cliente final.', '2025-12-02 22:10:36', '2025-12-02 22:10:36');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `tiendas`
--

CREATE TABLE `tiendas` (
  `id_tienda` int(11) NOT NULL,
  `nombre_tienda` varchar(100) NOT NULL,
  `logo_tienda` varchar(255) DEFAULT NULL,
  `rif_tienda` varchar(20) DEFAULT NULL,
  `telefono_tienda` varchar(20) DEFAULT NULL,
  `direccion_tienda` text DEFAULT NULL,
  `email_tienda` varchar(100) DEFAULT NULL,
  `moneda_simbolo` varchar(5) DEFAULT '$',
  `impuesto` decimal(5,2) DEFAULT 0.00,
  `color_principal` varchar(20) DEFAULT '#E11D48',
  `color_secundario` varchar(20) DEFAULT '#1F2937',
  `pm_banco` varchar(50) DEFAULT NULL,
  `pm_telefono` varchar(20) DEFAULT NULL,
  `pm_cedula` varchar(20) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `tiendas`
--

INSERT INTO `tiendas` (`id_tienda`, `nombre_tienda`, `logo_tienda`, `rif_tienda`, `telefono_tienda`, `direccion_tienda`, `email_tienda`, `moneda_simbolo`, `impuesto`, `color_principal`, `color_secundario`, `pm_banco`, `pm_telefono`, `pm_cedula`) VALUES
(1, 'Alas Restaurante', 'logo_1_31.png', '123456789', '04124618344', 'Av. Agustin Alvarez, Maracay 2101, Aragua, Maracay, Aragua 2103', NULL, '', 0.00, '#eb0000', '#1F2937', 'Venezuela', '04124618344', '30956956');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `usuario`
--

CREATE TABLE `usuario` (
  `usuario_id` int(11) NOT NULL,
  `id_tienda` int(11) NOT NULL,
  `usuario_nombre` varchar(40) DEFAULT NULL,
  `usuario_apellido` varchar(50) DEFAULT NULL,
  `usuario_usuario` varchar(20) DEFAULT NULL,
  `usuario_clave` varchar(200) DEFAULT NULL,
  `rol_id` bigint(20) UNSIGNED DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `usuario`
--

INSERT INTO `usuario` (`usuario_id`, `id_tienda`, `usuario_nombre`, `usuario_apellido`, `usuario_usuario`, `usuario_clave`, `rol_id`) VALUES
(30, 1, 'Administrador', 'Administrador', NULL, NULL, NULL),
(31, 1, 'admin', 'Dueño', 'admin', '$2y$10$3e.zaoF/pfzrUIoAfzkGuuSUV8/4hsfybciQU/2XyUwxvuTELmYq.', 1),
(32, 1, 'Maria', 'Gerente', 'gerente', '$2y$10$3e.zaoF/pfzrUIoAfzkGuuSUV8/4hsfybciQU/2XyUwxvuTELmYq.', 2),
(34, 1, 'Ana', 'Despacho', 'caja', '$2y$10$3e.zaoF/pfzrUIoAfzkGuuSUV8/4hsfybciQU/2XyUwxvuTELmYq.', 4),
(37, 1, 'Daniel', 'Pua', 'danielpss', '$2y$10$p6svhCW7ZW5ClLh4NOegRuU9LC0k7iqEG0j9PJHLg/fruRETJDwz6', 4);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `variante`
--

CREATE TABLE `variante` (
  `id_variante` int(11) NOT NULL,
  `nombre_variante` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `variante`
--

INSERT INTO `variante` (`id_variante`, `nombre_variante`) VALUES
(8, 'salsa'),
(9, 'pollo'),
(18, 'Pequeño'),
(19, 'grande');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `variante_producto`
--

CREATE TABLE `variante_producto` (
  `id_variante_producto` int(11) NOT NULL,
  `producto_id` int(11) NOT NULL,
  `id_variante` int(11) NOT NULL,
  `precio_variante` float DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `variante_producto`
--

INSERT INTO `variante_producto` (`id_variante_producto`, `producto_id`, `id_variante`, `precio_variante`) VALUES
(8, 117, 8, NULL),
(9, 117, 9, NULL),
(19, 102, 18, 2),
(20, 123, 18, 2),
(21, 123, 19, 4);

--
-- Índices para tablas volcadas
--

--
-- Indices de la tabla `anuncios`
--
ALTER TABLE `anuncios`
  ADD PRIMARY KEY (`anuncio_id`);

--
-- Indices de la tabla `anuncio_categorias`
--
ALTER TABLE `anuncio_categorias`
  ADD PRIMARY KEY (`anuncio_id`,`categoria_id`),
  ADD KEY `categoria_id` (`categoria_id`);

--
-- Indices de la tabla `anuncio_productos`
--
ALTER TABLE `anuncio_productos`
  ADD PRIMARY KEY (`anuncio_id`,`producto_id`),
  ADD KEY `producto_id` (`producto_id`);

--
-- Indices de la tabla `categoria`
--
ALTER TABLE `categoria`
  ADD PRIMARY KEY (`categoria_id`);

--
-- Indices de la tabla `cliente`
--
ALTER TABLE `cliente`
  ADD PRIMARY KEY (`id_cliente`);

--
-- Indices de la tabla `pedido`
--
ALTER TABLE `pedido`
  ADD PRIMARY KEY (`id_pedido`),
  ADD KEY `fk_pedido_cliente` (`id_cliente`);

--
-- Indices de la tabla `pedido_detalle`
--
ALTER TABLE `pedido_detalle`
  ADD PRIMARY KEY (`id_detalle`),
  ADD KEY `fk_pedido` (`id_pedido`),
  ADD KEY `fk_producto` (`id_producto`),
  ADD KEY `fk_variante` (`id_variante_producto`),
  ADD KEY `fk_detalle_a_promo` (`id_promo`);

--
-- Indices de la tabla `permisos`
--
ALTER TABLE `permisos`
  ADD PRIMARY KEY (`permiso_id`),
  ADD UNIQUE KEY `clave` (`permiso_clave`);

--
-- Indices de la tabla `permiso_rol`
--
ALTER TABLE `permiso_rol`
  ADD PRIMARY KEY (`rol_id`,`permiso_id`),
  ADD KEY `fk_pr_permiso` (`permiso_id`);

--
-- Indices de la tabla `producto`
--
ALTER TABLE `producto`
  ADD PRIMARY KEY (`producto_id`),
  ADD KEY `categoria_id` (`categoria_id`),
  ADD KEY `usuario_id` (`usuario_id`);

--
-- Indices de la tabla `promociones`
--
ALTER TABLE `promociones`
  ADD PRIMARY KEY (`promo_id`);

--
-- Indices de la tabla `promocion_productos`
--
ALTER TABLE `promocion_productos`
  ADD PRIMARY KEY (`promo_id`,`producto_id`),
  ADD KEY `producto_id` (`producto_id`);

--
-- Indices de la tabla `roles`
--
ALTER TABLE `roles`
  ADD PRIMARY KEY (`rol_id`),
  ADD UNIQUE KEY `clave` (`rol_clave`);

--
-- Indices de la tabla `tiendas`
--
ALTER TABLE `tiendas`
  ADD PRIMARY KEY (`id_tienda`);

--
-- Indices de la tabla `usuario`
--
ALTER TABLE `usuario`
  ADD PRIMARY KEY (`usuario_id`),
  ADD KEY `id_tienda` (`id_tienda`),
  ADD KEY `fk_usuario_rol` (`rol_id`);

--
-- Indices de la tabla `variante`
--
ALTER TABLE `variante`
  ADD PRIMARY KEY (`id_variante`);

--
-- Indices de la tabla `variante_producto`
--
ALTER TABLE `variante_producto`
  ADD PRIMARY KEY (`id_variante_producto`),
  ADD KEY `producto_id` (`producto_id`),
  ADD KEY `id_variante` (`id_variante`);

--
-- AUTO_INCREMENT de las tablas volcadas
--

--
-- AUTO_INCREMENT de la tabla `anuncios`
--
ALTER TABLE `anuncios`
  MODIFY `anuncio_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=12;

--
-- AUTO_INCREMENT de la tabla `categoria`
--
ALTER TABLE `categoria`
  MODIFY `categoria_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=49;

--
-- AUTO_INCREMENT de la tabla `cliente`
--
ALTER TABLE `cliente`
  MODIFY `id_cliente` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=251;

--
-- AUTO_INCREMENT de la tabla `pedido`
--
ALTER TABLE `pedido`
  MODIFY `id_pedido` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=253;

--
-- AUTO_INCREMENT de la tabla `pedido_detalle`
--
ALTER TABLE `pedido_detalle`
  MODIFY `id_detalle` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=39;

--
-- AUTO_INCREMENT de la tabla `permisos`
--
ALTER TABLE `permisos`
  MODIFY `permiso_id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=13;

--
-- AUTO_INCREMENT de la tabla `producto`
--
ALTER TABLE `producto`
  MODIFY `producto_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=125;

--
-- AUTO_INCREMENT de la tabla `promociones`
--
ALTER TABLE `promociones`
  MODIFY `promo_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT de la tabla `roles`
--
ALTER TABLE `roles`
  MODIFY `rol_id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT de la tabla `tiendas`
--
ALTER TABLE `tiendas`
  MODIFY `id_tienda` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT de la tabla `usuario`
--
ALTER TABLE `usuario`
  MODIFY `usuario_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=43;

--
-- AUTO_INCREMENT de la tabla `variante`
--
ALTER TABLE `variante`
  MODIFY `id_variante` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=20;

--
-- AUTO_INCREMENT de la tabla `variante_producto`
--
ALTER TABLE `variante_producto`
  MODIFY `id_variante_producto` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=22;

--
-- Restricciones para tablas volcadas
--

--
-- Filtros para la tabla `anuncio_categorias`
--
ALTER TABLE `anuncio_categorias`
  ADD CONSTRAINT `anuncio_categorias_ibfk_1` FOREIGN KEY (`anuncio_id`) REFERENCES `anuncios` (`anuncio_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `anuncio_categorias_ibfk_2` FOREIGN KEY (`categoria_id`) REFERENCES `categoria` (`categoria_id`) ON DELETE CASCADE;

--
-- Filtros para la tabla `anuncio_productos`
--
ALTER TABLE `anuncio_productos`
  ADD CONSTRAINT `anuncio_productos_ibfk_1` FOREIGN KEY (`anuncio_id`) REFERENCES `anuncios` (`anuncio_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `anuncio_productos_ibfk_2` FOREIGN KEY (`producto_id`) REFERENCES `producto` (`producto_id`) ON DELETE CASCADE;

--
-- Filtros para la tabla `pedido`
--
ALTER TABLE `pedido`
  ADD CONSTRAINT `fk_pedido_cliente` FOREIGN KEY (`id_cliente`) REFERENCES `cliente` (`id_cliente`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Filtros para la tabla `pedido_detalle`
--
ALTER TABLE `pedido_detalle`
  ADD CONSTRAINT `fk_detalle_a_pedido` FOREIGN KEY (`id_pedido`) REFERENCES `pedido` (`id_pedido`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_detalle_a_producto` FOREIGN KEY (`id_producto`) REFERENCES `producto` (`producto_id`) ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_detalle_a_promo` FOREIGN KEY (`id_promo`) REFERENCES `promociones` (`promo_id`) ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_detalle_a_variante` FOREIGN KEY (`id_variante_producto`) REFERENCES `variante_producto` (`id_variante_producto`) ON UPDATE CASCADE;

--
-- Filtros para la tabla `permiso_rol`
--
ALTER TABLE `permiso_rol`
  ADD CONSTRAINT `fk_pr_permiso` FOREIGN KEY (`permiso_id`) REFERENCES `permisos` (`permiso_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_pr_rol` FOREIGN KEY (`rol_id`) REFERENCES `roles` (`rol_id`) ON DELETE CASCADE;

--
-- Filtros para la tabla `producto`
--
ALTER TABLE `producto`
  ADD CONSTRAINT `producto_ibfk_1` FOREIGN KEY (`categoria_id`) REFERENCES `categoria` (`categoria_id`),
  ADD CONSTRAINT `producto_ibfk_2` FOREIGN KEY (`usuario_id`) REFERENCES `usuario` (`usuario_id`);

--
-- Filtros para la tabla `promocion_productos`
--
ALTER TABLE `promocion_productos`
  ADD CONSTRAINT `promocion_productos_ibfk_1` FOREIGN KEY (`promo_id`) REFERENCES `promociones` (`promo_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `promocion_productos_ibfk_2` FOREIGN KEY (`producto_id`) REFERENCES `producto` (`producto_id`) ON DELETE CASCADE;

--
-- Filtros para la tabla `usuario`
--
ALTER TABLE `usuario`
  ADD CONSTRAINT `fk_usuario_rol` FOREIGN KEY (`rol_id`) REFERENCES `roles` (`rol_id`) ON DELETE SET NULL,
  ADD CONSTRAINT `usuario_ibfk_1` FOREIGN KEY (`id_tienda`) REFERENCES `tiendas` (`id_tienda`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Filtros para la tabla `variante_producto`
--
ALTER TABLE `variante_producto`
  ADD CONSTRAINT `variante_producto_ibfk_1` FOREIGN KEY (`producto_id`) REFERENCES `producto` (`producto_id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `variante_producto_ibfk_2` FOREIGN KEY (`id_variante`) REFERENCES `variante` (`id_variante`) ON DELETE CASCADE ON UPDATE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
