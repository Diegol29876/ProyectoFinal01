<?php

require_once 'config.php';

function conectar_bd(): mysqli
{
    mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

    $conexion = new mysqli(
        DB_HOST,
        DB_USUARIO,
        DB_CONTRASENA,
        DB_NOMBRE
    );

    $conexion->set_charset('utf8mb4');
    return $conexion;
}