<?php
header('Content-Type: application/json; charset=utf-8');
header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');
header('Pragma: no-cache');
header('Expires: 0');

$uploadDir = __DIR__ . '/../uploads';
$eliminadosPath = $uploadDir . '/documentos_eliminados.txt';
$documentosEliminados = [];
if (is_readable($eliminadosPath)) {
    $documentosEliminados = file($eliminadosPath, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    if ($documentosEliminados === false) {
        http_response_code(500);
        echo json_encode(['error' => 'No se pudieron cargar los documentos.']);
        exit;
    }
}

$documentos = [
    [
        'id' => 'base-1',
        'nombre' => 'Preparación para estudios imagenológicos',
        'tipo' => 'Manual de preparación',
        'archivo' => '../pdf/preparacion_estudios.pdf'
    ],
    [
        'id' => 'base-2',
        'nombre' => 'Indicaciones para pacientes con Warfarina',
        'tipo' => 'Pauta de enfermería',
        'archivo' => '../pdf/warfarina.pdf'
    ],
    [
        'id' => 'base-3',
        'nombre' => 'Prevención de infecciones',
        'tipo' => 'Documento de paciente',
        'archivo' => '../pdf/infecciones.pdf'
    ]
];

$catalogoPath = $uploadDir . '/catalogo.json';
$archivosPublicados = [];

if (is_readable($catalogoPath)) {
    $lineas = file($catalogoPath, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);

    foreach ($lineas as $linea) {
        $documento = json_decode($linea, true);

        if (is_array($documento) && isset($documento['nombre'], $documento['archivo'])) {
            $archivo = basename(rawurldecode($documento['archivo']));
            $archivosPublicados[] = $archivo;
            if (!is_file($uploadDir . '/' . $archivo)) {
                continue;
            }
            $documentos[] = [
                'id' => 'upload-' . $archivo,
                'nombre' => $documento['nombre'],
                'tipo' => $documento['tipo'] ?? 'Documento de paciente',
                'archivo' => $documento['archivo']
            ];
        }
    }
}

$archivosExistentes = glob($uploadDir . '/*.{pdf,doc,docx}', GLOB_BRACE) ?: [];

foreach ($archivosExistentes as $archivoPath) {
    $archivo = basename($archivoPath);

    if (!in_array($archivo, $archivosPublicados, true)) {
        $documentos[] = [
            'id' => 'upload-' . $archivo,
            'nombre' => pathinfo($archivo, PATHINFO_FILENAME),
            'tipo' => 'Documento de paciente',
            'archivo' => '../uploads/' . rawurlencode($archivo)
        ];
    }
}

$documentos = array_values(array_filter($documentos, function ($documento) use ($documentosEliminados) {
    return !in_array($documento['id'], $documentosEliminados, true);
}));

echo json_encode($documentos, JSON_UNESCAPED_UNICODE);