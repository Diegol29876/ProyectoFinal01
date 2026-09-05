<?php
header('Content-Type: application/json; charset=utf-8');

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

$catalogoPath = __DIR__ . '/../uploads/catalogo.json';
$archivosPublicados = [];

if (is_readable($catalogoPath)) {
    $lineas = file($catalogoPath, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);

    foreach ($lineas as $indice => $linea) {
        $documento = json_decode($linea, true);

        if (is_array($documento) && isset($documento['nombre'], $documento['archivo'])) {
            $archivo = basename(rawurldecode($documento['archivo']));
            $archivosPublicados[] = $archivo;
            $documentos[] = [
                'id' => 'cargado-' . $indice,
                'nombre' => $documento['nombre'],
                'tipo' => $documento['tipo'] ?? 'Documento de paciente',
                'archivo' => $documento['archivo']
            ];
        }
    }
}

$uploadDir = __DIR__ . '/../uploads';
$archivosExistentes = glob($uploadDir . '/*.{pdf,doc,docx}', GLOB_BRACE) ?: [];

foreach ($archivosExistentes as $archivoPath) {
    $archivo = basename($archivoPath);

    if (!in_array($archivo, $archivosPublicados, true)) {
        $documentos[] = [
            'id' => 'archivo-' . $archivo,
            'nombre' => pathinfo($archivo, PATHINFO_FILENAME),
            'tipo' => 'Documento de paciente',
            'archivo' => '../uploads/' . rawurlencode($archivo)
        ];
    }
}

echo json_encode($documentos, JSON_UNESCAPED_UNICODE);