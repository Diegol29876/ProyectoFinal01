<?php 

    require_once 'config.php';

    function conectar_bd() {

        $con = new mysqli(DB_host, DB_usuario, DB_contraseña, DB_nombre);

        if($con->connect_error){
            
            die("Error de conexión: " . $con->connect_error);
        }

        return $con;

    }

?> 