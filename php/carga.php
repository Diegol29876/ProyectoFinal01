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

if ($nombre === '') {
    http_response_code(400);
    echo 'Falta el nombre del documento';
    exit;
}

// limpio caracteres que pueden romper el nombre del archivo
$nombre = preg_replace('/[\\\\\/:*?"<>|]/', '', $nombre);

$uploadDir = __DIR__ . '/../uploads';

if (!is_dir($uploadDir)) {
    mkdir($uploadDir, 0755, true);
}

$archivo = $_FILES['archivo'];
$extension = strtolower(pathinfo($archivo['name'], PATHINFO_EXTENSION));

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
    echo 'Archivo subido correctamente: ' . basename($ruta);
} else {
    http_response_code(500);
    echo 'No se pudo guardar el archivo';
}