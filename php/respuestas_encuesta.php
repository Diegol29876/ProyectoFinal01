<?php

session_start();
require_once 'conexion.php';

header('Content-Type: application/json; charset=utf-8');

if (!isset($_SESSION['funcionario_id'])) {
    http_response_code(401);
    echo json_encode(['error' => 'Sesión no iniciada.']);
    exit;
}

try {
    $conexion = conectar_bd();
    $resultado = $conexion->query(
        'SELECT ID_Respuesta, ID_Encuesta, Fecha, Clasificacion, Respuesta_Texto,
            COALESCE(ID_Envio, CONCAT("anterior-", ID_Encuesta, "-", Fecha)) AS ID_Envio
         FROM Respuesta
         ORDER BY Fecha DESC, ID_Respuesta DESC'
    );

    $respuestas = [];

    while ($fila = $resultado->fetch_assoc()) {
        $respuestas[] = $fila;
    }

    echo json_encode($respuestas, JSON_UNESCAPED_UNICODE);
    $conexion->close();
} catch (Exception $error) {
    http_response_code(500);
    echo json_encode(['error' => 'No se pudieron cargar las respuestas.']);
}