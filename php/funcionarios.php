<?php
session_start();
require_once __DIR__ . '/conexion.php';
header('Content-Type: application/json; charset=utf-8');

function responder($status, $mensaje, $datos = [], $codigo = 200)
{
    http_response_code($codigo);
    echo json_encode(array_merge(['status' => $status, 'mensaje' => $mensaje], $datos), JSON_UNESCAPED_UNICODE);
    exit;
}

if (!isset($_SESSION['funcionario_id'])) {
    responder(false, 'Iniciá sesión para gestionar funcionarios.', [], 401);
}

if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    try {
        $conexion = conectar_bd();
        $resultado = $conexion->query(
            'SELECT ID_Funcionario AS id, n_usuario AS nombre, c_electronico AS correo,
                    direccion, f_nacimineto AS nacimiento, cedula, n_telefono AS telefono,
                    f_Ingreso AS ingreso, estado
             FROM funcionario ORDER BY n_usuario'
        );
        $_SESSION['csrf_funcionarios'] ??= bin2hex(random_bytes(32));
        responder(true, '', [
            'funcionarios' => $resultado->fetch_all(MYSQLI_ASSOC),
            'csrf_token' => $_SESSION['csrf_funcionarios']
        ]);
    } catch (mysqli_sql_exception $error) {
        error_log('funcionarios.php GET: ' . $error->getMessage());
        responder(false, 'No se pudieron consultar los funcionarios.', [], 500);
    }
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    responder(false, 'Método no permitido.', [], 405);
}

$datos = json_decode(file_get_contents('php://input'), true);
if (!is_array($datos)) {
    responder(false, 'La solicitud no contiene datos válidos.', [], 400);
}
if (
    !isset($_SESSION['csrf_funcionarios'])
    || !hash_equals($_SESSION['csrf_funcionarios'], (string) ($datos['csrf_token'] ?? ''))
) {
    responder(false, 'La sesión venció. Recargá la página e intentá de nuevo.', [], 403);
}

$id = filter_var($datos['id'] ?? null, FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);
if (!$id) {
    responder(false, 'El funcionario indicado no es válido.', [], 422);
}

$accion = $datos['action'] ?? '';

try {
    $conexion = conectar_bd();

    if ($accion === 'delete') {
        if ((int) $_SESSION['funcionario_id'] === $id) {
            responder(false, 'No podés eliminar tu propio usuario.', [], 422);
        }

        $consulta = $conexion->prepare('DELETE FROM funcionario WHERE ID_Funcionario = ?');
        $consulta->bind_param('i', $id);
        $consulta->execute();
        if (!$consulta->affected_rows) {
            responder(false, 'No se encontró el funcionario.', [], 404);
        }
        responder(true, 'Funcionario eliminado correctamente.');
    }

    if ($accion !== 'update') {
        responder(false, 'Operación no válida.', [], 400);
    }

    $nombre = trim($datos['n_usuario'] ?? '');
    $correo = trim($datos['c_electronico'] ?? '');
    $direccion = trim($datos['direccion'] ?? '');
    $nacimiento = trim($datos['f_nacimiento'] ?? '');
    $cedula = trim($datos['cedula'] ?? '');
    $telefono = trim($datos['n_telefono'] ?? '');
    $ingreso = trim($datos['f_ingreso'] ?? '');
    $estado = trim($datos['estado'] ?? '');

    if (!$nombre || !$correo || !$direccion || !$nacimiento || !$cedula || !$telefono || !$ingreso || !$estado) {
        responder(false, 'Completá todos los campos.', [], 422);
    }
    if (!filter_var($correo, FILTER_VALIDATE_EMAIL)) {
        responder(false, 'El correo electrónico no es válido.', [], 422);
    }
    $fechaNacimiento = DateTime::createFromFormat('!Y-m-d', $nacimiento);
    $fechaIngreso = DateTime::createFromFormat('!Y-m-d', $ingreso);
    if (
        !in_array($estado, ['Activo', 'Inactivo'], true)
        || !$fechaNacimiento || $fechaNacimiento->format('Y-m-d') !== $nacimiento
        || !$fechaIngreso || $fechaIngreso->format('Y-m-d') !== $ingreso
    ) {
        responder(false, 'Revisá las fechas y el estado ingresados.', [], 422);
    }

    $existe = $conexion->prepare('SELECT ID_Funcionario FROM funcionario WHERE ID_Funcionario = ?');
    $existe->bind_param('i', $id);
    $existe->execute();
    if (!$existe->get_result()->num_rows) {
        responder(false, 'No se encontró el funcionario.', [], 404);
    }

    $repetido = $conexion->prepare(
        'SELECT ID_Funcionario FROM funcionario
         WHERE (n_usuario = ? OR c_electronico = ? OR cedula = ?) AND ID_Funcionario <> ?'
    );
    $repetido->bind_param('sssi', $nombre, $correo, $cedula, $id);
    $repetido->execute();
    if ($repetido->get_result()->num_rows) {
        responder(false, 'El nombre, correo o cédula ya pertenece a otro funcionario.', [], 409);
    }

    $consulta = $conexion->prepare(
        'UPDATE funcionario SET n_usuario = ?, c_electronico = ?, direccion = ?,
         f_nacimineto = ?, cedula = ?, n_telefono = ?, f_Ingreso = ?, estado = ?
         WHERE ID_Funcionario = ?'
    );
    $consulta->bind_param(
        'ssssssssi',
        $nombre,
        $correo,
        $direccion,
        $nacimiento,
        $cedula,
        $telefono,
        $ingreso,
        $estado,
        $id
    );
    $consulta->execute();

    responder(true, 'Funcionario actualizado correctamente.');
} catch (mysqli_sql_exception $error) {
    error_log('funcionarios.php ' . $accion . ': ' . $error->getMessage());
    if ($accion === 'delete' && $error->getCode() === 1451) {
        responder(false, 'No se puede eliminar porque tiene registros asociados.', [], 409);
    }
    responder(false, 'No se pudo completar la operación en la base de datos.', [], 500);
}
