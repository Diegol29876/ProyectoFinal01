CREATE TABLE IF NOT EXISTS `muestra_paciente` (
    `ID_Muestra` int(11) NOT NULL AUTO_INCREMENT,
    `Nombre` varchar(100) NOT NULL,
    `Apellido` varchar(100) NOT NULL,
    `Cedula` varchar(20) NOT NULL,
    `Muestra` varchar(255) NOT NULL,
    `Fecha_Muestra` date NOT NULL,
    PRIMARY KEY (`ID_Muestra`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
