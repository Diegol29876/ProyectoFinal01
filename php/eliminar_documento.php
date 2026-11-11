<?php
session_start();
header('Content-Type: text/plain; charset=utf-8');

if (!isset($_SESSION['funcionario_id'])) {
    http_response_code(401);
    echo 'Acceso no autorizado.';
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo 'Método no permitido.';
    exit;
}

$id = $_POST['id'] ?? '';
$documentosBase = ['base-1', 'base-2', 'base-3'];
$uploadDir = __DIR__ . '/../uploads';

if (!is_dir($uploadDir) && !mkdir($uploadDir, 0755, true) && !is_dir($uploadDir)) {
    http_response_code(500);
    echo 'No se pudo acceder al catálogo de documentos.';
    exit;
}

if (in_array($id, $documentosBase, true)) {
    $eliminadosPath = $uploadDir . '/documentos_eliminados.txt';
    if (file_put_contents($eliminadosPath, $id . PHP_EOL, FILE_APPEND | LOCK_EX) === false) {
        http_response_code(500);
        echo 'No se pudo guardar la eliminación del documento.';
        exit;
    }

    echo 'Documento eliminado.';
    exit;
}

if (strncmp($id, 'upload-', 7) !== 0) {
    http_response_code(400);
    echo 'Documento inválido.';
    exit;
}

$archivo = substr($id, 7);
$extension = strtolower(pathinfo($archivo, PATHINFO_EXTENSION));
if (
    $archivo === '' ||
    basename($archivo) !== $archivo ||
    preg_match('/[\x00-\x1F\\\\\/]/', $archivo) ||
    !in_array($extension, ['pdf', 'doc', 'docx'], true)
) {
    http_response_code(400);
    echo 'Documento inválido.';
    exit;
}

$rutaArchivo = $uploadDir . '/' . $archivo;
$catalogoPath = $uploadDir . '/catalogo.json';
$lineasRestantes = null;
$enCatalogo = false;
if (is_readable($catalogoPath)) {
    $lineas = file($catalogoPath, FILE_IGNORE_NEW_LINES);
    if ($lineas === false) {
        http_response_code(500);
        echo 'No se pudo actualizar el catálogo de documentos.';
        exit;
    }

    $lineasRestantes = [];
    foreach ($lineas as $linea) {
        $documento = json_decode($linea, true);
        $archivoCatalogado = is_array($documento) && isset($documento['archivo'])
            ? basename(rawurldecode($documento['archivo']))
            : null;

        if ($archivoCatalogado !== $archivo) {
            $lineasRestantes[] = $linea;
        } else {
            $enCatalogo = true;
        }
    }
}

if (!is_file($rutaArchivo) && !$enCatalogo) {
    http_response_code(404);
    echo 'No se encontró el documento.';
    exit;
}

if (is_file($rutaArchivo) && !unlink($rutaArchivo)) {
    http_response_code(500);
    echo 'No se pudo eliminar el archivo.';
    exit;
}

if ($lineasRestantes !== null) {
    $contenido = $lineasRestantes ? implode(PHP_EOL, $lineasRestantes) . PHP_EOL : '';
    if (file_put_contents($catalogoPath, $contenido, LOCK_EX) === false) {
        http_response_code(500);
        echo 'No se pudo actualizar el catálogo de documentos.';
        exit;
    }
}

echo 'Documento eliminado.';
