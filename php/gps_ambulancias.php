<?php
header("Content-Type: application/json");

$archivo = "ubicaciones.json";

// RECIBIR Y GUARDAR UBICACIÓN
if ($_SERVER["REQUEST_METHOD"] == "POST") {

    $datos = json_decode(file_get_contents("php://input"), true);

    if (!isset($datos["id_ambulancia"]) || !isset($datos["latitud"]) || !isset($datos["longitud"])) {
        echo json_encode(["error" => "Faltan datos"]);
        exit;
    }

    $ubicaciones = [];

    if (file_exists($archivo)) {
        $ubicaciones = json_decode(file_get_contents($archivo), true);

        if (!is_array($ubicaciones)) {
            $ubicaciones = [];
        }
    }

    $id = $datos["id_ambulancia"];

    $ubicaciones[$id] = [
        "latitud" => $datos["latitud"],
        "longitud" => $datos["longitud"]
    ];

    file_put_contents($archivo, json_encode($ubicaciones), LOCK_EX);

    echo json_encode(["mensaje" => "Ubicación guardada"]);
    exit;
}


// CONSULTAR UBICACIÓN
if (isset($_GET["id"])) {

    if (file_exists($archivo)) {
        $ubicaciones = json_decode(file_get_contents($archivo), true);

        if (isset($ubicaciones[$_GET["id"]])) {
            echo json_encode($ubicaciones[$_GET["id"]]);
        } else {
            echo json_encode(["error" => "Todavía no hay ubicación"]);
        }
    } else {
        echo json_encode(["error" => "Todavía no hay ubicación"]);
    }

    exit;
}

echo json_encode(["error" => "Solicitud incorrecta"]);
?>