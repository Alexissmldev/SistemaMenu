-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Servidor: 127.0.0.1
-- Tiempo de generación: 17-11-2025 a las 04:34:36
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
(9, 'El Desayuno termina a las 11:00 AM', 8, 23, 'alerta', 1, 1, NULL, NULL, '2025-11-17 00:34:57'),
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
  `categoria_ubicacion` varchar(150) DEFAULT NULL,
  `categoria_estado` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `categoria`
--

INSERT INTO `categoria` (`categoria_id`, `categoria_nombre`, `categoria_ubicacion`, `categoria_estado`) VALUES
(43, 'Almuerzos', NULL, 1),
(44, 'Desayunos', NULL, 1),
(45, 'Bebidas', NULL, 1),
(46, 'Especiales', NULL, 1),
(47, 'dededed', NULL, 1),
(48, 'platos especiales', NULL, 1);

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
(98, 'Empanada', 1.70, 1, 'Empanada_75.webp', 44, 30, 'pollo, carne, jamon y queso'),
(99, 'botella agua', 0.50, 1, 'botella_agua_72.webp', 45, 30, 'botella de 440ml'),
(100, 'Pabellon criollo', 3.00, 1, 'Pabellon_criollo_53.webp', 43, 30, 'pabellon criollo clasico'),
(101, 'pasticho', 2.90, 1, 'pasticho_45.webp', 43, 30, 'pasticho venezolano'),
(102, 'arroz chino', 2.00, 1, 'arroz_chino_40.webp', 43, 30, 'arroz chinos especial'),
(103, 'jugo de melon pequeño', 0.50, 1, 'jugo_de_melon_pequeño_26.webp', 45, 30, 'pequeño'),
(104, 'jugo de guayaba pequeño', 0.50, 1, 'jugo_de_guayaba_pequeño_0.webp', 45, 30, 'pequeño'),
(105, 'Arepas', 1.70, 1, 'Arepas_35.webp', 44, 30, 'pelua, domino, reina pepiada, catira'),
(106, 'jugo de mango', 0.30, 1, 'jugo_de_mango_42.webp', 45, 30, 'mango'),
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
(2, '2x1 en Arepas', 3.00, '2x1_en_Arepas_10.webp', 0, 23, NULL, NULL, 2, 1);

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
(1, 98),
(1, 104),
(2, 105);

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
-- AUTO_INCREMENT de las tablas volcadas
--

--
-- AUTO_INCREMENT de la tabla `anuncios`
--
ALTER TABLE `anuncios`
  MODIFY `anuncio_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT de la tabla `categoria`
--
ALTER TABLE `categoria`
  MODIFY `categoria_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=49;

--
-- AUTO_INCREMENT de la tabla `producto`
--
ALTER TABLE `producto`
  MODIFY `producto_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=111;

--
-- AUTO_INCREMENT de la tabla `promociones`
--
ALTER TABLE `promociones`
  MODIFY `promo_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT de la tabla `usuario`
--
ALTER TABLE `usuario`
  MODIFY `usuario_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=31;

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
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
