<?php 

    require_once 'config.php';

    function conectar_bd() : mysqli {

    mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

        $con = new mysqli(DB_host, DB_usuario, DB_contraseña, DB_nombre);

        if($con->connect_error){
            
            die("Error de conexión: " . $con->connect_error);
        }

        return $con;

    }

?> 