-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Servidor: 127.0.0.1
-- Tiempo de generación: 23-11-2025 a las 04:06:42
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
(10, 'Contamos con Delivery', 0, 23, 'info', 3, 1, NULL, NULL, '2025-11-17 00:41:31');

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

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `categoria`
--

CREATE TABLE `categoria` (
  `categoria_id` int(11) NOT NULL,
  `categoria_nombre` varchar(50) DEFAULT NULL,
  `categoria_estado` int(11) NOT NULL,
  `categoria_hora_inicio` int(2) NOT NULL DEFAULT 0,
  `categoria_hora_fin` int(2) NOT NULL DEFAULT 23
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `categoria`
--

INSERT INTO `categoria` (`categoria_id`, `categoria_nombre`, `categoria_estado`, `categoria_hora_inicio`, `categoria_hora_fin`) VALUES
(43, 'Almuerzos', 1, 0, 23),
(44, 'Desayunos', 1, 7, 11),
(45, 'Bebidas', 1, 0, 23),
(46, 'Especiales', 1, 0, 23),
(48, 'platos especiales', 1, 0, 23);

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
(105, 'Arepas', 1.70, 1, 'Arepas_35.webp', 45, 30, 'pelua, domino, reina pepiadka, catira'),
(106, 'jugo de mangos', 0.11, 1, 'jugo_de_mangos_36.webp', 45, 30, 'mango'),
(107, 'club house', 3.00, 1, 'club_house_57.webp', 43, 30, 'normal'),
(108, 'prueba', 2.00, 1, 'prueba_40.webp', 44, 30, 'mouse'),
(109, 'prueba2', 2.00, 1, 'prueba2_81.webp', 46, 30, 'horizontal'),
(110, 'prueba3', 2.00, 1, 'prueba3_46.webp', 46, 30, 'lejos horizontal');

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
(4, '1', 3.00, '', 2, 22, NULL, NULL, 4, 1);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `promocion_productos`
--

CREATE TABLE `promocion_productos` (
  `promo_id` int(11) NOT NULL,
  `producto_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `promocion_productos`
--

INSERT INTO `promocion_productos` (`promo_id`, `producto_id`) VALUES
(1, 104),
(2, 105),
(3, 105),
(4, 105);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `usuario`
--

CREATE TABLE `usuario` (
  `usuario_id` int(11) NOT NULL,
  `usuario_nombre` varchar(40) DEFAULT NULL,
  `usuario_apellido` varchar(50) DEFAULT NULL,
  `usuario_usuario` varchar(20) DEFAULT NULL,
  `usuario_clave` varchar(200) DEFAULT NULL,
  `usuario_email` varchar(70) DEFAULT NULL,
  `usuario_telefono` varchar(20) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `usuario`
--

INSERT INTO `usuario` (`usuario_id`, `usuario_nombre`, `usuario_apellido`, `usuario_usuario`, `usuario_clave`, `usuario_email`, `usuario_telefono`) VALUES
(30, 'Administrador', 'Administrador', 'admin', '$2y$10$3e.zaoF/pfzrUIoAfzkGuuSUV8/4hsfybciQU/2XyUwxvuTELmYq.', '', '4124618344');

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
(26, 'Pequeño'),
(27, 'Grande');

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
(40, 106, 26, 0.11),
(41, 106, 27, 0.11);

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
-- Indices de la tabla `usuario`
--
ALTER TABLE `usuario`
  ADD PRIMARY KEY (`usuario_id`);

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
  MODIFY `categoria_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=53;

--
-- AUTO_INCREMENT de la tabla `producto`
--
ALTER TABLE `producto`
  MODIFY `producto_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=132;

--
-- AUTO_INCREMENT de la tabla `promociones`
--
ALTER TABLE `promociones`
  MODIFY `promo_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT de la tabla `usuario`
--
ALTER TABLE `usuario`
  MODIFY `usuario_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=31;

--
-- AUTO_INCREMENT de la tabla `variante`
--
ALTER TABLE `variante`
  MODIFY `id_variante` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=28;

--
-- AUTO_INCREMENT de la tabla `variante_producto`
--
ALTER TABLE `variante_producto`
  MODIFY `id_variante_producto` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=42;

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
-- Filtros para la tabla `variante_producto`
--
ALTER TABLE `variante_producto`
  ADD CONSTRAINT `variante_producto_ibfk_1` FOREIGN KEY (`producto_id`) REFERENCES `producto` (`producto_id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `variante_producto_ibfk_2` FOREIGN KEY (`id_variante`) REFERENCES `variante` (`id_variante`) ON DELETE CASCADE ON UPDATE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
