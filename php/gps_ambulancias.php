<?php
header('Content-Type: application/json; charset=utf-8');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['error' => 'Método no permitido.']);
    exit;
}

$datos = json_decode(file_get_contents('php://input'), true);

if (!is_array($datos)) {
    http_response_code(400);
    echo json_encode(['error' => 'Los datos de ubicación no son válidos.']);
    exit;
}

$idTraslado = filter_var($datos['id_traslado'] ?? null, FILTER_VALIDATE_INT);
$latitud = $datos['latitud'] ?? null;
$longitud = $datos['longitud'] ?? null;

if (
    $idTraslado === false ||
    $idTraslado === null ||
    $idTraslado <= 0 ||
    !is_numeric($latitud) ||
    !is_numeric($longitud) ||
    (float) $latitud < -90 ||
    (float) $latitud > 90 ||
    (float) $longitud < -180 ||
    (float) $longitud > 180
) {
    http_response_code(400);
    echo json_encode(['error' => 'Los datos de ubicación no son válidos.']);
    exit;
}

require_once __DIR__ . '/conexion.php';

try {
    $conexion = conectar_bd();
    $consulta = $conexion->prepare(
        'INSERT INTO ubicacion_traslado (latitud, longuitod, id_traslado) VALUES (?, ?, ?)'
    );
    $latitud = (float) $latitud;
    $longitud = (float) $longitud;
    $consulta->bind_param('ddi', $latitud, $longitud, $idTraslado);
    $consulta->execute();
    $consulta->close();
    $conexion->close();

    echo json_encode(['mensaje' => 'Ubicación guardada.']);
} catch (Throwable $error) {
    error_log('Error al guardar ubicación de traslado: ' . $error->getMessage());
    http_response_code(500);
    echo json_encode(['error' => 'No se pudo guardar la ubicación.']);
}
