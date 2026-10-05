-- phpMyAdmin SQL Dump
-- version 5.2.3
-- https://www.phpmyadmin.net/
--
-- Servidor: db
-- Tiempo de generación: 05-10-2026 a las 04:11:32
-- Versión del servidor: 10.11.19-MariaDB-ubu2204
-- Versión de PHP: 8.3.26

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Base de datos: `dw2_agenda`
--

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `alquileres`
--

CREATE TABLE `alquileres` (
  `id` int(11) NOT NULL,
  `alumno_id` int(11) NOT NULL,
  `cancha_id` int(11) NOT NULL,
  `fecha` date NOT NULL,
  `hora_inicio` time DEFAULT NULL,
  `hora_fin` time DEFAULT NULL,
  `horas` decimal(5,2) NOT NULL,
  `costo` decimal(12,2) NOT NULL,
  `cantidad_alumnos` int(11) NOT NULL DEFAULT 1,
  `recurrencia_id` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `alquileres`
--

INSERT INTO `alquileres` (`id`, `alumno_id`, `cancha_id`, `fecha`, `hora_inicio`, `hora_fin`, `horas`, `costo`, `cantidad_alumnos`, `recurrencia_id`) VALUES
(1, 3, 1, '2026-09-09', '14:00:00', '15:00:00', 1.00, 20000.00, 3, NULL),
(2, 7, 1, '2026-09-09', '15:00:00', '17:00:00', 2.00, 40000.00, 1, NULL),
(5, 4, 1, '2026-09-09', '13:00:00', '14:00:00', 1.00, 20000.00, 1, NULL),
(6, 8, 1, '2026-09-10', '13:30:00', '14:30:00', 1.00, 20000.00, 1, NULL),
(7, 6, 1, '2026-09-08', '08:00:00', '09:00:00', 1.00, 20000.00, 1, NULL),
(8, 5, 1, '2026-09-08', '09:00:00', '10:00:00', 1.00, 20000.00, 1, NULL),
(9, 9, 1, '2026-09-08', '13:00:00', '14:00:00', 1.00, 20000.00, 1, NULL),
(10, 10, 1, '2026-09-08', '14:00:00', '15:00:00', 1.00, 20000.00, 1, NULL),
(11, 7, 1, '2026-09-08', '15:00:00', '17:00:00', 2.00, 40000.00, 1, NULL),
(12, 4, 1, '2026-09-07', '13:00:00', '14:00:00', 1.00, 20000.00, 1, NULL),
(13, 3, 1, '2026-09-07', '14:00:00', '15:00:00', 1.00, 20000.00, 3, NULL),
(14, 7, 1, '2026-09-07', '15:00:00', '17:00:00', 2.00, 40000.00, 1, NULL),
(16, 11, 1, '2026-09-12', '08:00:00', '09:00:00', 1.00, 20000.00, 1, NULL),
(17, 4, 1, '2026-09-14', '13:00:00', '14:00:00', 1.00, 20000.00, 1, NULL),
(18, 3, 1, '2026-09-14', '14:00:00', '15:00:00', 1.00, 20000.00, 3, NULL),
(19, 7, 1, '2026-09-14', '15:00:00', '17:00:00', 2.00, 40000.00, 1, NULL),
(20, 6, 1, '2026-09-15', '08:00:00', '09:00:00', 1.00, 20000.00, 1, NULL),
(21, 5, 1, '2026-09-15', '09:00:00', '10:00:00', 1.00, 20000.00, 1, NULL),
(22, 5, 1, '2026-09-10', '09:00:00', '10:00:00', 1.00, 20000.00, 1, NULL),
(23, 6, 1, '2026-09-10', '08:00:00', '09:00:00', 1.00, 20000.00, 1, NULL),
(24, 4, 1, '2026-09-16', '13:00:00', '14:00:00', 1.00, 20000.00, 1, NULL),
(25, 3, 1, '2026-09-16', '14:00:00', '15:00:00', 1.00, 20000.00, 2, NULL),
(26, 12, 1, '2026-09-16', '15:00:00', '16:00:00', 1.00, 20000.00, 1, NULL),
(27, 9, 1, '2026-09-15', '14:00:00', '15:00:00', 1.00, 20000.00, 1, NULL),
(28, 4, 1, '2026-09-18', '13:30:00', '14:30:00', 1.00, 20000.00, 2, NULL),
(29, 6, 1, '2026-09-17', '08:00:00', '09:00:00', 1.00, 20000.00, 1, NULL),
(30, 5, 1, '2026-09-17', '09:00:00', '10:00:00', 1.00, 20000.00, 1, NULL),
(31, 9, 1, '2026-09-17', '14:00:00', '16:00:00', 2.00, 40000.00, 1, NULL),
(32, 12, 1, '2026-09-18', '16:00:00', '17:00:00', 1.00, 20000.00, 1, NULL),
(33, 5, 1, '2026-09-22', '09:00:00', '10:00:00', 1.00, 20000.00, 1, NULL),
(34, 3, 1, '2026-09-23', '13:30:00', '15:00:00', 1.50, 30000.00, 3, NULL),
(35, 12, 1, '2026-09-23', '15:00:00', '16:30:00', 1.50, 30000.00, 2, NULL),
(36, 6, 1, '2026-09-22', '08:00:00', '09:00:00', 1.00, 20000.00, 1, NULL),
(37, 5, 1, '2026-09-23', '09:00:00', '10:00:00', 1.00, 20000.00, 1, NULL),
(38, 9, 1, '2026-09-22', '13:00:00', '14:00:00', 1.00, 20000.00, 1, NULL),
(39, 10, 1, '2026-09-22', '14:00:00', '15:00:00', 1.00, 20000.00, 1, NULL),
(40, 4, 1, '2026-09-21', '13:00:00', '14:00:00', 1.00, 20000.00, 1, NULL),
(41, 3, 1, '2026-09-21', '14:00:00', '15:00:00', 1.00, 20000.00, 3, NULL),
(42, 9, 1, '2026-09-24', '15:00:00', '16:00:00', 1.00, 20000.00, 1, NULL),
(43, 10, 1, '2026-09-24', '16:00:00', '17:00:00', 1.00, 20000.00, 1, NULL),
(44, 8, 1, '2026-09-24', '13:00:00', '15:00:00', 2.00, 40000.00, 1, NULL),
(45, 6, 1, '2026-09-25', '09:00:00', '10:00:00', 1.00, 20000.00, 1, NULL),
(46, 4, 1, '2026-09-25', '13:00:00', '16:00:00', 3.00, 60000.00, 4, NULL),
(47, 11, 1, '2026-09-26', '11:00:00', '12:00:00', 1.00, 20000.00, 1, NULL),
(48, 9, 1, '2026-09-29', '13:00:00', '14:00:00', 1.00, 20000.00, 1, NULL),
(49, 10, 1, '2026-09-29', '14:00:00', '15:00:00', 1.00, 20000.00, 1, NULL),
(50, 7, 1, '2026-09-29', '15:00:00', '17:00:00', 2.00, 40000.00, 2, NULL),
(51, 6, 1, '2026-09-30', '08:00:00', '09:00:00', 1.00, 20000.00, 1, NULL),
(52, 4, 1, '2026-09-30', '13:00:00', '15:00:00', 2.00, 40000.00, 4, NULL),
(53, 7, 1, '2026-09-30', '15:00:00', '17:00:00', 2.00, 40000.00, 2, NULL),
(54, 8, 1, '2026-10-01', '13:00:00', '15:00:00', 2.00, 40000.00, 2, NULL),
(55, 7, 1, '2026-10-01', '15:00:00', '17:00:00', 2.00, 40000.00, 2, NULL),
(56, 4, 1, '2026-10-02', '13:00:00', '15:00:00', 2.00, 40000.00, 4, NULL),
(57, 7, 1, '2026-10-02', '15:00:00', '17:00:00', 2.00, 40000.00, 2, NULL);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `alquiler_alumnos`
--

CREATE TABLE `alquiler_alumnos` (
  `id` int(11) NOT NULL,
  `alquiler_id` int(11) NOT NULL,
  `alumno_id` int(11) NOT NULL,
  `importe` decimal(12,2) NOT NULL DEFAULT 0.00,
  `monto_pagado` decimal(12,2) NOT NULL DEFAULT 0.00,
  `estado_pago` enum('pendiente','pagado_parcial','pagado') NOT NULL DEFAULT 'pendiente',
  `medio_pago` enum('efectivo','transferencia','otro') DEFAULT NULL,
  `periodo` varchar(20) DEFAULT NULL,
  `asistencia` enum('programada','asistio','ausente') NOT NULL DEFAULT 'asistio',
  `importe_programado` decimal(12,2) NOT NULL DEFAULT 0.00
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `alquiler_alumnos`
--

INSERT INTO `alquiler_alumnos` (`id`, `alquiler_id`, `alumno_id`, `importe`, `monto_pagado`, `estado_pago`, `medio_pago`, `periodo`, `asistencia`, `importe_programado`) VALUES
(4, 2, 7, 200000.00, 0.00, 'pendiente', 'transferencia', NULL, 'asistio', 200000.00),
(12, 8, 5, 120000.00, 0.00, 'pagado_parcial', 'transferencia', NULL, 'asistio', 120000.00),
(23, 5, 4, 100000.00, 0.00, 'pendiente', 'efectivo', NULL, 'asistio', 100000.00),
(24, 12, 4, 100000.00, 0.00, 'pendiente', 'efectivo', NULL, 'asistio', 100000.00),
(28, 1, 3, 100000.00, 0.00, 'pagado_parcial', 'transferencia', NULL, 'asistio', 100000.00),
(29, 1, 1, 0.00, 0.00, 'pendiente', 'transferencia', NULL, 'asistio', 0.00),
(30, 1, 2, 100000.00, 0.00, 'pagado', 'transferencia', NULL, 'asistio', 100000.00),
(35, 10, 10, 100000.00, 100000.00, 'pagado', 'efectivo', NULL, 'asistio', 100000.00),
(36, 9, 9, 100000.00, 100000.00, 'pagado', 'efectivo', NULL, 'asistio', 100000.00),
(37, 13, 3, 100000.00, 0.00, 'pendiente', 'transferencia', NULL, 'asistio', 100000.00),
(38, 13, 1, 0.00, 0.00, 'pendiente', 'transferencia', NULL, 'asistio', 0.00),
(39, 13, 2, 100000.00, 0.00, 'pendiente', 'transferencia', NULL, 'asistio', 100000.00),
(40, 14, 7, 200000.00, 0.00, 'pendiente', 'transferencia', NULL, 'asistio', 200000.00),
(41, 7, 6, 100000.00, 0.00, 'pendiente', 'efectivo', NULL, 'asistio', 100000.00),
(43, 11, 7, 200000.00, 0.00, 'pendiente', 'transferencia', NULL, 'asistio', 200000.00),
(46, 16, 11, 100000.00, 0.00, 'pagado', 'transferencia', NULL, 'asistio', 100000.00),
(47, 17, 4, 100000.00, 0.00, 'pagado', NULL, NULL, 'asistio', 100000.00),
(51, 19, 7, 200000.00, 0.00, 'pagado_parcial', NULL, NULL, 'asistio', 200000.00),
(52, 20, 6, 100000.00, 0.00, 'pagado', NULL, NULL, 'asistio', 100000.00),
(53, 21, 5, 120000.00, 0.00, 'pagado_parcial', NULL, NULL, 'asistio', 120000.00),
(54, 22, 5, 100000.00, 0.00, 'pagado_parcial', NULL, NULL, 'asistio', 100000.00),
(55, 23, 6, 100000.00, 0.00, 'pagado_parcial', NULL, NULL, 'asistio', 100000.00),
(56, 6, 8, 100000.00, 0.00, 'pendiente', 'transferencia', NULL, 'asistio', 100000.00),
(57, 24, 4, 100000.00, 0.00, 'pagado', 'efectivo', NULL, 'asistio', 100000.00),
(58, 25, 3, 100000.00, 0.00, 'pagado_parcial', 'transferencia', NULL, 'asistio', 100000.00),
(59, 25, 2, 100000.00, 0.00, 'pagado', 'transferencia', NULL, 'asistio', 100000.00),
(60, 26, 12, 0.00, 0.00, 'pagado_parcial', 'efectivo', NULL, 'asistio', 0.00),
(62, 27, 9, 100000.00, 0.00, 'pagado', 'otro', NULL, 'asistio', 100000.00),
(63, 28, 4, 100000.00, 0.00, 'pagado', 'efectivo', NULL, 'asistio', 100000.00),
(64, 28, 2, 100000.00, 0.00, 'pagado', 'transferencia', NULL, 'asistio', 100000.00),
(65, 29, 6, 100000.00, 0.00, 'pagado', 'efectivo', NULL, 'asistio', 100000.00),
(67, 31, 9, 200000.00, 0.00, 'pagado', 'transferencia', NULL, 'asistio', 200000.00),
(70, 34, 3, 100000.00, 0.00, 'pagado_parcial', 'transferencia', NULL, 'asistio', 100000.00),
(71, 34, 1, 0.00, 0.00, 'pendiente', 'transferencia', NULL, 'asistio', 0.00),
(72, 34, 2, 100000.00, 100000.00, 'pagado', 'transferencia', NULL, 'asistio', 100000.00),
(73, 35, 12, 75000.00, 0.00, 'pagado_parcial', NULL, NULL, 'asistio', 75000.00),
(74, 35, 7, 200000.00, 0.00, 'pagado_parcial', NULL, NULL, 'asistio', 200000.00),
(75, 36, 6, 100000.00, 100000.00, 'pagado', NULL, NULL, 'asistio', 100000.00),
(76, 37, 5, 120000.00, 0.00, 'pagado_parcial', 'transferencia', NULL, 'asistio', 120000.00),
(77, 33, 5, 120000.00, 0.00, 'pagado_parcial', 'transferencia', NULL, 'asistio', 120000.00),
(79, 39, 10, 100000.00, 100000.00, 'pagado', 'efectivo', NULL, 'asistio', 100000.00),
(80, 38, 9, 100000.00, 100000.00, 'pagado', 'efectivo', NULL, 'asistio', 100000.00),
(81, 40, 4, 100000.00, 100000.00, 'pagado', 'efectivo', NULL, 'asistio', 100000.00),
(85, 41, 3, 100000.00, 0.00, 'pagado_parcial', 'transferencia', NULL, 'asistio', 100000.00),
(86, 41, 1, 0.00, 0.00, 'pendiente', 'transferencia', NULL, 'asistio', 0.00),
(87, 41, 2, 100000.00, 100000.00, 'pagado', 'transferencia', NULL, 'asistio', 100000.00),
(90, 42, 9, 100000.00, 100000.00, 'pagado', 'transferencia', NULL, 'asistio', 100000.00),
(91, 43, 10, 100000.00, 100000.00, 'pagado', 'transferencia', NULL, 'asistio', 100000.00),
(93, 44, 8, 200000.00, 0.00, 'pendiente', 'transferencia', NULL, 'asistio', 200000.00),
(94, 45, 6, 100000.00, 100000.00, 'pagado', 'efectivo', NULL, 'asistio', 100000.00),
(95, 46, 4, 100000.00, 100000.00, 'pagado', 'efectivo', NULL, 'asistio', 100000.00),
(96, 46, 3, 100000.00, 0.00, 'pendiente', 'transferencia', NULL, 'asistio', 100000.00),
(97, 46, 2, 100000.00, 100000.00, 'pagado', 'transferencia', NULL, 'asistio', 100000.00),
(98, 46, 12, 75000.00, 0.00, 'pendiente', 'transferencia', NULL, 'asistio', 75000.00),
(101, 30, 5, 120000.00, 0.00, 'pagado_parcial', 'transferencia', NULL, 'asistio', 120000.00),
(102, 32, 12, 75000.00, 0.00, 'pagado', 'transferencia', NULL, 'asistio', 75000.00),
(103, 18, 3, 100000.00, 100000.00, 'pagado', NULL, NULL, 'asistio', 100000.00),
(104, 18, 1, 0.00, 0.00, 'pagado_parcial', NULL, NULL, 'asistio', 0.00),
(105, 18, 2, 0.00, 0.00, 'pendiente', NULL, NULL, 'asistio', 0.00),
(106, 47, 11, 140000.00, 140000.00, 'pagado', 'transferencia', NULL, 'asistio', 140000.00),
(107, 48, 9, 100000.00, 100000.00, 'pagado', 'transferencia', NULL, 'asistio', 100000.00),
(111, 49, 10, 100000.00, 100000.00, 'pagado', 'transferencia', NULL, 'asistio', 100000.00),
(112, 50, 7, 100000.00, 0.00, 'pendiente', 'transferencia', NULL, 'asistio', 100000.00),
(113, 50, 12, 75000.00, 0.00, 'pendiente', 'transferencia', NULL, 'asistio', 75000.00),
(114, 51, 6, 100000.00, 100000.00, 'pagado', 'efectivo', NULL, 'asistio', 100000.00),
(115, 52, 4, 100000.00, 100000.00, 'pendiente', 'efectivo', NULL, 'asistio', 100000.00),
(116, 52, 1, 0.00, 0.00, 'pendiente', NULL, NULL, 'asistio', 0.00),
(117, 52, 2, 100000.00, 100000.00, 'pendiente', 'transferencia', NULL, 'asistio', 100000.00),
(118, 52, 3, 100000.00, 0.00, 'pendiente', NULL, NULL, 'asistio', 100000.00),
(121, 53, 7, 200000.00, 0.00, 'pendiente', 'transferencia', NULL, 'asistio', 200000.00),
(122, 53, 12, 75000.00, 0.00, 'pendiente', 'transferencia', NULL, 'asistio', 75000.00),
(123, 54, 8, 200000.00, 200000.00, 'pendiente', 'transferencia', NULL, 'asistio', 200000.00),
(124, 54, 13, 0.00, 0.00, 'pendiente', 'transferencia', NULL, 'asistio', 0.00),
(127, 55, 7, 200000.00, 0.00, 'pendiente', 'transferencia', NULL, 'asistio', 200000.00),
(128, 55, 12, 75000.00, 0.00, 'pendiente', 'transferencia', NULL, 'asistio', 75000.00),
(129, 56, 4, 100000.00, 100000.00, 'pagado', 'efectivo', NULL, 'asistio', 100000.00),
(130, 56, 1, 0.00, 0.00, 'pendiente', 'otro', NULL, 'asistio', 0.00),
(131, 56, 3, 100000.00, 0.00, 'pendiente', NULL, NULL, 'asistio', 100000.00),
(132, 56, 2, 100000.00, 100000.00, 'pagado', 'transferencia', NULL, 'asistio', 100000.00),
(133, 57, 7, 200000.00, 0.00, 'pendiente', 'transferencia', NULL, 'asistio', 200000.00),
(134, 57, 12, 75000.00, 0.00, 'pendiente', 'transferencia', NULL, 'asistio', 75000.00);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `alumnos`
--

CREATE TABLE `alumnos` (
  `id` int(11) NOT NULL,
  `nombre` varchar(100) NOT NULL,
  `apellido` varchar(100) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `alumnos`
--

INSERT INTO `alumnos` (`id`, `nombre`, `apellido`) VALUES
(1, 'owen', '.'),
(2, 'seba', '.'),
(3, 'gonza', '.'),
(4, 'dinka', '.'),
(5, 'lorena', '.'),
(6, 'fio', '.'),
(7, 'franco', 'trussy'),
(8, 'benja', '.'),
(9, 'gio', '.'),
(10, 'hermano de gio', '.'),
(11, 'lucas', '.'),
(12, 'Negro', 'zorilla'),
(13, 'arturo', ',');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `canchas`
--

CREATE TABLE `canchas` (
  `id` int(11) NOT NULL,
  `complejo_id` int(11) NOT NULL,
  `nombre` varchar(100) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `canchas`
--

INSERT INTO `canchas` (`id`, `complejo_id`, `nombre`) VALUES
(1, 1, 'cedros1'),
(2, 1, 'cedros2'),
(3, 1, 'cedros3');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `complejos`
--

CREATE TABLE `complejos` (
  `id` int(11) NOT NULL,
  `nombre` varchar(100) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `complejos`
--

INSERT INTO `complejos` (`id`, `nombre`) VALUES
(1, 'Complejo principal');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `configuracion`
--

CREATE TABLE `configuracion` (
  `clave` varchar(50) NOT NULL,
  `valor` decimal(12,2) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `configuracion`
--

INSERT INTO `configuracion` (`clave`, `valor`) VALUES
('precio_hora', 20000.00);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `pagos_cancha`
--

CREATE TABLE `pagos_cancha` (
  `id` int(11) NOT NULL,
  `fecha` date NOT NULL,
  `monto` decimal(12,2) NOT NULL,
  `medio_pago` enum('efectivo','transferencia','otro') NOT NULL DEFAULT 'efectivo',
  `observacion` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `pagos_cancha`
--

INSERT INTO `pagos_cancha` (`id`, `fecha`, `monto`, `medio_pago`, `observacion`) VALUES
(2, '2026-09-27', 1060000.00, 'transferencia', NULL);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `perfil_profesor`
--

CREATE TABLE `perfil_profesor` (
  `id` tinyint(4) NOT NULL,
  `nombre` varchar(120) NOT NULL DEFAULT '',
  `titulo` varchar(160) NOT NULL DEFAULT '',
  `descripcion` text NOT NULL,
  `telefono` varchar(40) NOT NULL DEFAULT '',
  `email` varchar(191) NOT NULL DEFAULT '',
  `instagram` varchar(100) NOT NULL DEFAULT '',
  `imagen` varchar(100) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `perfil_profesor`
--

INSERT INTO `perfil_profesor` (`id`, `nombre`, `titulo`, `descripcion`, `telefono`, `email`, `instagram`, `imagen`) VALUES
(1, 'Isaac Benitez', 'Entrenamiento de pádel', 'Mi nombre es Isaac y desde hace varios años me dedico al pádel, tanto en la competencia internacional como en la formación de jugadores jóvenes y amateurs.\r\n\r\n🏆 Trayectoria deportiva\r\n2017: Tercer puesto por naciones en el Mundial de Pádel de Menores – Málaga, España.\r\n\r\n2021: Campeón del mundo por naciones en el Mundial de Pádel de Menores – Torreón, México.\r\n\r\n2022: Subcampeón por naciones en el Panamericano – Camboriú, Brasil.\r\n\r\n👨‍🏫 Experiencia como entrenador\r\n2023: Integrante del plantel de profesores para la preselección del Mundial de Pádel de Menores – Paraguay.\r\n\r\nMás de tres años enseñando pádel a jugadores amateurs, transmitiendo técnica, disciplina y pasión por el deporte.\r\n\r\nFundador de una escuelita de pádel para niños de 8 a 12 años, fomentando el desarrollo deportivo desde edades tempranas.\r\n\r\n🎯 Enfoque y misión\r\nMi objetivo como entrenador es formar jugadores completos, que no solo dominen la técnica y la táctica, sino que también desarrollen valores como el respeto, el trabajo en equipo y la perseverancia.', '0984768234', 'isaacpadel23@gmail.com', 'isaac_benitez_22', '3bcad4bf1595901d53acc233e95be58f.jpg');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `sesiones_recurrentes`
--

CREATE TABLE `sesiones_recurrentes` (
  `id` int(11) NOT NULL,
  `nombre` varchar(100) NOT NULL,
  `cancha_id` int(11) NOT NULL,
  `dia_semana` tinyint(4) NOT NULL,
  `hora_inicio` time NOT NULL,
  `hora_fin` time NOT NULL,
  `importe_asistencia` decimal(12,2) NOT NULL DEFAULT 0.00,
  `activa` tinyint(1) NOT NULL DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `sesiones_recurrentes_alumnos`
--

CREATE TABLE `sesiones_recurrentes_alumnos` (
  `recurrencia_id` int(11) NOT NULL,
  `alumno_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `usuarios`
--

CREATE TABLE `usuarios` (
  `id` int(11) NOT NULL,
  `nombre` varchar(150) NOT NULL,
  `apellido` varchar(150) NOT NULL,
  `mail` varchar(150) NOT NULL,
  `contrasena` varchar(150) NOT NULL,
  `esadmin` int(11) NOT NULL,
  `rol` varchar(20) NOT NULL DEFAULT 'usuario',
  `alumno_id` int(11) DEFAULT NULL,
  `doc` varchar(150) DEFAULT NULL,
  `telefono` varchar(150) NOT NULL,
  `direccion` text DEFAULT NULL,
  `fenac` date DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `usuarios`
--

INSERT INTO `usuarios` (`id`, `nombre`, `apellido`, `mail`, `contrasena`, `esadmin`, `rol`, `alumno_id`, `doc`, `telefono`, `direccion`, `fenac`) VALUES
(1, 'isaac', 'benitez', 'isaac@gmail.com', '$2y$10$D9yiGPM8nwZtXTsSTmdhau8ZTx1Kwh1ekXMUl7h8hSZxgEYSwR4BO', 1, 'admin', NULL, '456789', '6789876342', 'camby', '2017-09-11'),
(2, 'Principal', 'Administrador', 'admin@padel.local', '$2y$10$occhYljBpdtgKwg.trM2OuQiSJAvzTdmErb1npxh40tY28FusdhH6', 1, 'admin', NULL, NULL, '', NULL, NULL),
(3, 'seba', 'seco', 'seba@gmail.com', '$2y$10$bTbUg.AQ79./auLp3LFGleZmGEfRcN2Epk0I.f1UxU/hugez3rdby', 0, 'usuario', 2, '', '', '', NULL);

--
-- Índices para tablas volcadas
--

--
-- Indices de la tabla `alquileres`
--
ALTER TABLE `alquileres`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uq_alquileres_recurrencia_fecha` (`recurrencia_id`,`fecha`),
  ADD KEY `fk_alquileres_alumno` (`alumno_id`),
  ADD KEY `fk_alquileres_cancha` (`cancha_id`);

--
-- Indices de la tabla `alquiler_alumnos`
--
ALTER TABLE `alquiler_alumnos`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uq_alquiler_alumno` (`alquiler_id`,`alumno_id`),
  ADD KEY `fk_alquiler_alumnos_alumno` (`alumno_id`);

--
-- Indices de la tabla `alumnos`
--
ALTER TABLE `alumnos`
  ADD PRIMARY KEY (`id`);

--
-- Indices de la tabla `canchas`
--
ALTER TABLE `canchas`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uq_canchas_complejo_nombre` (`complejo_id`,`nombre`);

--
-- Indices de la tabla `complejos`
--
ALTER TABLE `complejos`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uq_complejos_nombre` (`nombre`);

--
-- Indices de la tabla `configuracion`
--
ALTER TABLE `configuracion`
  ADD PRIMARY KEY (`clave`);

--
-- Indices de la tabla `pagos_cancha`
--
ALTER TABLE `pagos_cancha`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_pagos_cancha_fecha` (`fecha`);

--
-- Indices de la tabla `perfil_profesor`
--
ALTER TABLE `perfil_profesor`
  ADD PRIMARY KEY (`id`);

--
-- Indices de la tabla `sesiones_recurrentes`
--
ALTER TABLE `sesiones_recurrentes`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_recurrentes_dia` (`dia_semana`,`activa`),
  ADD KEY `fk_recurrentes_cancha` (`cancha_id`);

--
-- Indices de la tabla `sesiones_recurrentes_alumnos`
--
ALTER TABLE `sesiones_recurrentes_alumnos`
  ADD PRIMARY KEY (`recurrencia_id`,`alumno_id`),
  ADD KEY `fk_recurrentes_alumnos_alumno` (`alumno_id`);

--
-- Indices de la tabla `usuarios`
--
ALTER TABLE `usuarios`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `mail_idx` (`mail`),
  ADD UNIQUE KEY `uq_usuarios_alumno` (`alumno_id`);

--
-- AUTO_INCREMENT de las tablas volcadas
--

--
-- AUTO_INCREMENT de la tabla `alquileres`
--
ALTER TABLE `alquileres`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=58;

--
-- AUTO_INCREMENT de la tabla `alquiler_alumnos`
--
ALTER TABLE `alquiler_alumnos`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=135;

--
-- AUTO_INCREMENT de la tabla `alumnos`
--
ALTER TABLE `alumnos`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=14;

--
-- AUTO_INCREMENT de la tabla `canchas`
--
ALTER TABLE `canchas`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT de la tabla `complejos`
--
ALTER TABLE `complejos`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT de la tabla `pagos_cancha`
--
ALTER TABLE `pagos_cancha`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT de la tabla `sesiones_recurrentes`
--
ALTER TABLE `sesiones_recurrentes`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `usuarios`
--
ALTER TABLE `usuarios`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- Restricciones para tablas volcadas
--

--
-- Filtros para la tabla `alquileres`
--
ALTER TABLE `alquileres`
  ADD CONSTRAINT `fk_alquileres_alumno` FOREIGN KEY (`alumno_id`) REFERENCES `alumnos` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_alquileres_cancha` FOREIGN KEY (`cancha_id`) REFERENCES `canchas` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_alquileres_recurrencia` FOREIGN KEY (`recurrencia_id`) REFERENCES `sesiones_recurrentes` (`id`) ON DELETE SET NULL ON UPDATE CASCADE;

--
-- Filtros para la tabla `alquiler_alumnos`
--
ALTER TABLE `alquiler_alumnos`
  ADD CONSTRAINT `fk_alquiler_alumnos_alquiler` FOREIGN KEY (`alquiler_id`) REFERENCES `alquileres` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_alquiler_alumnos_alumno` FOREIGN KEY (`alumno_id`) REFERENCES `alumnos` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Filtros para la tabla `canchas`
--
ALTER TABLE `canchas`
  ADD CONSTRAINT `fk_canchas_complejo` FOREIGN KEY (`complejo_id`) REFERENCES `complejos` (`id`) ON UPDATE CASCADE;

--
-- Filtros para la tabla `sesiones_recurrentes`
--
ALTER TABLE `sesiones_recurrentes`
  ADD CONSTRAINT `fk_recurrentes_cancha` FOREIGN KEY (`cancha_id`) REFERENCES `canchas` (`id`) ON UPDATE CASCADE;

--
-- Filtros para la tabla `sesiones_recurrentes_alumnos`
--
ALTER TABLE `sesiones_recurrentes_alumnos`
  ADD CONSTRAINT `fk_recurrentes_alumnos_alumno` FOREIGN KEY (`alumno_id`) REFERENCES `alumnos` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_recurrentes_alumnos_recurrencia` FOREIGN KEY (`recurrencia_id`) REFERENCES `sesiones_recurrentes` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Filtros para la tabla `usuarios`
--
ALTER TABLE `usuarios`
  ADD CONSTRAINT `fk_usuarios_alumno` FOREIGN KEY (`alumno_id`) REFERENCES `alumnos` (`id`) ON DELETE SET NULL ON UPDATE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
