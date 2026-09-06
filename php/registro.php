<?php
require_once 'conexion.php';

header('Content-Type: application/json; charset=utf-8');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['status' => false, 'mensaje' => 'Método no permitido.']);
    exit;
}

$usuario     = trim($_POST['n_usuario'] ?? '');
$correo      = trim($_POST['c_electronico'] ?? '');
$telefono    = trim($_POST['n_telefono'] ?? '');
$contrasenia = $_POST['contrasenia'] ?? '';
$direccion   = trim($_POST['direccion'] ?? '');
$nacimiento  = $_POST['f_nacimiento'] ?? '';
$cedula      = trim($_POST['cedula'] ?? '');
$estado      = trim($_POST['estado'] ?? '');
$ingreso     = $_POST['f_ingreso'] ?? '';

if (empty($usuario) || empty($correo) || empty($telefono) || empty($contrasenia) ||
    empty($direccion) || empty($nacimiento) || empty($cedula) || empty($estado) || empty($ingreso)) {
    echo json_encode(['status' => false, 'mensaje' => 'Completá todos los campos.']);
    exit;
}

if (!filter_var($correo, FILTER_VALIDATE_EMAIL)) {
    echo json_encode(['status' => false, 'mensaje' => 'El correo no es válido.']);
    exit;
}

try {
    $conexion = conectar_bd();

    $verificar = $conexion->prepare(
        'SELECT n_usuario, c_electronico, cedula FROM funcionario 
         WHERE n_usuario = ? OR c_electronico = ? OR cedula = ?'
    );
    $verificar->bind_param('sss', $usuario, $correo, $cedula);
    $verificar->execute();
    $resultado = $verificar->get_result();

    if ($resultado->num_rows > 0) {
        $fila = $resultado->fetch_assoc();

        if ($fila['n_usuario'] === $usuario) {
            $msg = "El usuario '{$usuario}' ya está registrado.";
        } elseif ($fila['c_electronico'] === $correo) {
            $msg = "El correo '{$correo}' ya está registrado.";
        } else {
            $msg = "La cédula '{$cedula}' ya está registrada.";
        }

        echo json_encode(['status' => false, 'mensaje' => $msg]);
        $verificar->close();
        exit;
    }

    $verificar->close();
    $hashPassword = password_hash($contrasenia, PASSWORD_DEFAULT);

    $consulta = $conexion->prepare(
        'INSERT INTO funcionario
        (n_usuario, contrasenia, c_electronico, direccion, f_nacimineto, cedula, n_telefono, f_Ingreso, estado)
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)'
    );

    $consulta->bind_param(
        'sssssssss',
        $usuario,
        $hashPassword,
        $correo,
        $direccion,
        $nacimiento,
        $cedula,
        $telefono,
        $ingreso,
        $estado
    );

    $consulta->execute();
    $consulta->close();

    echo json_encode(['status' => true, 'mensaje' => 'Registro creado correctamente.']);
    exit;

} catch (mysqli_sql_exception $error) {
    echo json_encode([
        'status' => false,
        'mensaje' => $error->getCode() === 1062
            ? 'El usuario, correo o cédula ya está registrado.'
            : 'No se pudo guardar el registro.'
    ]);
    exit;
}