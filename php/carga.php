<?php
header('Content-Type: text/plain; charset=utf-8');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo 'Método no permitido';
    exit;
}

if (!isset($_FILES['archivo']) || $_FILES['archivo']['error'] !== UPLOAD_ERR_OK) {
    http_response_code(400);
    echo 'Error al recibir el archivo';
    exit;
}

$nombre = isset($_POST['nombre']) ? trim($_POST['nombre']) : '';
$tipo = isset($_POST['tipo']) ? trim($_POST['tipo']) : 'Documento de paciente';
$confirmarPublico = isset($_POST['confirmar_publico']) && $_POST['confirmar_publico'] === '1';

if ($nombre === '' || !$confirmarPublico) {
    http_response_code(400);
    echo 'Indicá un nombre y confirmá que el documento es información general.';
    exit;
}

$nombre = preg_replace('/[\\\\\/:*?"<>|]/', '', $nombre);

$uploadDir = __DIR__ . '/../uploads';

if (!is_dir($uploadDir)) {
    mkdir($uploadDir, 0755, true);
}

$archivo = $_FILES['archivo'];
$extension = strtolower(pathinfo($archivo['name'], PATHINFO_EXTENSION));

if (!in_array($extension, ['pdf', 'doc', 'docx'], true)) {
    http_response_code(400);
    echo 'Solo se permiten archivos PDF, DOC o DOCX.';
    exit;
}

$nombreBase = $nombre;
$ruta = $uploadDir . '/' . $nombreBase;

if ($extension !== '') {
    $ruta .= '.' . $extension;
}

$contador = 1;

while (file_exists($ruta)) {
    $ruta = $uploadDir . '/' . $nombreBase . '_' . $contador;

    if ($extension !== '') {
        $ruta .= '.' . $extension;
    }

    $contador++;
}

if (move_uploaded_file($archivo['tmp_name'], $ruta)) {
    $catalogo = [
        'nombre' => $nombre,
        'tipo' => $tipo,
        'archivo' => '../uploads/' . rawurlencode(basename($ruta))
    ];
    file_put_contents($uploadDir . '/catalogo.json', json_encode($catalogo) . PHP_EOL, FILE_APPEND | LOCK_EX);
    echo 'Archivo subido correctamente: ' . basename($ruta);
} else {
    http_response_code(500);
    echo 'No se pudo guardar el archivo';
}