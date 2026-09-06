<?php
session_start();
require_once 'conexion.php';
header('Content-Type: application/json; charset=utf-8');

function responder($correcto, $mensaje, $usuario = null) {
    $respuesta = [
        'status' => $correcto,
        'mensaje' => $mensaje
    ];

    if ($usuario !== null) {
        $respuesta['usuario'] = $usuario;
    }

    echo json_encode($respuesta);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    responder(false, 'Método no permitido.');
}

$cedula = trim($_POST['cedula'] ?? '');
$contrasenia = $_POST['password'] ?? '';

if (empty($cedula) || empty($contrasenia)) {
    responder(false, 'Completá todos los campos.');
}

try {
    $conexion = conectar_bd();
    $consulta = $conexion->prepare(
        'SELECT ID_Funcionario, n_usuario, contrasenia FROM funcionario WHERE cedula = ?'
    );
    $consulta->bind_param('s', $cedula);
    $consulta->execute();
    $fila = $consulta->get_result()->fetch_assoc();

    if (!$fila || !password_verify($contrasenia, $fila['contrasenia'])) {
        responder(false, 'Cédula o contraseña incorrecta.');
    }

    session_regenerate_id(true);
    $_SESSION['funcionario_id'] = $fila['ID_Funcionario'];
    $_SESSION['usuario'] = $fila['n_usuario'];

    responder(true, 'Inicio de sesión exitoso.', $fila['n_usuario']);
} catch (Exception $error) {
    responder(false, 'Error interno en el servidor.');
}