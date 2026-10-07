<?php
session_start();
require_once __DIR__ . '/conexion.php';
require_once __DIR__ . '/traslados_operaciones.php';
require_once __DIR__ . '/traslados_consultas.php';
header('Content-Type: application/json; charset=utf-8');

function responder_json(bool $status, string $mensaje, array $datos = [], int $codigo = 200): void
{
    http_response_code($codigo);
    echo json_encode(array_merge(['status' => $status, 'mensaje' => $mensaje], $datos), JSON_UNESCAPED_UNICODE);
    exit;
}

if (!isset($_SESSION['funcionario_id'])) responder_json(false, 'Iniciá sesión para gestionar traslados.', [], 401);
$metodo = $_SERVER['REQUEST_METHOD'];
$accion = $metodo === 'GET' ? ($_GET['action'] ?? '') : '';

if ($metodo === 'GET') {
    try {
        $conexion = conectar_bd();
        if ($accion === 'list') $datos = ['traslados' => listar_traslados($conexion)];
        elseif ($accion === 'catalogs') $datos = listar_catalogos($conexion);
        else responder_json(false, 'Operación no válida.', [], 400);
        $datos['csrf_token'] = $_SESSION['csrf_token'] ??= bin2hex(random_bytes(32));
        responder_json(true, '', $datos);
    } catch (mysqli_sql_exception $error) {
        error_log('traslados.php GET: ' . $error->getMessage());
        responder_json(false, 'No se pudieron consultar los traslados. Verificá que la migración del módulo esté aplicada.', [], 500);
    }
}

if ($metodo !== 'POST') responder_json(false, 'Método no permitido.', [], 405);
$entrada = json_decode(file_get_contents('php://input'), true);
if (!is_array($entrada)) responder_json(false, 'La solicitud no contiene datos válidos.', [], 400);
if (!isset($_SESSION['csrf_token']) || !hash_equals($_SESSION['csrf_token'], (string) ($entrada['csrf_token'] ?? ''))) {
    responder_json(false, 'La sesión venció o la solicitud no es válida. Recargá la página.', [], 403);
}

$accion = (string) ($entrada['action'] ?? '');
$id = entero_positivo($entrada['id'] ?? 0);
try {
    $conexion = conectar_bd();
    if ($accion === 'delete') {
        if (!$id) responder_json(false, 'El traslado indicado no es válido.', [], 422);
        if (!eliminar_traslado($conexion, $id)) responder_json(false, 'No se encontró el traslado.', [], 404);
        responder_json(true, 'Traslado eliminado correctamente.');
    }
    if (!in_array($accion, ['create', 'update'], true)) responder_json(false, 'Operación no válida.', [], 400);
    guardar_traslado($conexion, $entrada, $accion, (int) $_SESSION['funcionario_id']);
    responder_json(true, $accion === 'create' ? 'Traslado registrado correctamente.' : 'Traslado modificado correctamente.');
} catch (InvalidArgumentException $error) {
    responder_json(false, $error->getMessage(), [], 422);
} catch (OutOfBoundsException $error) {
    responder_json(false, $error->getMessage(), [], 404);
} catch (mysqli_sql_exception $error) {
    if (isset($conexion) && $conexion->thread_id) $conexion->rollback();
    if ($accion === 'delete') {
        error_log('traslados.php delete: ' . $error->getMessage());
        responder_json(false, 'No se pudo eliminar el traslado.', [], 500);
    }
    error_log('traslados.php guardar: ' . $error->getMessage());
    $mensaje = $error->getCode() === 1062
        ? 'Ya existe un registro con los mismos datos únicos (por ejemplo, cédula o matrícula).'
        : 'No se pudo guardar el traslado. Verificá los datos y que la migración esté aplicada.';
    responder_json(false, $mensaje, [], 500);
} catch (Throwable $error) {
    if (isset($conexion) && $conexion->thread_id) $conexion->rollback();
    error_log('traslados.php guardar: ' . $error->getMessage());
    responder_json(false, $error->getMessage(), [], 500);
}
