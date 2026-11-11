-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Servidor: 127.0.0.1
-- Tiempo de generación: 11-11-2026 a las 18:29:36
-- Versión del servidor: 10.4.32-MariaDB
-- Versión de PHP: 8.0.30

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Base de datos: `hospital_de_clinicas`
--

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `acompañante`
--

CREATE TABLE `acompañante` (
  `ID_Acompañante` int(11) NOT NULL,
  `Nombre` varchar(100) NOT NULL,
  `Documento` varchar(20) NOT NULL,
  `Telefono` varchar(20) NOT NULL,
  `Rol` varchar(100) NOT NULL,
  `ID_Traslado` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `acompañante`
--

INSERT INTO `acompañante` (`ID_Acompañante`, `Nombre`, `Documento`, `Telefono`, `Rol`, `ID_Traslado`) VALUES
(5, 'lola', '', '', 'Acompañante', 4),
(8, 'lola', '', '', 'Acompañante', 6),
(9, 'Pablo', '', '', 'Acompañante', 7),
(10, 'Pablo', '', '', 'Acompañante', 8);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `ambulancia`
--

CREATE TABLE `ambulancia` (
  `ID_ambulancia` int(11) NOT NULL,
  `Estado` varchar(50) NOT NULL,
  `Año` int(11) NOT NULL,
  `Modelo` varchar(100) NOT NULL,
  `Matricula` varchar(30) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `ambulancia`
--

INSERT INTO `ambulancia` (`ID_ambulancia`, `Estado`, `Año`, `Modelo`, `Matricula`) VALUES
(1, 'activa', 2021, '2323', '12312');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `conductor`
--

CREATE TABLE `conductor` (
  `ID_Conductor` int(11) NOT NULL,
  `Nombre` varchar(100) NOT NULL,
  `Documento` varchar(20) NOT NULL,
  `Licencia` varchar(50) NOT NULL,
  `Telefono` varchar(20) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `conductor`
--

INSERT INTO `conductor` (`ID_Conductor`, `Nombre`, `Documento`, `Licencia`, `Telefono`) VALUES
(1, 'Juan', 'Enrique', '23123321', '31231231');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `documento`
--

CREATE TABLE `documento` (
  `Id_Documento` int(11) NOT NULL,
  `Titulo` varchar(150) NOT NULL,
  `Tipo` varchar(50) NOT NULL,
  `Url_Archivo` varchar(255) NOT NULL,
  `QR_Codigo` varchar(255) NOT NULL,
  `Descripcion` varchar(255) NOT NULL,
  `F_Creacion` date NOT NULL,
  `ID_Paciente` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `elementos_traslados`
--

CREATE TABLE `elementos_traslados` (
  `ID_elemento` int(11) NOT NULL,
  `Tipo` varchar(100) NOT NULL,
  `Detalle` varchar(255) NOT NULL,
  `Descripcion` varchar(255) NOT NULL,
  `Paciente_equipo_insumos` varchar(100) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `encuesta`
--

CREATE TABLE `encuesta` (
  `ID_Encuesta` int(11) NOT NULL,
  `Titulo` varchar(150) NOT NULL,
  `Descripcion` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `encuesta`
--

INSERT INTO `encuesta` (`ID_Encuesta`, `Titulo`, `Descripcion`) VALUES
(1, 'Encuesta de satisfacción', 'Encuesta sobre la atención recibida en el Hospital de Clínicas');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `funcionario`
--

CREATE TABLE `funcionario` (
  `ID_Funcionario` int(11) NOT NULL,
  `n_usuario` varchar(100) NOT NULL,
  `contrasenia` varchar(100) NOT NULL,
  `c_electronico` varchar(100) NOT NULL,
  `direccion` varchar(150) NOT NULL,
  `f_nacimineto` date NOT NULL,
  `cedula` varchar(20) NOT NULL,
  `n_telefono` varchar(20) NOT NULL,
  `f_Ingreso` date NOT NULL,
  `estado` varchar(50) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `funcionario`
--

INSERT INTO `funcionario` (`ID_Funcionario`, `n_usuario`, `contrasenia`, `c_electronico`, `direccion`, `f_nacimineto`, `cedula`, `n_telefono`, `f_Ingreso`, `estado`) VALUES
(1, 'Diego', '$2y$10$Iy8VFNIQ0hf8HtVMvSB4RuVUOHqimB6Zi2d.g0eWK10zztw6PJFxG', 'diego@gmail.com', 'paysandu', '2003-06-11', '21232131', '099232332', '2026-08-31', 'activo'),
(2, 'Thiago Diaz', '$2y$10$D2XHBzrvGcJWpjOpP/9p.uciwONReyqFqtfXuEX0e59NouVfffELK', 'Thiago@gmail.com', 'av roldan', '2008-12-10', '57792576', '098765432', '2025-10-10', 'activo'),
(4, 'Diegol', '$2y$10$jwKfMVoxvZziTN.jQVy/GeMTA3vPDkFoT6cc8Bx6/y0fJSBf4fxWS', 'diego12@gmail.com', 'urguay', '2026-09-01', '12121212', '098212312', '2026-09-01', 'Activo'),
(5, 'DIGOLORITO', '$2y$10$9i7hbN9WC6VJ7OfCY6MwOeiKQfRUXbAGnuiWk0lwGsiCXBoH/UDgK', 'diegolo@gmail.com', 'Panama', '2026-09-12', '23213333', '00394445', '2026-09-05', 'Inactivo'),
(6, 'DIGOLORITO1', '$2y$10$5g5xSiZLXgRDIpNfgBYLsO1XiZAA70EPDwxw0NDoc9ov/TIbxGZye', 'diegolo32312@gmail.com', 'Panama', '2026-09-12', '13121212', '00394445', '2026-09-05', 'Inactivo'),
(7, 'Diego Gonzalez', '$2y$10$QT8BDNelGK9bYJZ3gFzUv.ODNNHXW3M2l6fGFmYXJyLJk/KAi531a', 'diego0098@gmail.com', 'Paysandu', '2026-10-03', '12345678', '123456789', '2026-10-03', 'Activo'),
(8, 'Prueba', '$2y$10$vwjgbTIpTTFxu1qyJfTbWufq3v8dVYwFz/vG8pcQKHPAEQ5eRNWXW', 'prueba@gmail.com', 'Uruguay', '2026-10-21', '11111111', '09999999', '2026-10-21', 'Activo'),
(9, 'rodrigo', '$2y$10$FY5yFA6IaWYCsrADafdsR.cYxCde/BPfUcNhb9z0AQMN.6cxPlG9S', 'diegofransisco2021467@gmail.com', 'zoriila 1243', '2002-02-28', '57631332', '099559323', '2026-11-03', 'Activo');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `muestra_paciente`
--

CREATE TABLE `muestra_paciente` (
  `ID_Muestra` int(11) NOT NULL,
  `Nombre` varchar(100) NOT NULL,
  `Apellido` varchar(100) NOT NULL,
  `Cedula` varchar(20) NOT NULL,
  `Muestra` varchar(255) NOT NULL,
  `Fecha_Muestra` date NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `muestra_paciente`
--

INSERT INTO `muestra_paciente` (`ID_Muestra`, `Nombre`, `Apellido`, `Cedula`, `Muestra`, `Fecha_Muestra`) VALUES
(1, 'Diego', 'González', '22222222', 'Sangre', '2026-10-20'),
(2, 'Thiago', 'Diaz', '12334562', 'Sangre', '2026-10-20');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `paciente`
--

CREATE TABLE `paciente` (
  `ID_Paciente` int(11) NOT NULL,
  `F_Nacimiento` date NOT NULL,
  `Cedula` varchar(20) NOT NULL,
  `Nombre` varchar(100) NOT NULL,
  `Apellido` varchar(100) NOT NULL,
  `Email` varchar(100) NOT NULL,
  `ID_Encuesta` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `paciente`
--

INSERT INTO `paciente` (`ID_Paciente`, `F_Nacimiento`, `Cedula`, `Nombre`, `Apellido`, `Email`, `ID_Encuesta`) VALUES
(1, '2026-10-04', '12345678', 'Diego', 'Gonzalez', 'diego0098@gmail.com', 1);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `paciente_traslado`
--

CREATE TABLE `paciente_traslado` (
  `ID_Paciente` int(11) NOT NULL,
  `ID_Traslado` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `paciente_traslado`
--

INSERT INTO `paciente_traslado` (`ID_Paciente`, `ID_Traslado`) VALUES
(1, 4),
(1, 6),
(1, 7),
(1, 8);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `qr`
--

CREATE TABLE `qr` (
  `id` int(11) NOT NULL,
  `descripcion` varchar(255) NOT NULL,
  `Id_Documento` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `respuesta`
--

CREATE TABLE `respuesta` (
  `ID_Respuesta` int(11) NOT NULL,
  `Respuesta_Texto` varchar(255) NOT NULL,
  `Clasificacion` varchar(100) NOT NULL,
  `Fecha` date NOT NULL,
  `ID_Encuesta` int(11) NOT NULL,
  `ID_Envio` varchar(32) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `respuesta`
--

INSERT INTO `respuesta` (`ID_Respuesta`, `Respuesta_Texto`, `Clasificacion`, `Fecha`, `ID_Encuesta`, `ID_Envio`) VALUES
(3, 'emergencia', 'Servicio utilizado', '2026-09-05', 1, NULL),
(4, 'buena', 'Atención recibida', '2026-09-05', 1, NULL),
(5, 'muy_largo', 'Tiempo de espera', '2026-09-05', 1, NULL),
(6, 'muy_buena', 'Instalaciones', '2026-09-05', 1, NULL),
(7, 'no', 'Recomendaría el hospital', '2026-09-05', 1, NULL),
(8, 'Dberian ser mas rapios', 'Comentarios', '2026-09-05', 1, NULL),
(9, 'emergencia', 'Servicio utilizado', '2026-09-05', 1, NULL),
(10, 'excelente', 'Atención recibida', '2026-09-05', 1, NULL),
(11, 'muy_corto', 'Tiempo de espera', '2026-09-05', 1, NULL),
(12, 'regular', 'Instalaciones', '2026-09-05', 1, NULL),
(13, 'no', 'Recomendaría el hospital', '2026-09-05', 1, NULL),
(14, 'MUY BUENO', 'Comentarios', '2026-09-05', 1, NULL),
(15, 'emergencia', 'Servicio utilizado', '2026-09-05', 1, '29dc293284e282cb1690570b6bc7a519'),
(16, 'excelente', 'Atención recibida', '2026-09-05', 1, '29dc293284e282cb1690570b6bc7a519'),
(17, 'muy_corto', 'Tiempo de espera', '2026-09-05', 1, '29dc293284e282cb1690570b6bc7a519'),
(18, 'muy_buena', 'Instalaciones', '2026-09-05', 1, '29dc293284e282cb1690570b6bc7a519'),
(19, 'si', 'Recomendaría el hospital', '2026-09-05', 1, '29dc293284e282cb1690570b6bc7a519'),
(20, 'La verdad es que estuvo feo xd', 'Comentarios', '2026-09-05', 1, '29dc293284e282cb1690570b6bc7a519');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `ruta`
--

CREATE TABLE `ruta` (
  `ID_ruta` int(11) NOT NULL,
  `Descripcion` varchar(255) NOT NULL,
  `Tipo` varchar(50) NOT NULL,
  `Local_Nacional` varchar(50) NOT NULL,
  `ID_Traslado` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `ruta`
--

INSERT INTO `ruta` (`ID_ruta`, `Descripcion`, `Tipo`, `Local_Nacional`, `ID_Traslado`) VALUES
(9, 'Montevideo', 'Origen', 'Local', 4),
(10, 'Hostpital de clinicas', 'Destino', 'Local', 4),
(15, 'Latitud -34.892901, longitud -56.153097', 'Origen', 'Local', 6),
(16, 'Tapes, Montevideo, Uruguay', 'Destino', 'Local', 6),
(17, 'Avenida Italia 2886, Montevideo, Uruguay', 'Origen', 'Local', 7),
(18, 'Caramurú 5939, Montevideo, Uruguay', 'Destino', 'Local', 7),
(19, 'Avenida Italia 2886, Montevideo, Uruguay', 'Origen', 'Local', 8),
(20, 'La Blanqueada, Montevideo, Uruguay', 'Destino', 'Local', 8);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `traslado`
--

CREATE TABLE `traslado` (
  `ID_Traslado` int(11) NOT NULL,
  `Fecha_solicitud` date NOT NULL,
  `Hora_Salida` time NOT NULL,
  `Hora_llegada_estimada` time NOT NULL,
  `Hora_llegada_efectiva` time NOT NULL,
  `Estado` varchar(50) NOT NULL,
  `Observaciones` varchar(255) NOT NULL,
  `ID_ambulancia` int(11) NOT NULL,
  `ID_Conductor` int(11) NOT NULL,
  `ID_Funcionario` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `traslado`
--

INSERT INTO `traslado` (`ID_Traslado`, `Fecha_solicitud`, `Hora_Salida`, `Hora_llegada_estimada`, `Hora_llegada_efectiva`, `Estado`, `Observaciones`, `ID_ambulancia`, `ID_Conductor`, `ID_Funcionario`) VALUES
(4, '2026-10-07', '23:22:00', '00:00:00', '00:00:00', 'Finalizado', '', 1, 1, 7),
(6, '2026-10-08', '11:12:00', '11:10:00', '11:10:00', 'Pendiente', '', 1, 1, 7),
(7, '2026-10-14', '10:26:00', '11:31:00', '00:00:00', 'En camino', 'La persona se descompuso', 1, 1, 7),
(8, '2026-11-11', '08:01:00', '08:04:00', '00:00:00', 'En camino', '', 1, 1, 8);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `traslado_elemento`
--

CREATE TABLE `traslado_elemento` (
  `ID_Traslado` int(11) NOT NULL,
  `ID_elemento` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `ubicacion_traslado`
--

CREATE TABLE `ubicacion_traslado` (
  `id_ubicacion` int(11) NOT NULL,
  `latitud` decimal(10,7) NOT NULL,
  `longuitod` decimal(10,7) NOT NULL,
  `fecha_hora` datetime NOT NULL DEFAULT current_timestamp(),
  `id_traslado` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Índices para tablas volcadas
--

--
-- Indices de la tabla `acompañante`
--
ALTER TABLE `acompañante`
  ADD PRIMARY KEY (`ID_Acompañante`),
  ADD KEY `ID_Traslado` (`ID_Traslado`);

--
-- Indices de la tabla `ambulancia`
--
ALTER TABLE `ambulancia`
  ADD PRIMARY KEY (`ID_ambulancia`);

--
-- Indices de la tabla `conductor`
--
ALTER TABLE `conductor`
  ADD PRIMARY KEY (`ID_Conductor`);

--
-- Indices de la tabla `documento`
--
ALTER TABLE `documento`
  ADD PRIMARY KEY (`Id_Documento`),
  ADD KEY `ID_Paciente` (`ID_Paciente`);

--
-- Indices de la tabla `elementos_traslados`
--
ALTER TABLE `elementos_traslados`
  ADD PRIMARY KEY (`ID_elemento`);

--
-- Indices de la tabla `encuesta`
--
ALTER TABLE `encuesta`
  ADD PRIMARY KEY (`ID_Encuesta`);

--
-- Indices de la tabla `funcionario`
--
ALTER TABLE `funcionario`
  ADD PRIMARY KEY (`ID_Funcionario`);

--
-- Indices de la tabla `muestra_paciente`
--
ALTER TABLE `muestra_paciente`
  ADD PRIMARY KEY (`ID_Muestra`);

--
-- Indices de la tabla `paciente`
--
ALTER TABLE `paciente`
  ADD PRIMARY KEY (`ID_Paciente`),
  ADD KEY `ID_Encuesta` (`ID_Encuesta`);

--
-- Indices de la tabla `paciente_traslado`
--
ALTER TABLE `paciente_traslado`
  ADD PRIMARY KEY (`ID_Paciente`,`ID_Traslado`),
  ADD KEY `ID_Traslado` (`ID_Traslado`);

--
-- Indices de la tabla `qr`
--
ALTER TABLE `qr`
  ADD PRIMARY KEY (`id`),
  ADD KEY `Id_Documento` (`Id_Documento`);

--
-- Indices de la tabla `respuesta`
--
ALTER TABLE `respuesta`
  ADD PRIMARY KEY (`ID_Respuesta`),
  ADD KEY `ID_Encuesta` (`ID_Encuesta`);

--
-- Indices de la tabla `ruta`
--
ALTER TABLE `ruta`
  ADD PRIMARY KEY (`ID_ruta`),
  ADD KEY `ID_Traslado` (`ID_Traslado`);

--
-- Indices de la tabla `traslado`
--
ALTER TABLE `traslado`
  ADD PRIMARY KEY (`ID_Traslado`),
  ADD KEY `ID_ambulancia` (`ID_ambulancia`),
  ADD KEY `ID_Conductor` (`ID_Conductor`),
  ADD KEY `ID_Funcionario` (`ID_Funcionario`);

--
-- Indices de la tabla `traslado_elemento`
--
ALTER TABLE `traslado_elemento`
  ADD PRIMARY KEY (`ID_Traslado`,`ID_elemento`),
  ADD KEY `ID_elemento` (`ID_elemento`);

--
-- Indices de la tabla `ubicacion_traslado`
--
ALTER TABLE `ubicacion_traslado`
  ADD PRIMARY KEY (`id_ubicacion`),
  ADD KEY `id_traslado` (`id_traslado`);

--
-- AUTO_INCREMENT de las tablas volcadas
--

--
-- AUTO_INCREMENT de la tabla `respuesta`
--
ALTER TABLE `respuesta`
  MODIFY `ID_Respuesta` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `acompañante`
--
ALTER TABLE `acompañante`
  MODIFY `ID_Acompañante` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT de la tabla `ambulancia`
--
ALTER TABLE `ambulancia`
  MODIFY `ID_ambulancia` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT de la tabla `conductor`
--
ALTER TABLE `conductor`
  MODIFY `ID_Conductor` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT de la tabla `documento`
--
ALTER TABLE `documento`
  MODIFY `Id_Documento` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `elementos_traslados`
--
ALTER TABLE `elementos_traslados`
  MODIFY `ID_elemento` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `encuesta`
--
ALTER TABLE `encuesta`
  MODIFY `ID_Encuesta` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT de la tabla `funcionario`
--
ALTER TABLE `funcionario`
  MODIFY `ID_Funcionario` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- AUTO_INCREMENT de la tabla `muestra_paciente`
--
ALTER TABLE `muestra_paciente`
  MODIFY `ID_Muestra` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT de la tabla `paciente`
--
ALTER TABLE `paciente`
  MODIFY `ID_Paciente` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `ruta`
--
ALTER TABLE `ruta`
  MODIFY `ID_ruta` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `traslado`
--
ALTER TABLE `traslado`
  MODIFY `ID_Traslado` int(11) NOT NULL AUTO_INCREMENT;

COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
