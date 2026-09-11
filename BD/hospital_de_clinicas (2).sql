
SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

CREATE TABLE `acompañante` (
  `ID_Acompañante` int(11) NOT NULL,
  `Nombre` varchar(100) NOT NULL,
  `Documento` varchar(20) NOT NULL,
  `Telefono` varchar(20) NOT NULL,
  `Rol` varchar(100) NOT NULL,
  `ID_Traslado` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE `ambulancia` (
  `ID_ambulancia` int(11) NOT NULL,
  `Estado` varchar(50) NOT NULL,
  `Año` int(11) NOT NULL,
  `Modelo` varchar(100) NOT NULL,
  `Matricula` varchar(30) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE `conductor` (
  `ID_Conductor` int(11) NOT NULL,
  `Nombre` varchar(100) NOT NULL,
  `Documento` varchar(20) NOT NULL,
  `Licencia` varchar(50) NOT NULL,
  `Telefono` varchar(20) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

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

CREATE TABLE `elementos_traslados` (
  `ID_elemento` int(11) NOT NULL,
  `Tipo` varchar(100) NOT NULL,
  `Detalle` varchar(255) NOT NULL,
  `Descripcion` varchar(255) NOT NULL,
  `Paciente_equipo_insumos` varchar(100) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE `encuesta` (
  `ID_Encuesta` int(11) NOT NULL,
  `Titulo` varchar(150) NOT NULL,
  `Descripcion` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

INSERT INTO `encuesta` (`ID_Encuesta`, `Titulo`, `Descripcion`) VALUES
(1, 'Encuesta de satisfacción', 'Encuesta sobre la atención recibida en el Hospital de Clínicas');

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

INSERT INTO `funcionario` (`ID_Funcionario`, `n_usuario`, `contrasenia`, `c_electronico`, `direccion`, `f_nacimineto`, `cedula`, `n_telefono`, `f_Ingreso`, `estado`) VALUES
(1, 'Diego', '$2y$10$Iy8VFNIQ0hf8HtVMvSB4RuVUOHqimB6Zi2d.g0eWK10zztw6PJFxG', 'diego@gmail.com', 'paysandu', '2003-06-11', '21232131', '099232332', '2026-08-31', 'activo'),
(2, 'Thiago Diaz', '$2y$10$D2XHBzrvGcJWpjOpP/9p.uciwONReyqFqtfXuEX0e59NouVfffELK', 'Thiago@gmail.com', 'av roldan', '2008-12-10', '57792576', '098765432', '2025-10-10', 'activo'),
(4, 'Diegol', '$2y$10$jwKfMVoxvZziTN.jQVy/GeMTA3vPDkFoT6cc8Bx6/y0fJSBf4fxWS', 'diego12@gmail.com', 'urguay', '2026-09-01', '12121212', '098212312', '2026-09-01', 'Activo'),
(5, 'DIGOLORITO', '$2y$10$9i7hbN9WC6VJ7OfCY6MwOeiKQfRUXbAGnuiWk0lwGsiCXBoH/UDgK', 'diegolo@gmail.com', 'Panama', '2026-09-12', '23213333', '00394445', '2026-09-05', 'Inactivo'),
(6, 'DIGOLORITO1', '$2y$10$5g5xSiZLXgRDIpNfgBYLsO1XiZAA70EPDwxw0NDoc9ov/TIbxGZye', 'diegolo32312@gmail.com', 'Panama', '2026-09-12', '13121212', '00394445', '2026-09-05', 'Inactivo');

CREATE TABLE `paciente` (
  `ID_Paciente` int(11) NOT NULL,
  `F_Nacimiento` date NOT NULL,
  `Cedula` varchar(20) NOT NULL,
  `Nombre` varchar(100) NOT NULL,
  `Apellido` varchar(100) NOT NULL,
  `Email` varchar(100) NOT NULL,
  `ID_Encuesta` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE `paciente_traslado` (
  `ID_Paciente` int(11) NOT NULL,
  `ID_Traslado` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE `qr` (
  `id` int(11) NOT NULL,
  `descripcion` varchar(255) NOT NULL,
  `Id_Documento` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE `respuesta` (
  `ID_Respuesta` int(11) NOT NULL,
  `Respuesta_Texto` varchar(255) NOT NULL,
  `Clasificacion` varchar(100) NOT NULL,
  `Fecha` date NOT NULL,
  `ID_Encuesta` int(11) NOT NULL,
  `ID_Envio` varchar(32) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

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
(20, 'La verdad es que estuvo feo xd', 'Comentarios', '2026-09-05', 1, '29dc293284e282cb1690570b6bc7a519'),
(21, 'emergencia', 'Servicio utilizado', '2026-09-06', 1, '17af313b3bf4b2bd40323d0a83d4ddcb'),
(22, 'excelente', 'Atención recibida', '2026-09-06', 1, '17af313b3bf4b2bd40323d0a83d4ddcb'),
(23, 'muy_corto', 'Tiempo de espera', '2026-09-06', 1, '17af313b3bf4b2bd40323d0a83d4ddcb'),
(24, 'excelente', 'Instalaciones', '2026-09-06', 1, '17af313b3bf4b2bd40323d0a83d4ddcb'),
(25, 'si', 'Recomendaría el hospital', '2026-09-06', 1, '17af313b3bf4b2bd40323d0a83d4ddcb'),
(26, 'Mal', 'Comentarios', '2026-09-06', 1, '17af313b3bf4b2bd40323d0a83d4ddcb'),
(27, 'emergencia', 'Servicio utilizado', '2026-09-07', 1, '81860fe82723e6e3b650db92699d90dc'),
(28, 'excelente', 'Atención recibida', '2026-09-07', 1, '81860fe82723e6e3b650db92699d90dc'),
(29, 'muy_corto', 'Tiempo de espera', '2026-09-07', 1, '81860fe82723e6e3b650db92699d90dc'),
(30, 'excelente', 'Instalaciones', '2026-09-07', 1, '81860fe82723e6e3b650db92699d90dc'),
(31, 'si', 'Recomendaría el hospital', '2026-09-07', 1, '81860fe82723e6e3b650db92699d90dc'),
(32, 'hola xd dani', 'Comentarios', '2026-09-07', 1, '81860fe82723e6e3b650db92699d90dc'),
(33, 'estudios', 'Servicio utilizado', '2026-09-10', 1, '22c05583a03532ed31561eb6c43da47a'),
(34, 'excelente', 'Atención recibida', '2026-09-10', 1, '22c05583a03532ed31561eb6c43da47a'),
(35, 'largo', 'Tiempo de espera', '2026-09-10', 1, '22c05583a03532ed31561eb6c43da47a'),
(36, 'buena', 'Instalaciones', '2026-09-10', 1, '22c05583a03532ed31561eb6c43da47a'),
(37, 'si', 'Recomendaría el hospital', '2026-09-10', 1, '22c05583a03532ed31561eb6c43da47a'),
(38, 'Muy bueno pero hay que esperar mucho', 'Comentarios', '2026-09-10', 1, '22c05583a03532ed31561eb6c43da47a'),
(39, 'consulta', 'Servicio utilizado', '2026-09-10', 1, '576539c2eb9919f60b7ff34bc1542ecf'),
(40, 'excelente', 'Atención recibida', '2026-09-10', 1, '576539c2eb9919f60b7ff34bc1542ecf'),
(41, 'muy_corto', 'Tiempo de espera', '2026-09-10', 1, '576539c2eb9919f60b7ff34bc1542ecf'),
(42, 'excelente', 'Instalaciones', '2026-09-10', 1, '576539c2eb9919f60b7ff34bc1542ecf'),
(43, 'si', 'Recomendaría el hospital', '2026-09-10', 1, '576539c2eb9919f60b7ff34bc1542ecf'),
(44, 'si', 'Comentarios', '2026-09-10', 1, '576539c2eb9919f60b7ff34bc1542ecf'),
(45, 'consulta', 'Servicio utilizado', '2026-09-10', 1, 'b4e2cb49919a70e347ca74f68cfed676'),
(46, 'excelente', 'Atención recibida', '2026-09-10', 1, 'b4e2cb49919a70e347ca74f68cfed676'),
(47, 'muy_corto', 'Tiempo de espera', '2026-09-10', 1, 'b4e2cb49919a70e347ca74f68cfed676'),
(48, 'excelente', 'Instalaciones', '2026-09-10', 1, 'b4e2cb49919a70e347ca74f68cfed676'),
(49, 'si', 'Recomendaría el hospital', '2026-09-10', 1, 'b4e2cb49919a70e347ca74f68cfed676'),
(50, 'sisisi', 'Comentarios', '2026-09-10', 1, 'b4e2cb49919a70e347ca74f68cfed676'),
(51, 'estudios', 'Servicio utilizado', '2026-09-10', 1, '8b87de30320d4bbbe7a9490b0a601ddc'),
(52, 'excelente', 'Atención recibida', '2026-09-10', 1, '8b87de30320d4bbbe7a9490b0a601ddc'),
(53, 'muy_corto', 'Tiempo de espera', '2026-09-10', 1, '8b87de30320d4bbbe7a9490b0a601ddc'),
(54, 'excelente', 'Instalaciones', '2026-09-10', 1, '8b87de30320d4bbbe7a9490b0a601ddc'),
(55, 'si', 'Recomendaría el hospital', '2026-09-10', 1, '8b87de30320d4bbbe7a9490b0a601ddc'),
(56, '', 'Comentarios', '2026-09-10', 1, '8b87de30320d4bbbe7a9490b0a601ddc');

CREATE TABLE `ruta` (
  `ID_ruta` int(11) NOT NULL,
  `Descripcion` varchar(255) NOT NULL,
  `Tipo` varchar(50) NOT NULL,
  `Local_Nacional` varchar(50) NOT NULL,
  `ID_Traslado` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

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

CREATE TABLE `traslado_elemento` (
  `ID_Traslado` int(11) NOT NULL,
  `ID_elemento` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
ALTER TABLE `acompañante`
  ADD PRIMARY KEY (`ID_Acompañante`),
  ADD KEY `ID_Traslado` (`ID_Traslado`);
ALTER TABLE `ambulancia`
  ADD PRIMARY KEY (`ID_ambulancia`);
ALTER TABLE `conductor`
  ADD PRIMARY KEY (`ID_Conductor`);
ALTER TABLE `documento`
  ADD PRIMARY KEY (`Id_Documento`),
  ADD KEY `ID_Paciente` (`ID_Paciente`);
ALTER TABLE `elementos_traslados`
  ADD PRIMARY KEY (`ID_elemento`);
ALTER TABLE `encuesta`
  ADD PRIMARY KEY (`ID_Encuesta`);
ALTER TABLE `funcionario`
  ADD PRIMARY KEY (`ID_Funcionario`);
ALTER TABLE `paciente`
  ADD PRIMARY KEY (`ID_Paciente`),
  ADD KEY `ID_Encuesta` (`ID_Encuesta`);
ALTER TABLE `paciente_traslado`
  ADD PRIMARY KEY (`ID_Paciente`,`ID_Traslado`),
  ADD KEY `ID_Traslado` (`ID_Traslado`);
ALTER TABLE `qr`
  ADD PRIMARY KEY (`id`),
  ADD KEY `Id_Documento` (`Id_Documento`);
ALTER TABLE `respuesta`
  ADD PRIMARY KEY (`ID_Respuesta`),
  ADD KEY `ID_Encuesta` (`ID_Encuesta`);
ALTER TABLE `ruta`
  ADD PRIMARY KEY (`ID_ruta`),
  ADD KEY `ID_Traslado` (`ID_Traslado`);
ALTER TABLE `traslado`
  ADD PRIMARY KEY (`ID_Traslado`),
  ADD KEY `ID_ambulancia` (`ID_ambulancia`),
  ADD KEY `ID_Conductor` (`ID_Conductor`),
  ADD KEY `ID_Funcionario` (`ID_Funcionario`);
ALTER TABLE `traslado_elemento`
  ADD PRIMARY KEY (`ID_Traslado`,`ID_elemento`),
  ADD KEY `ID_elemento` (`ID_elemento`);
ALTER TABLE `acompañante`
  MODIFY `ID_Acompañante` int(11) NOT NULL AUTO_INCREMENT;
ALTER TABLE `ambulancia`
  MODIFY `ID_ambulancia` int(11) NOT NULL AUTO_INCREMENT;
ALTER TABLE `conductor`
  MODIFY `ID_Conductor` int(11) NOT NULL AUTO_INCREMENT;
ALTER TABLE `documento`
  MODIFY `Id_Documento` int(11) NOT NULL AUTO_INCREMENT;
ALTER TABLE `elementos_traslados`
  MODIFY `ID_elemento` int(11) NOT NULL AUTO_INCREMENT;
ALTER TABLE `encuesta`
  MODIFY `ID_Encuesta` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;
ALTER TABLE `funcionario`
  MODIFY `ID_Funcionario` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;
ALTER TABLE `paciente`
  MODIFY `ID_Paciente` int(11) NOT NULL AUTO_INCREMENT;
ALTER TABLE `qr`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;
ALTER TABLE `respuesta`
  MODIFY `ID_Respuesta` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=57;
ALTER TABLE `ruta`
  MODIFY `ID_ruta` int(11) NOT NULL AUTO_INCREMENT;
ALTER TABLE `traslado`
  MODIFY `ID_Traslado` int(11) NOT NULL AUTO_INCREMENT;
ALTER TABLE `acompañante`
  ADD CONSTRAINT `acompañante_ibfk_1` FOREIGN KEY (`ID_Traslado`) REFERENCES `traslado` (`ID_Traslado`);
ALTER TABLE `documento`
  ADD CONSTRAINT `documento_ibfk_1` FOREIGN KEY (`ID_Paciente`) REFERENCES `paciente` (`ID_Paciente`);
ALTER TABLE `paciente`
  ADD CONSTRAINT `paciente_ibfk_1` FOREIGN KEY (`ID_Encuesta`) REFERENCES `encuesta` (`ID_Encuesta`);
ALTER TABLE `paciente_traslado`
  ADD CONSTRAINT `paciente_traslado_ibfk_1` FOREIGN KEY (`ID_Paciente`) REFERENCES `paciente` (`ID_Paciente`),
  ADD CONSTRAINT `paciente_traslado_ibfk_2` FOREIGN KEY (`ID_Traslado`) REFERENCES `traslado` (`ID_Traslado`);
ALTER TABLE `qr`
  ADD CONSTRAINT `qr_ibfk_1` FOREIGN KEY (`Id_Documento`) REFERENCES `documento` (`Id_Documento`);
ALTER TABLE `respuesta`
  ADD CONSTRAINT `respuesta_ibfk_1` FOREIGN KEY (`ID_Encuesta`) REFERENCES `encuesta` (`ID_Encuesta`);
ALTER TABLE `ruta`
  ADD CONSTRAINT `ruta_ibfk_1` FOREIGN KEY (`ID_Traslado`) REFERENCES `traslado` (`ID_Traslado`);
ALTER TABLE `traslado`
  ADD CONSTRAINT `traslado_ibfk_1` FOREIGN KEY (`ID_ambulancia`) REFERENCES `ambulancia` (`ID_ambulancia`),
  ADD CONSTRAINT `traslado_ibfk_2` FOREIGN KEY (`ID_Conductor`) REFERENCES `conductor` (`ID_Conductor`),
  ADD CONSTRAINT `traslado_ibfk_3` FOREIGN KEY (`ID_Funcionario`) REFERENCES `funcionario` (`ID_Funcionario`);
ALTER TABLE `traslado_elemento`
  ADD CONSTRAINT `traslado_elemento_ibfk_1` FOREIGN KEY (`ID_Traslado`) REFERENCES `traslado` (`ID_Traslado`),
  ADD CONSTRAINT `traslado_elemento_ibfk_2` FOREIGN KEY (`ID_elemento`) REFERENCES `elementos_traslados` (`ID_elemento`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
