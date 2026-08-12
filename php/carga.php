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
$uploadDir = __DIR__ . '/../uploads';

if (!is_dir($uploadDir)) {
    mkdir($uploadDir, 0755, true);
}

$archivo = $_FILES['archivo'];
$destino = $uploadDir . '/' . basename($archivo['name']);

if (move_uploaded_file($archivo['tmp_name'], $destino)) {
    echo "Archivo subido correctamente: $nombre";
} else {
    http_response_code(500);
    echo 'No se pudo guardar el archivo';
}
