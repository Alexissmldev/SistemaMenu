-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Servidor: 127.0.0.1
-- Tiempo de generación: 12-11-2025 a las 16:27:08
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
(47, 'dededed', NULL, 1);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `producto`
--

CREATE TABLE `producto` (
  `producto_id` int(11) NOT NULL,
  `producto_codigo` varchar(70) DEFAULT NULL,
  `producto_nombre` varchar(50) DEFAULT NULL,
  `producto_precio` decimal(30,2) DEFAULT NULL,
  `producto_stock` int(11) DEFAULT NULL,
  `producto_estado` tinyint(1) NOT NULL DEFAULT 1,
  `producto_foto` varchar(500) DEFAULT NULL,
  `categoria_id` int(11) DEFAULT NULL,
  `usuario_id` int(11) DEFAULT NULL,
  `descripcion_producto` varchar(250) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `producto`
--

INSERT INTO `producto` (`producto_id`, `producto_codigo`, `producto_nombre`, `producto_precio`, `producto_stock`, `producto_estado`, `producto_foto`, `categoria_id`, `usuario_id`, `descripcion_producto`) VALUES
(98, NULL, 'Empanada', 1.70, NULL, 1, 'Empanada_75.webp', 44, 30, 'pollo, carne, jamon y queso'),
(99, NULL, 'botella agua', 0.50, NULL, 1, 'botella_agua_72.webp', 45, 30, 'botella de 440ml'),
(100, NULL, 'Pabellon criollo', 3.00, NULL, 1, 'Pabellon_criollo_53.webp', 43, 30, 'pabellon criollo clasico'),
(101, NULL, 'pasticho', 2.90, NULL, 1, 'pasticho_45.webp', 43, 30, 'pasticho venezolano'),
(102, NULL, 'arroz chino', 2.00, NULL, 1, 'arroz_chino_87.webp', 43, 30, 'arroz chinos especial'),
(103, NULL, 'jugo de melon pequeño', 0.50, NULL, 1, 'jugo_de_melon_pequeño_45.webp', 45, 30, 'pequeño'),
(104, NULL, 'jugo de guayaba pequeño', 0.50, NULL, 1, 'jugo_de_guayaba_pequeño_9.webp', 45, 30, 'pequeño'),
(105, NULL, 'Arepas', 1.70, NULL, 1, 'Arepas_61.webp', 44, 30, 'pelua, domino, reina pepiada, catira');

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
(30, 'Administrador', 'Administrador', 'admin', '$2y$10$3e.zaoF/pfzrUIoAfzkGuuSUV8/4hsfybciQU/2XyUwxvuTELmYq.', '', '5804243487774');

--
-- Índices para tablas volcadas
--

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
-- Indices de la tabla `usuario`
--
ALTER TABLE `usuario`
  ADD PRIMARY KEY (`usuario_id`);

--
-- AUTO_INCREMENT de las tablas volcadas
--

--
-- AUTO_INCREMENT de la tabla `categoria`
--
ALTER TABLE `categoria`
  MODIFY `categoria_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=48;

--
-- AUTO_INCREMENT de la tabla `producto`
--
ALTER TABLE `producto`
  MODIFY `producto_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=106;

--
-- AUTO_INCREMENT de la tabla `usuario`
--
ALTER TABLE `usuario`
  MODIFY `usuario_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=31;

--
-- Restricciones para tablas volcadas
--

--
-- Filtros para la tabla `producto`
--
ALTER TABLE `producto`
  ADD CONSTRAINT `producto_ibfk_1` FOREIGN KEY (`categoria_id`) REFERENCES `categoria` (`categoria_id`),
  ADD CONSTRAINT `producto_ibfk_2` FOREIGN KEY (`usuario_id`) REFERENCES `usuario` (`usuario_id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
