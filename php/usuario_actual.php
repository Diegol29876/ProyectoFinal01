<?php

session_start();
require_once 'conexion.php';

header('Content-Type: application/json; charset=utf-8');

if (!isset($_SESSION['funcionario_id'])) {
    echo json_encode(['nombre' => 'Usuario']);
    exit;
}

try {
    $conexion = conectar_bd();
    $consulta = $conexion->prepare(
        'SELECT n_usuario FROM funcionario WHERE ID_Funcionario = ?'
    );
    $consulta->bind_param('i', $_SESSION['funcionario_id']);
    $consulta->execute();
    $fila = $consulta->get_result()->fetch_assoc();

    echo json_encode([
        'nombre' => $fila['n_usuario'] ?? 'Usuario'
    ]);

    $consulta->close();
    $conexion->close();
} catch (Exception $error) {
    echo json_encode(['nombre' => 'Usuario']);
}