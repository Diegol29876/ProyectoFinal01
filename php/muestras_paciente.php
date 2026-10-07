<?php

session_start();
require_once __DIR__ . '/conexion.php';

header('Content-Type: application/json; charset=utf-8');

function responder_muestras(bool $status, string $mensaje, array $datos = [], int $codigo = 200): void
{
    http_response_code($codigo);
    echo json_encode(
        array_merge(['status' => $status, 'mensaje' => $mensaje], $datos),
        JSON_UNESCAPED_UNICODE
    );
    exit;
}

function fecha_muestra_valida(string $fecha): bool
{
    $formato = DateTime::createFromFormat('!Y-m-d', $fecha);
    return $formato !== false && $formato->format('Y-m-d') === $fecha;
}

if (!isset($_SESSION['funcionario_id'])) {
    responder_muestras(false, 'Iniciá sesión para gestionar muestras.', [], 401);
}

if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    if (($_GET['action'] ?? '') !== 'list') {
        responder_muestras(false, 'Operación no válida.', [], 400);
    }

    try {
        $conexion = conectar_bd();
        $resultado = $conexion->query(
            'SELECT ID_Muestra AS id, Nombre AS nombre, Apellido AS apellido,
                    Cedula AS cedula, Muestra AS muestra, Fecha_Muestra AS fecha
             FROM muestra_paciente
             ORDER BY Fecha_Muestra DESC, ID_Muestra DESC'
        );
        $_SESSION['csrf_token'] ??= bin2hex(random_bytes(32));
        responder_muestras(true, '', [
            'muestras' => $resultado->fetch_all(MYSQLI_ASSOC),
            'csrf_token' => $_SESSION['csrf_token']
        ]);
    } catch (mysqli_sql_exception $error) {
        error_log('muestras_paciente.php GET: ' . $error->getMessage());
        responder_muestras(false, 'No se pudieron consultar las muestras. Verificá que la migración esté aplicada.', [], 500);
    }
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    responder_muestras(false, 'Método no permitido.', [], 405);
}

$entrada = json_decode(file_get_contents('php://input'), true);
if (!is_array($entrada)) {
    responder_muestras(false, 'La solicitud no contiene datos válidos.', [], 400);
}

if (!isset($_SESSION['csrf_token']) ||
    !hash_equals($_SESSION['csrf_token'], (string) ($entrada['csrf_token'] ?? ''))) {
    responder_muestras(false, 'La sesión venció o la solicitud no es válida. Recargá la página.', [], 403);
}

$nombre = trim((string) ($entrada['nombre'] ?? ''));
$apellido = trim((string) ($entrada['apellido'] ?? ''));
$cedula = trim((string) ($entrada['cedula'] ?? ''));
$muestra = trim((string) ($entrada['muestra'] ?? ''));
$fecha = (string) ($entrada['fecha'] ?? '');

if ($nombre === '' || $apellido === '' || $cedula === '' || $muestra === '' ||
    strlen($nombre) > 100 || strlen($apellido) > 100 || strlen($cedula) > 20 ||
    strlen($muestra) > 255 || !fecha_muestra_valida($fecha)) {
    responder_muestras(false, 'Completá correctamente todos los datos de la muestra.', [], 422);
}

try {
    $conexion = conectar_bd();
    $consulta = $conexion->prepare(
        'INSERT INTO muestra_paciente (Nombre, Apellido, Cedula, Muestra, Fecha_Muestra)
         VALUES (?, ?, ?, ?, ?)'
    );
    $consulta->bind_param('sssss', $nombre, $apellido, $cedula, $muestra, $fecha);
    $consulta->execute();
    $consulta->close();

    responder_muestras(true, 'Muestra registrada correctamente.', [
        'csrf_token' => $_SESSION['csrf_token']
    ]);
} catch (mysqli_sql_exception $error) {
    error_log('muestras_paciente.php POST: ' . $error->getMessage());
    responder_muestras(false, 'No se pudo registrar la muestra. Verificá que la migración esté aplicada.', [], 500);
}
